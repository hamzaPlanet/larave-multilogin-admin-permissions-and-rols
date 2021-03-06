<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use mysql_xdevapi\Exception;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $providers = config('app.socialite_providers');
        $config    = [];

        foreach ($providers as $provider) {
            $config['services.'.$provider.'.client_id']     = setting($provider.'_client_id');
            $config['services.'.$provider.'.client_secret'] = setting($provider.'_client_secret');
            $config['services.'.$provider.'.redirect']      = setting($provider.'_redirect_url');
        }
        config($config);
    }

    public function redirectToProvider($provider)
    {

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {

        try {
            $social_user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect('/');
        }

        $user = User::where('provider', $provider)
                    ->where('provider_id', $social_user->getId())
                    ->first();

        if ( ! $user) {
            $user = User::create([
                'name'        => $social_user->getName(),
                'email'       => $social_user->getEmail(),
                'password'    => bcrypt($social_user->getId()),
                'provider_id' => $social_user->getId(),
                'provider'    => $provider,
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
