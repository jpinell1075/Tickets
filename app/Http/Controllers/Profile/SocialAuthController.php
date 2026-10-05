<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Configurations\Configuration;
use App\Models\Users\UserProvider;

class SocialAuthController extends Controller
{
    protected $global_use_social_login;

    public function __construct()
    {
        $this->global_use_social_login = Configuration::where('key', 'global_use_social_login')->first()?->typed_value ?? null;
    }

    public function redirect($provider, $state = 'link')
    {
        if(!$this->global_use_social_login){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'El inicio de sesión con redes sociales no está habilitado.',
                    'type' => 'danger'
                ],
            ]);
        }

        return Socialite::driver($provider)
        ->stateless()
        ->with(['state' => $state])
        ->redirect();
    }

    public function callback($provider)
    {
        if(!$this->global_use_social_login){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'El inicio de sesión con redes sociales no está habilitado.',
                    'type' => 'danger'
                ],
            ]);
        }

        $state = request('state');

        if($state === 'link'){
            try{
                $socialite_user = Socialite::driver($provider)
                ->stateless()->user();
                
                if(!$socialite_user || !$socialite_user->getId()){
                    return redirect()->route('profile.index')->with([
                        'message' => [
                            'message' => 'Error al obtener los datos del usuario de '.$provider,
                            'type' => 'danger'
                        ],
                    ]);
                }
            }
            catch(\Exception $e){

                return redirect()->route('profile.index')->with([
                    'message' => [
                        'message' => 'Error al autenticar con '.$provider,
                        'type' => 'danger'
                    ],
                ]);
            }

            $user = auth()->user();

            if(!$user){
                return redirect()->route('profile.index')->with([
                    'message' => [
                        'message' => 'No se encontró el usuario autenticado',
                        'type' => 'danger'
                    ],
                ]);
            }

            // Check if the social account is already linked to another user
            if(
                UserProvider::where('provider_name', $provider)
                ->where('provider_id', $socialite_user->getId())
                ->exists()
            ){
                return redirect()->route('profile.index')->with([
                    'message' => [
                        'message' => 'Esta cuenta de '.$provider.' ya está vinculada.',
                        'type' => 'danger'
                    ],
                ]);
            }

            // Link the social account to the authenticated user
            $user_provider = new UserProvider();
            $user_provider->user_id = $user->id;
            $user_provider->provider_name = $provider;
            $user_provider->provider_id = $socialite_user->getId();
            $user_provider->provider_email = $socialite_user->getEmail();

            if(!$user_provider->save()){
                return redirect()->route('profile.index')->with([
                    'message' => [
                        'message' => 'Error al vincular la cuenta de '.$provider,
                        'type' => 'danger'
                    ],
                ]);
            }

            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'Cuenta de '.$provider.' vinculada exitosamente.',
                    'type' => 'success'
                ],
            ]);
        }
        else if($state === 'login'){
            try{
                $socialite_user = Socialite::driver($provider)
                ->stateless()->user();
                
                if(!$socialite_user || !$socialite_user->getId()){
                    return redirect()->route('/')->with([
                        'error' => [
                            'message' => 'Error al obtener los datos del usuario de '.$provider,
                            'type' => 'danger'
                        ],
                    ]);
                }
            }
            catch(\Exception $e){

                return redirect()->route('/')->with([
                    'error' => [
                        'message' => 'Error al autenticar con '.$provider,
                        'type' => 'danger'
                    ],
                ]);
            }

            // Find the user by the social account
            $user_provider = UserProvider::where('provider_name', $provider)
                ->where('provider_id', $socialite_user->getId())
                ->first();

            if(!$user_provider){
                return redirect()->route('/')->with([
                    'error' => [
                        'message' => 'No se encontró una cuenta vinculada con '.$provider,
                        'type' => 'danger'
                    ],
                ]);
            }

            if(!$user_provider->user || !$user_provider->user->can_login){
                return redirect()->route('/')->with([
                    'error' => [
                        'message' => 'El usuario asociado a esta cuenta de '.$provider.' no tiene permiso para iniciar sesión',
                        'type' => 'danger'
                    ],
                ]);
            }

            // Log in the user
            auth()->login($user_provider->user);
            request()->session()->regenerate();
            return redirect()->route('panel');
        }
    }

    public function removeProvider(UserProvider $userProvider)
    {
        if(!$this->global_use_social_login){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'El inicio de sesión con redes sociales no está habilitado.',
                    'type' => 'danger'
                ],
            ]);
        }

        $user = auth()->user();

        if(!$user){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'Error al eliminar el proveedor',
                    'type' => 'danger'
                ],
            ]);
        }

        if($userProvider->user_id != $user->id){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'No tienes permiso para eliminar este proveedor',
                    'type' => 'danger'
                ],
            ]);
        }

        if($userProvider->delete()){
            return redirect()->route('profile.index')->with([
                'message' => [
                    'message' => 'Proveedor eliminado correctamente',
                    'type' => 'success'
                ],
            ]);
        }

        return redirect()->route('profile.index')->with([
            'message' => [
                'message' => 'Error al eliminar el proveedor',
                'type' => 'danger'
            ],
        ]);
    }
}
