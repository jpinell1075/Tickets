<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Requests\BaseFormRequest;
use App\Models\Configurations\Configuration;

class ProfilesController extends Controller
{
    public function index()
    {
        return Inertia::render('profile.index', [
            'user' => auth()->user()->load([
                'userModule.actions', 
                'userModule.module', 
                'userModelFilters',
                'userProviders',
            ]),
            'global_use_social_login' => Configuration::where('key', 'global_use_social_login')->first()?->typed_value ?? false,
        ]);
    }

    public function update(BaseFormRequest $request)
    {
        $user = auth()->user();
        $user->name = $request->name;
        $user->email = $request->email;
        
        if($user->save()){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Perfil actualizado correctamente',
                    'type' => 'success'
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Error al actualizar el perfil',
                'type' => 'danger'
            ],
        ]);
    }

    public function changePassword(BaseFormRequest $request)
    {
        if($request->new_password != $request->confirm_new_password){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Las contraseñas no coinciden',
                    'type' => 'danger'
                ],
            ]);
        }

        $user = auth()->user();
        $user->password = bcrypt($request->new_password);
        
        if($user->save()){
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Contraseña actualizada correctamente',
                    'type' => 'success'
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Error al actualizar la contraseña',
                'type' => 'danger'
            ],
        ]);
    }
}
