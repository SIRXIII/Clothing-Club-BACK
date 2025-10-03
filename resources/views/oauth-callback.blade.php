<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Callback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #F77F00;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        @if($success)
            <h2>Login Successful!</h2>
            <p>Redirecting you to the admin panel...</p>
        @else
            <h2>Login Failed</h2>
            <p>{{ $error ?? 'An error occurred during authentication.' }}</p>
            <p>Redirecting you back to login...</p>
        @endif
    </div>

    <script>
        // Handle OAuth callback
        @if($success)
            // Store authentication data
            const token = {!! json_encode($token) !!};
            const user = {!! json_encode($user) !!};
            const provider = {!! json_encode($provider) !!};
            
            // If this is opened in a popup, post message to parent
            if (window.opener) {
                window.opener.postMessage({
                    type: 'OAUTH_SUCCESS',
                    token: token,
                    user: user,
                    provider: provider
                }, '*');
                window.close();
            } else {
                try {
                    // Store token in localStorage and redirect to dashboard
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('auth_user', JSON.stringify(user));
                    localStorage.setItem('type', user.type || 'admin');
                    
                    // Trigger a storage event to notify other tabs/components
                    window.dispatchEvent(new Event('storage'));
                    
                    console.log('OAuth success, redirecting to dashboard...');
                    
                    // Add a small delay to ensure localStorage is written
                    setTimeout(() => {
                        const frontendUrl = {!! json_encode($frontendUrl) !!};
                        // Force a full page reload to ensure auth context is updated
                        window.location.href = `${frontendUrl}/`;
                    }, 200);
                } catch (error) {
                    console.error('Error storing OAuth data:', error);
                    // Fallback redirect
                    const frontendUrl = {!! json_encode($frontendUrl) !!};
                    window.location.href = `${frontendUrl}/login?error=storage_failed`;
                }
            }
        @else
            // Handle error
            const error = {!! json_encode($error ?? 'Authentication failed') !!};
            
            if (window.opener) {
                window.opener.postMessage({
                    type: 'OAUTH_ERROR',
                    error: error
                }, '*');
                window.close();
            } else {
                // Redirect to login with error
                const frontendUrl = {!! json_encode($frontendUrl) !!};
                const redirectUrl = `${frontendUrl}/login?error=${encodeURIComponent(error)}`;
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 3000);
            }
        @endif
    </script>
</body>
</html>
