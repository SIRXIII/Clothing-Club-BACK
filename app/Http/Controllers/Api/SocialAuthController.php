<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Http\Resources\UserResource;
use App\Models\Partner;
use App\Models\User;
use App\Services\ShopifyOAuthService;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use ApiResponse;



    public function redirectToProvider($provider, Request $request)
    {
        $this->validateProvider($provider);

        try {
            // Handle Shopify separately
            if ($provider === 'shopify') {
                $shop = $request->get('shop');
                if (!$shop) {
                    return $this->error("Shop domain is required for Shopify OAuth", null, 422);
                }

                // Validate shop domain format
                if (!preg_match('/^[a-zA-Z0-9-]+\.myshopify\.com$/', $shop)) {
                    return $this->error("Invalid shop domain format", null, 422);
                }

                try {
                    $shopifyService = new ShopifyOAuthService();
                    $authUrl = $shopifyService->getAuthUrl($shop);
                    return redirect($authUrl);
                } catch (\Exception $e) {
                    return $this->error("Shopify OAuth configuration error", $e->getMessage(), 500);
                }
            }

            // Handle other providers (Google, Facebook)
            $driver = Socialite::driver($provider)->stateless();
            return $driver->redirect();
        } catch (\Exception $e) {
            return $this->error("Failed to redirect to {$provider}", $e->getMessage(), 500);
        }
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback($provider, Request $request)
    {
        $this->validateProvider($provider);

        try {
            // Handle Shopify separately
            if ($provider === 'shopify') {
                $shopifyService = new ShopifyOAuthService();
                $socialUser = $shopifyService->handleCallback($request);
            } else {
                // Handle other providers (Google, Facebook)
                $socialUser = Socialite::driver($provider)->stateless()->user();
            }

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Generate token
            $token = $user->createToken("{$provider}-login")->plainTextToken;

            // Return HTML page that will post message to parent window (for popup flow)
            // or redirect to frontend for direct flow
            $frontendUrl = env('FRONTEND_URL', 'https://travelclothingclub-partner.online');

            return response()->view('oauth-callback', [
                'token' => $token,
                'user' => new PartnerResource($user),
                'provider' => $provider,
                'frontendUrl' => $frontendUrl,
                'success' => true
            ]);

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'https://travelclothingclub-partner.online');

            return response()->view('oauth-callback', [
                'error' => $e->getMessage(),
                'frontendUrl' => $frontendUrl,
                'success' => false
            ]);
        }
    }

    /**
     * Handle OAuth callback (for web redirects)
     */
    public function callback(Request $request, $provider = 'google')
    {
        $this->validateProvider($provider);

        try {
            // Get user from provider
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $user = $this->findOrCreateUser($socialUser, $provider);

            // Generate token
            $token = $user->createToken("{$provider}-login")->plainTextToken;

            // Redirect to frontend with token and success status
            $frontendUrl = env('FRONTEND_URL', 'https://travelclothingclub-partner.online');
            return redirect("{$frontendUrl}/oauth/callback?token={$token}&login=success&provider={$provider}");

        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'https://travelclothingclub-partner.online');
            return redirect("{$frontendUrl}/login?error=" . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle social login with access token (for mobile apps)
     */
    public function loginWithToken(Request $request, $provider)
    {
        $this->validateProvider($provider);

        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            // Get user from provider using access token
            $socialUser = Socialite::driver($provider)->userFromToken($request->access_token);

            // Find or create user
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Generate token
            $token = $user->createToken('api')->plainTextToken;

            return $this->success([
                'user' => new PartnerResource($user),
                'token' => $token,
            ], 'Social login successful', 200);

        } catch (\Exception $e) {
            return $this->error("Social authentication failed", $e->getMessage(), 422);
        }
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // Handle different provider data structures
        $email = $provider === 'shopify' ? $socialUser->email : $socialUser->getEmail();
        $id = $provider === 'shopify' ? $socialUser->id : $socialUser->getId();
        $name = $provider === 'shopify' ? $socialUser->name : $socialUser->getName();
        $avatar = $provider === 'shopify' ? $socialUser->avatar : $socialUser->getAvatar();

        // Parse name from social user
        $nameData = $this->parseUserName($name);

        // First, try to find user by provider and provider_id
        $user = Partner::where('provider', $provider)
                   ->where('provider_id', $id)
                   ->first();

        if ($user) {
            // Update user info including name fields
            $user->update([
                'name' => $nameData['name'],
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'avatar' => $avatar ?? $user->avatar,

            ]);
            return $user;
        }

        // Try to find user by email
        $user = Partner::where('email', $email)->first();

        if ($user) {
            // Link social account to existing user and update name fields
            $user->update([
                'name' => $nameData['name'],

                'provider' => $provider,
                'provider_id' => $id,
                'provider_token' => $socialUser->token ?? null,
                'provider_refresh_token' => $socialUser->refreshToken ?? null,
                'avatar' => $avatar ?? $user->avatar,

            ]);
            return $user;
        }

        // Create new user
        return Partner::create([
            'name' => $nameData['name'],

            'email' => $email,
            'provider' => $provider,
            'provider_id' => $id,
            'provider_token' => $socialUser->token ?? null,
            'provider_refresh_token' => $socialUser->refreshToken ?? null,
            'avatar' => $avatar,
            'email_verified_at' => now(), // Social accounts are considered verified

        ]);
    }

    /**
     * Get first name from full name
     */
    private function getFirstName($fullName)
    {
        if (empty($fullName)) return 'Social';
        $nameParts = explode(' ', trim($fullName));
        return $nameParts[0];
    }

    /**
     * Get last name from full name
     */
    private function getLastName($fullName)
    {
        if (empty($fullName)) return 'User';
        $nameParts = explode(' ', trim($fullName));
        if (count($nameParts) === 1) return '';
        return implode(' ', array_slice($nameParts, 1));
    }

    /**
     * Parse user name into first name, last name, and full name
     */
    private function parseUserName($fullName)
    {
        if (empty($fullName)) {
            return [
                'name' => 'Social User',

            ];
        }

        $nameParts = explode(' ', trim($fullName));

        if (count($nameParts) === 1) {
            return [
                'name' => $nameParts[0],

            ];
        }

        $firstName = $nameParts[0];
        $lastName = implode(' ', array_slice($nameParts, 1));

        return [
            'name' => $firstName . $lastName,

        ];
    }

    /**
     * Validate social provider
     */
    private function validateProvider($provider)
    {
        $allowedProviders = ['google', 'facebook', 'shopify'];

        if (!in_array($provider, $allowedProviders)) {
            abort(422, 'Invalid social provider');
        }
    }

    /**
     * Unlink social account
     */
    public function unlinkSocialAccount(Request $request)
    {
        $user = $request->user();

        if (!$user->provider) {
            return $this->error('No social account linked', null, 422);
        }

        // Don't allow unlinking if user has no password (social-only account)
        if (!$user->password) {
            return $this->error('Cannot unlink social account. Please set a password first.', null, 422);
        }

        $user->update([
            'provider' => null,
            'provider_id' => null,
            'provider_token' => null,
            'provider_refresh_token' => null,
        ]);

        return $this->success([
            'user' => new PartnerResource($user)
        ], 'Social account unlinked successfully', 200);
    }

    /**
     * Get available social providers
     */
    public function getProviders()
    {
        return $this->success([
            'providers' => [
                'google' => [
                    'name' => 'Google',
                    'enabled' => !empty(config('services.google.client_id')),
                ],
                'facebook' => [
                    'name' => 'Facebook',
                    'enabled' => !empty(config('services.facebook.client_id')),
                ],
                'shopify' => [
                    'name' => 'Shopify',
                    'enabled' => !empty(config('services.shopify.client_id')),
                ],
            ]
        ], 'Available social providers', 200);
    }
}
