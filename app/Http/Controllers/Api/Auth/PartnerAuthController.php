<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PartnerAuthController extends Controller
{
   public function login(Request $request)
    {
        $partner = Partner::where('email', $request->email)->first();

        if (!$partner || !Hash::check($request->password, $partner->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $partner->createToken('partner-token', ['partner'])->plainTextToken;

        return response()->json([
            'user' => $partner,
            'token' => $token,
            'type' => 'partner',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user('partner')->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
