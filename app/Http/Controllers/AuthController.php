<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use App\Services\SocialAccountService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Traits\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\UserService;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    use APIResponse;
    private $userService;
    protected $socialAccountService;

    public function __construct(SocialAccountService $socialAccountService, UserService $userService)
    {
        $this->socialAccountService = $socialAccountService;
        $this->userService = $userService;
    }

    public function redirectToTwitter(Request $request)
    {
        $twitter = new TwitterOAuth(
            env('TWITTER_CONSUMER_KEY', ''),
            env('TWITTER_CONSUMER_SECRET', '')
        );

        // Request token from Twitter
        $request_token = $twitter->oauth('oauth/request_token', [
            'oauth_callback' => route('login.twitter.callback') // this call the route login.twitter.callback ~ function handleTwitterCallback
        ]);

        // Store tokens in session
        Session::put('oauth_token', $request_token['oauth_token']);
        Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
        Session::put('auth_token', $request['access-token']); // access token to laravel project (bearer token)

        // Redirect user to Twitter for authentication
        return redirect($twitter->url('oauth/authorize', [
            'oauth_token' => $request_token['oauth_token']
        ]));
    }

    public function handleTwitterCallback(Request $request)
    {
        $oauth_token = session('oauth_token');
        $oauth_token_secret = session(key: 'oauth_token_secret');
        $auth_token = session('auth_token');

        if (!$oauth_token || !$oauth_token_secret) {
            return $this->responseError('Request token missing', 400);
        }

        if ($auth_token) {
            try {
                $decoded = JWT::decode($auth_token, new Key(env('JWT_SECRET'), 'HS256'));
                $user = User::find($decoded->sub);

                if ($user) {
                    Auth::login($user);
                }
            } catch (\Exception $e) {
                return $this->responseError('Invalid JWT Token', 401);
            }
        }

        $twitter = new TwitterOAuth(
            env('TWITTER_CONSUMER_KEY', ''),
            env('TWITTER_CONSUMER_SECRET', ''),
            $oauth_token,
            $oauth_token_secret
        );

        $access_token = $twitter->oauth("oauth/access_token", [
            "oauth_verifier" => $request->oauth_verifier
        ]);

        $user = auth()->user();

        if ($user) {
            $socialAccount = $this->socialAccountService->store($user->getAuthIdentifier(), $access_token, 'TWITTER');
            return "<script>
                window.opener.postMessage({
                    socialAccount: " . json_encode($socialAccount) . "
                }, 'http://localhost:3000');
                window.close(); 
            </script>";
        }

        return $this->responseError('User not authenticated', 401);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Auth::attempt => query the db with email, password => get the user with these credentials
        if (Auth::attempt($credentials)) {
            $user = Auth::user(); // => get the current authenticated user

            try {
                $access_token = JWTAuth::fromUser($user);

                return $this->responseSuccessWithData([
                    'user' => $user,
                    'access_token' => $access_token
                ]);
            } catch (JWTException $e) {
                return $this->responseError('Could not create token', 500);
            }
        }

        return $this->responseError('Unauthorized', 401);
    }

    public function register(UserStoreRequest $request)
    {
        $user = $this->userService->store($request->full_name, $request->email, $request->password);
        $access_token = JWTAuth::fromUser($user);
        $result = [
            'user' => $user,
            'access_token' => $access_token
        ];

        return $this->responseSuccessWithData($result, true);
    }

    public function redirectToLinkedin(Request $request)
    {
        Session::put('auth_token', $request['access-token']);

        $params = [
            'response_type' => 'code',
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'redirect_uri' => env('LINKEDIN_REDIRECT_URI'),
            'state' => 'random_string', // Dùng để bảo mật
            'scope' => 'openid,profile,email,w_member_social' // Phạm vi quyền
        ];

        $query = http_build_query($params);

        return redirect('https://www.linkedin.com/oauth/v2/authorization?' . $query);
    }

    public function handleLinkedinCallback(Request $request ) {
        $code = $request->get('code');
        $auth_token = session('auth_token');

        if (!$code) {
            return response()->json(['error' => 'Authorization code not received'], 400);
        }

        if ($auth_token) {
            try {
                $decoded = JWT::decode($auth_token, new Key(env('JWT_SECRET'), 'HS256'));
                $user = User::find($decoded->sub);

                if ($user) {
                    Auth::login($user);
                }
            } catch (\Exception $e) {
                return $this->responseError('Invalid JWT Token', 401);
            }
        }

        $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => env('LINKEDIN_REDIRECT_URI'),
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        ]);

        // echo $tokenResponse;
        if (!$tokenResponse->successful()) {
            return response()->json(['error' => 'Failed to retrieve access token'], 400);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        $userProfileResponse = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
        ])->get('https://api.linkedin.com/v2/userinfo');
        
        $userData = $userProfileResponse->json();

        $user = auth()->user();

        if ($user) {
            $socialAccount = $this->socialAccountService->store($user->getAuthIdentifier(), [
                "user_id" => $userData["sub"],
                "screen_name" => $userData["name"],
                "oauth_token" => $accessToken,
                "oauth_token_secret" => null
            ], "LINKEDIN");
            return "<script>
                window.opener.postMessage({
                    socialAccount: " . json_encode($socialAccount) . "
                }, 'http://localhost:3000');
                window.close(); 
            </script>";
        }
        return $this->responseError('User not authenticated', 401);
    }
}
