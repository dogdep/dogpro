<?php namespace App\Http\Controllers;

use App\Model\User;
use App\Model\UserSocialLogin;
use Illuminate\Http\JsonResponse;
use \Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => ['getIndex']]);
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return config('auth.providers');
    }

    /**
     * @param JWTAuth $jwt
     * @return \Illuminate\Http\Response
     */
    public function getStatus(JWTAuth $jwt)
    {
        if ($token = $jwt->getToken()) {
            return new JsonResponse(['user' => $jwt->toUser()]);
        }

        return new JsonResponse(null, 405);
    }

    /**
     * @param JWTAuth $jwt
     * @return \Illuminate\Http\Response
     */
    public function getIndex(JWTAuth $jwt)
    {
        if ($token = $jwt->getToken()) {
            $jwt->invalidate();
        }

        return new JsonResponse();
    }

    /**
     * @param JWTAuth $jwt
     * @return \Illuminate\Http\Response
     */
    public function getRefresh(JWTAuth $jwt)
    {
        try {
            $token = $jwt->refresh();
        } catch (JWTException $e) {
            return new JsonResponse(null, 401);
        }

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @param string $provider
     * @return \Illuminate\Http\Response
     */
    public function getLogin($provider)
    {
        if (!in_array($provider, $this->getProviders())) {
            return redirect('/login');
        }

        return \Socialite::driver($provider)->redirect();
    }

    /**
     * @param string $provider
     * @param JWTAuth $jwt
     * @return \Illuminate\Http\Response
     */
    public function getCallback($provider, JWTAuth $jwt)
    {
        if (!in_array($provider, $this->getProviders())) {
            return redirect('/login');
        }

        try {
            $user = \Socialite::driver($provider)->user();
            return $this->handleSocialLogin($jwt, $user, $provider);
        } catch (\Exception $e) {
            return redirect('/login?' . http_build_query(['error' => $e->getMessage()]));
        }
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $login
     * @return User
     */
    private function createOrFindUser($login)
    {
        if ($login->getEmail() && $user = User::whereEmail($login->getEmail())->first()) {
            return $user;
        }

        return User::create([
            'email' => $login->getEmail(),
            'name' => $login->getName() ?: $login->getNickname(),
            'nickname' => $login->getNickname(),
            'password' => Hash::make($login->getId() . time()),
            'avatar' => $login->getAvatar(),
            'admin' => User::count() == 0,
        ]);
    }

    /**
     * @param JWTAuth $jwt
     * @param \Laravel\Socialite\Contracts\User $login
     * @param string $provider
     * @return \Illuminate\Http\Response
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
            if ($token = $jwt->fromUser($user)) {
                return redirect("/login/handle/$token");
            }
        } catch (JWTException $e) {
            // return error on exception or empty token
        }

        return new JsonResponse(['Error creating JWT token'], 401);
    }
}
