<?php namespace App\Http\Controllers;

use App\Model\User;
use App\Model\UserSocialLogin;
use Illuminate\Http\JsonResponse;
use \Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => ['getIndex']]);
    }

    public function getProviders()
    {
        return config('auth.providers');
    }

    public function getStatus(JWTAuth $jwt)
    {
        if ($token = $jwt->getToken()) {
            return new JsonResponse(['user' => $jwt->toUser($token)]);
        }

        return new JsonResponse(null, 405);
    }

    public function getIndex(JWTAuth $jwt)
    {
        if ($token = $jwt->getToken()) {
            $jwt->invalidate($token);
        }

        return new JsonResponse();
    }

    public function getRefresh(JWTAuth $jwt)
    {
        try {
            $token = $jwt->refresh($jwt->getToken());
        } catch (JWTException $e) {
            return new JsonResponse(null, 401);
        }

        return new JsonResponse(['token' => $token]);
    }

    public function getLogin($provider)
    {
        if (!in_array($provider, $this->getProviders())) {
            return redirect('/login');
        }

        return \Socialite::driver($provider)->redirect();
    }

    public function getCallback($provider, JWTAuth $jwt)
    {
        if (!in_array($provider, $this->getProviders())) {
            return redirect('/login');
        }

        try {
            $user = \Socialite::driver($provider)->user();
            return $this->handleSocialLogin($jwt, $user, $provider);
        } catch (\Exception $e) {
            return redirect('/login?' . http_build_query(['error'=>$e->getMessage()]));
        }
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $login
     * @return static
     */
    private function createOrFindUser($login)
    {
        if ($login->getEmail() && $user = User::whereEmail($login->getEmail())->first()) {
            return $user;
        }

        return User::create([
            'email' => $login->getEmail(),
            'name' => $login->getName() ? : $login->getNickname(),
            'nickname' => $login->getNickname(),
            'password' => Hash::make($login->getId() . time()),
            'avatar' => $login->getAvatar(),
            'admin' => User::count() == 0,
        ]);
    }

    /**
     * @param JWTAuth $jwt
     * @param \Laravel\Socialite\Contracts\User $login
     * @return JsonResponse
     */
    private function handleSocialLogin(JWTAuth $jwt, $login, $provider)
    {
        $token = UserSocialLogin::firstOrNew([
            'token' => $login->getId(),
            'provider' => $provider,
        ]);

        if (!$token->exists) {
            $user = $this->createOrFindUser($login);
            $token->user_id = $user->id;
            $token->data = json_encode($login);
        } else {
            $user = $token->user;
        }

        $token->save();

        try {
            if ($token = $jwt->fromUser($user, ['user' => $user])) {
                return redirect("/login/handle/$token");
            }
        } catch (JWTException $e) {
        }

        return new JsonResponse(['Error creating JWT token'], 401);
    }
}
