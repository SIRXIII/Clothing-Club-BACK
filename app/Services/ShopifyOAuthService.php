<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class ShopifyOAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $scopes;

    public function __construct()
    {
        $this->clientId = config('services.shopify.client_id');
        $this->clientSecret = config('services.shopify.client_secret');
        $this->redirectUri = config('services.shopify.redirect', 'https://travelclothingclub-admin.online/api/social/shopify/callback');
        $this->scopes = 'read_products,read_orders,read_customers';
        
        if (!$this->clientId || !$this->clientSecret) {
            throw new Exception('Shopify OAuth credentials not configured');
        }
    }

    public function getAuthUrl($shop)
    {
        $params = [
            'client_id' => $this->clientId,
            'scope' => $this->scopes,
            'redirect_uri' => $this->redirectUri,
            'state' => csrf_token(),
        ];

        return "https://{$shop}/admin/oauth/authorize?" . http_build_query($params);
    }

    public function handleCallback(Request $request)
    {
        $shop = $request->get('shop');
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$shop || !$code) {
            throw new Exception('Missing required parameters: shop and code are required');
        }

        // Validate shop domain format
        if (!preg_match('/^[a-zA-Z0-9-]+\.myshopify\.com$/', $shop)) {
            throw new Exception('Invalid shop domain format');
        }

        try {
            // Exchange code for access token
            $response = Http::timeout(30)->post("https://{$shop}/admin/oauth/access_token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error_description'] ?? $errorBody['error'] ?? 'Failed to exchange code for access token';
                throw new Exception("Shopify OAuth error: {$errorMessage}");
            }

            $tokenData = $response->json();
            
            if (!isset($tokenData['access_token'])) {
                throw new Exception('No access token received from Shopify');
            }
            
            $accessToken = $tokenData['access_token'];

            // Get shop information
            $shopResponse = Http::timeout(30)->withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shop}/admin/api/2023-10/shop.json");

            if (!$shopResponse->successful()) {
                throw new Exception('Failed to get shop information from Shopify API');
            }

            $shopResponseData = $shopResponse->json();
            
            if (!isset($shopResponseData['shop'])) {
                throw new Exception('Invalid response from Shopify shop API');
            }
            
            $shopData = $shopResponseData['shop'];

            return (object) [
                'id' => $shopData['id'],
                'name' => $shopData['name'] ?? $shopData['shop_owner'] ?? 'Shopify User',
                'email' => $shopData['email'] ?? $shopData['customer_email'] ?? null,
                'avatar' => null,
                'token' => $accessToken,
                'refreshToken' => null,
                'shop' => $shop,
            ];
        } catch (\Exception $e) {
            throw new Exception("Shopify authentication failed: " . $e->getMessage());
        }
    }
}
