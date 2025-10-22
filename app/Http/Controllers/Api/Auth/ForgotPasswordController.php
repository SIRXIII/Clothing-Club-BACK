<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Models\Partner;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    /**
     * Send password reset link to user's email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:partners,email'
        ]);

        $user = Partner::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('User not found', null, 404);
        }

        // Generate reset token
        $token = Str::random(64);

        // Store token in password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send reset email
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
            
            return $this->success(
                ['email' => $request->email],
                'Password reset link sent to your email',
                200
            );
        } catch (\Exception $e) {
            return $this->error('Failed to send reset email', $e->getMessage(), 500);
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:partners,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        // Find password reset record
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return $this->error('Invalid reset token', null, 422);
        }

        // Check if token matches
        if (!Hash::check($request->token, $passwordReset->token)) {
            return $this->error('Invalid reset token', null, 422);
        }

        // Check if token is not expired (24 hours)
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            return $this->error('Reset token has expired', null, 422);
        }

        // Update user password
        $user = Partner::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete password reset record
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return $this->success(
            null,
            'Password reset successfully',
            200
        );
    }
}
