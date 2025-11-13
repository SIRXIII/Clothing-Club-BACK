<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\UserResource;
use App\Models\Partner;
use App\Models\User;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LoginController extends Controller
{
    use ApiResponse;


    /**
     * Handle the incoming request to log in a user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success([
                'user' => new UserResource($user),
                'token' => $token,

            ], 'Login successful', 200);
        }

        return $this->error('Invalid credentials', null, 401);
    }
    /**
     * Handle the incoming request to log out a user.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout successful', 200);
    }


    public function updateProfile(Request $request)
    {

        $validated = $request->validate([
            'user_id'           => 'required|exists:partners,id',
            'name'        => 'required|string|max:255',
            // 'last_name'         => 'nullable|string|max:255',
            'email'             => 'required|email|unique:partners,email,' . $request->user_id,
            'phone'             => ['nullable', 'regex:/^[0-9-]{7,20}$/'],
            'profile_photo'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        $user = Partner::findOrFail($validated['user_id']);


        $user->name = $validated['name'];
        // $user->last_name  = $validated['last_name'] ?? null;
        $user->email      = $validated['email'];
        $user->phone      = $validated['phone'] ?? null;


        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo from storage
            if ($user->profile_photo && Storage::disk('hetzner')->exists($user->profile_photo)) {
                Storage::disk('hetzner')->delete($user->profile_photo);
            }
            
            // Store in partner-specific folder
            $path = $request->file('profile_photo')->store("partners/profile/{$user->id}", 'hetzner');
            $user->profile_photo = $path;
        }

        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'user'    => new PartnerResource($user)
        ]);
    }


    public function updatePassword(Request $request)
    {
        // Find user first to check if they have a password
        $user = Partner::find($request->user_id);
        
        if (!$user) {
            return response()->json([
                'status' => 'error', 
                'message' => 'User not found'
            ], 404);
        }

        // Check if user has an existing password (regular login) or not (OAuth login)
        $hasPassword = !empty($user->password);

        // Validation rules based on whether user has password or not
        $rules = [
            'user_id'       => 'required|exists:partners,id',
            'newpassword'   => 'required|string|min:6',
            'confirmpassword' => 'required|string|same:newpassword',
        ];

        // Only require old password if user already has a password
        if ($hasPassword) {
            $rules['oldpassword'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Verify old password only if user has an existing password
        if ($hasPassword) {
            if (!Hash::check($validated['oldpassword'], $user->password)) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Old password is incorrect'
                ], 422);
            }
        }

        // Update password
        $user->password = Hash::make($validated['newpassword']);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => $hasPassword ? 'Password updated successfully' : 'Password set successfully',
            'user' => new PartnerResource($user),
        ]);
    }
}
