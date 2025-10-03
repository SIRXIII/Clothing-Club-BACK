<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\UserResource;
use App\Mail\TwoFactorOtpMail;
use App\Models\Partner;
use App\Models\User;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;

class LoginController extends Controller
{
    use ApiResponse;


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = Partner::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', null, 422);
        }

        if ($user->two_factor_method === 'none') {
            $token = $user->createToken('api')->plainTextToken;

            return $this->success([
                'user'  => new PartnerResource($user),
                'token' => $token,
            ], 'Login successful', 200);
        }

        $loginToken = Str::random(64);
        Cache::put("login_attempt:$loginToken", [
            'user_id' => $user->id,
            'method'  => $user->two_factor_method
        ], now()->addMinutes(10));

        if ($user->two_factor_method === 'email') {
            $code = random_int(100000, 999999);

            $user->update([
                'two_factor_email_code'       => encrypt($code),
                'two_factor_email_expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(new TwoFactorOtpMail($code, $user));
        }

        return $this->success([
            'two_factor_required' => true,
            'method'              => $user->two_factor_method,
            'login_token'         => $loginToken,
            'user_id'              => $user->id,
            'code'                  => $code ?? ""
        ], 'Two-factor authentication required', 200);
    }

    public function verify(Request $request, TwoFactorAuthenticationProvider $provider)
    {
        $request->validate([
            'login_token' => 'required|string',
            'code' => 'required|string'
        ]);

        $attempt = Cache::get("login_attempt:{$request->login_token}");

        if (!$attempt) {
            return $this->error('Expired login attempt', null, 422);
        }

        $user = User::find($attempt['user_id']);

        if (!$user) {
            return $this->error('User not found', null, 404);
        }

        $valid = false;

        if ($attempt['method'] === 'totp') {
            try {
                $valid = $provider->verify(decrypt($user->two_factor_secret), $request->code);
            } catch (\Exception $e) {
                return $this->error('Invalid 2FA secret', null, 422);
            }
        } elseif ($attempt['method'] === 'email') {
            $stored = decrypt($user->two_factor_email_code);
            $valid = $stored == $request->code;
        }

        if (!$valid) {
            return $this->error('Invalid 2FA code', null, 422);
        }

        // Success: delete cache & reset email code
        Cache::forget("login_attempt:{$request->login_token}");

        $user->update([
            'two_factor_email_code' => null,
            'two_factor_email_expires_at' => null
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'verified' => true,
            'user'     => new UserResource($user),
            'token'    => $token
        ], 'Login successful', 200);
    }
}
