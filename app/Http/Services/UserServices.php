<?php
namespace App\Http\Services;

use App\Models\User;
use App\Models\Users\Templates\UserTemplate;
use App\Models\Users\UserModule;
use App\Models\Users\UserModuleAction;
use App\Models\Users\UserModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserServices
{
    /**
     * Create a new user with the provided request data.
     * If the user has a role of 1, modules can also be assigned.
     *
     * @param \Illuminate\Http\Request $request
     * @return User|null The created user or null if creation fails.
     */
    public static function makeUser($request)
    {
        DB::beginTransaction();
        
        try{
            if($request->role === 1)
            {
                $user = User::create([
                    'name' => $request['name'],
                    'password' =>  Hash::make($request['password']),
                    'role' => $request['role'],
                    'it_staff' => $request['it_staff'] ?? 0,
                    'can_login' => $request['can_login'] == 1 ? true : false,
                    'email' => $request['email'] ?? null,
                    'nombre' => $request['nombre'],
                    'telefono' => $request['telefono'] ?? null,
                    'especialidad' => $request['especialidad'] ?? null,
                    'estado' => $request['estado'],
                    'id_area' => $request['id_area'] ?? null,
                ]); 

                if($request->modules !== null)
                {
                    foreach($request->modules as $module)
                    {
                        $user_module = UserModule::create([
                            'user_id' => $user->id,
                            'module_id' => $module['module_id'],
                        ]);
                        foreach($module['actions'] as $action)
                        {
                            UserModuleAction::create([
                                'user_module_id' => $user_module->id,
                                'action' => $action,
                            ]);
                        }
                    }
                }

                DB::commit();
                return $user;
            }
            else if($request->role === 0)
            {
                $user = User::create([
                    'name' => $request['name'],
                    'password' => bcrypt($request['password']),
                    'role' => $request['role'],
                    'it_staff' => $request['it_staff'] ?? 0,
                    'can_login' => $request['can_login'] == 1 ? true : false,
                    'email' => $request['email'] ?? null,
                    'nombre' => $request['nombre'],
                    'telefono' => $request['telefono'] ?? null,
                    'especialidad' => $request['especialidad'] ?? null,
                    'estado' => $request['estado'],
                    'id_area' => $request['id_area'] ?? null,
                ]);

                DB::commit();
                return $user;
            }
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Create a new user from a template.
     *
     * @param \Illuminate\Http\Request $request
     * @return User|null The created user or null if creation fails.
     */
    public static function makeUserFromTemplate($request)
    {
        $template = UserTemplate::find($request->user_template_id);

        if(!$template)
        {
            return null;
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'role' => $template->role,
                'can_login' => $request->can_login,
            ]);

            if($template->modules)
            {
                foreach($template->modules as $module)
                {
                    $user_module = UserModule::create([
                        'user_id' => $user->id,
                        'module_id' => $module->module_id,
                    ]);
                    foreach($module->actions as $action)
                    {
                        UserModuleAction::create([
                            'user_module_id' => $user_module->id,
                            'action' => $action->action,
                        ]);
                    }
                }
            }

            if($template->filters)
            {
                foreach($template->filters as $filter)
                {
                    UserModelFilter::create([
                        'user_id' => $user->id,
                        'comparison_type' => $filter->comparison_type,
                        'model' => $filter->model,
                        'field' => $filter->field,
                        'operator' => $filter->operator,
                        'value' => $filter->value,
                        'relation' => $filter->relation,
                        'extra' => $filter->extra,
                    ]);
                }
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Update an existing user's information.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return User|null The updated user or null if update fails.
     */
    public static function updateUser($request, User $user)
    {
        $user->name = $request->name;
        $can_login = $request->can_login == 1 ? true : false;
        $user->can_login = $can_login ?? $user->can_login;
        $user->it_staff = $request->it_staff ?? $user->it_staff;
        $user->email = $request->email ?? $user->email;
        $user->nombre = $request->nombre ?? $user->nombre;
        $user->telefono = $request->telefono ?? $user->telefono;
        $user->especialidad = $request->especialidad ?? $user->especialidad;
        $user->estado = $request->estado ?? $user->estado;
        $user->id_area = $request->id_area ?? $user->id_area;
        if($request->password !== null)
        {
            $user->password = bcrypt($request->password);
        }
        
        if($user->save())
        {
            return $user;
        }

        return null;
    }

    /**
     * Add a module to a user if the user has the appropriate role.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return UserModule|bool The created UserModule or false if addition fails.
     */
    public static function addModule($request, User $user)
    {
        if($user->role === '0')
        {
            return false;
        }

        if(UserModule::where('user_id', $user->id)->where('module_id', $request->module_id)->exists())
        {
            return false;
        }

        DB::beginTransaction();

        try {
            $user_module = UserModule::create([
                'user_id' => $user->id,
                'module_id' => $request->module_id,
            ]);

            foreach($request->actions as $action)
            {
                UserModuleAction::create([
                    'user_module_id' => $user_module->id,
                    'action' => $action,
                ]);
            }

            DB::commit();
            return $user_module;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function addUserModelFilter($request, User $user)
    {
        DB::beginTransaction();

        try {
            if($request->comparison_type == "simple")
            {
                $filter = UserModelFilter::create([
                    'user_id' => $user->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "relations")
            {
                $filter = UserModelFilter::create([
                    'user_id' => $user->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'relation' => $request->relation,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                    'relation' => $request->relation,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "functions")
            {
                $filter = UserModelFilter::create([
                    'user_id' => $user->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'field' => $request->field,
                    'operator' => $request->operator,
                    'value' => $request->value,
                    'extra' => $request->extra,
                ]);

                DB::commit();
                return $filter;
            }

            if($request->comparison_type == "user_own")
            {
                $filter = UserModelFilter::create([
                    'user_id' => $user->id,
                    'comparison_type' => $request->comparison_type,
                    'model' => $request->model,
                    'extra' => $request->extra,
                ]);

                DB::commit();
                return $filter;
            }

            DB::rollBack();
            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }
}