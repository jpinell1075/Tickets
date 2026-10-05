<?php

namespace App\Http\Controllers\AdministrationApp\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseFormRequest;
use Inertia\Inertia;
use App\Models\Users\Templates\UserTemplate;
use App\Models\Users\Templates\UserTemplateModule;
use App\Models\Users\Templates\UserTemplateModuleAction;
use App\Models\Users\Templates\UserTemplateFilter;
use Illuminate\Support\Facades\DB;
use App\Http\Services\UserTemplateServices;

class UserTemplateController extends Controller
{
    /**
     * Display a paginated list of users templates.
     *
     * @param int $pagination
     * @return \Inertia\Response
     */
    public function index($pagination = 20)
    {
        return Inertia::render('administration_app.users_templates.index', [
            'model' => UserTemplate::getStaticData(),
        ]);
    }

    /**
     * Display details of a specific user template.
     *
     * @param \App\Models\Users\Templates\UserTemplate $userTemplate
     * @return \Inertia\Response
     */
    public function show(UserTemplate $userTemplate)
    {
        $userTemplate->load(['modules.module', 'modules.actions', 'filters']);
        $available_user_filters = config('modelFilters');
        return Inertia::render('administration_app.users_templates.show', [
            'user_template' => $userTemplate,
            'available_user_filters' => $available_user_filters,
        ]);
    }

    /**
     * Store a new user template in the database.
     *
     * @param \App\Http\Requests\BaseFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BaseFormRequest $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres.',
        ]);
        

        $userTemplate = UserTemplate::create($request->only(['name', 'description']));

        if($userTemplate) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Plantilla de usuario creada exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->route('users.templates.index')->with([
            'message' => [
                'message' => 'Error al crear la plantilla de usuario.',
                'type' => 'error',
            ],
        ]);
    }

    /**
     * Update an existing user template.
     *
     * @param \App\Http\Requests\BaseFormRequest $request
     * @param \App\Models\Users\Templates\UserTemplate $userTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BaseFormRequest $request, UserTemplate $userTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres.',
        ]);

        if($userTemplate->update($request->only(['name', 'description']))){
            return redirect()->route('users.templates.show', ['userTemplate' => $userTemplate])->with([
                'message' => [
                    'message' => 'Plantilla de usuario actualizada exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->route('users.templates.index')->with([
            'message' => [
                'message' => 'Error al actualizar la plantilla de usuario.',
                'type' => 'error',
            ],
        ]);
    }

    /**
     * Delete a user template.
     *
     * @param \App\Models\Users\Templates\UserTemplate $userTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(UserTemplate $userTemplate)
    {
        if($userTemplate->delete()){
            return redirect()->route('users.templates.index')->with([
                'message' => [
                    'message' => 'Plantilla de usuario eliminada exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->route('users.templates.index')->with([
            'message' => [
                'message' => 'Error al eliminar la plantilla de usuario.',
                'type' => 'error',
            ],
        ]);
    }

    /**
     * Assign a module to a user template.
     * @param \App\Http\Requests\BaseFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addModule(BaseFormRequest $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'user_template_id' => 'required|exists:user_templates,id',
        ], [
            'module_id.required' => 'El módulo es obligatorio.',
            'module_id.exists' => 'El módulo seleccionado no existe.',
            'user_template_id.required' => 'La plantilla de usuario es obligatoria.',
            'user_template_id.exists' => 'La plantilla de usuario seleccionada no existe.',
        ]);

        if(UserTemplateModule::where('user_template_id', $request->user_template_id)
            ->where('module_id', $request->module_id)->exists()) {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'El módulo ya está asignado a la plantilla de usuario.',
                    'type' => 'warning',
                ],
            ]);
        }

        DB::beginTransaction();
        try{
            $userTemplateModule = UserTemplateModule::create([
                'user_template_id' => $request->user_template_id,
                'module_id' => $request->module_id,
            ]);

            foreach($request->actions as $action)
            {
                UserTemplateModuleAction::create([
                    'user_template_module_id' => $userTemplateModule->id,
                    'action' => $action,
                ]);
            }

            DB::commit();
            return redirect()->route('users.templates.show', ['userTemplate' => $request->user_template_id])->with([
                'message' => [
                    'message' => 'Módulo asignado a la plantilla de usuario exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al asignar el módulo a la plantilla de usuario: ' . $e->getMessage(),
                    'type' => 'error',
                ],
            ]);
        }
    }

    /**
     * Remove a module from a user template.
     *
     * @param \App\Models\Users\Templates\UserTemplateModule $userTemplateModule
     * @param \App\Models\Users\Templates\UserTemplate $userTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteModule(UserTemplateModule $userTemplateModule, UserTemplate $userTemplate)
    {
        if($userTemplateModule->delete()){
            return redirect()->route('users.templates.show', ['userTemplate' => $userTemplate])->with([
                'message' => [
                    'message' => 'Módulo eliminado de la plantilla de usuario exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->route('users.templates.show', ['userTemplate' => $userTemplate])->with([
            'message' => [
                'message' => 'Error al eliminar el módulo de la plantilla de usuario.',
                'type' => 'error',
            ],
        ]);
    }

    /**
     * Store a user template model filter.
     *
     * @param \App\Http\Requests\BaseFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUserTemplateModelFilter(BaseFormRequest $request, UserTemplate $userTemplate)
    {
        $request->validate([
            'comparison_type' => 'required',
        ], [
            'comparison_type.required' => 'El tipo de comparación es obligatorio',
        ]);

        $response = UserTemplateServices::addUserModelFilter($request, $userTemplate);

        if($response === null)
        {
            return redirect()->back()->with([
                'message' => [
                    'message' => 'Error al agregar el filtro',
                    'type' => 'danger',
                ],
            ]);
        }

        return redirect()->back()->with([
            'message' => [
                'message' => 'Filtro agregado correctamente',
                'type' => 'success',
            ],
        ]);
    }

    /**
     * Remove a user template model filter.
     *
     * @param \App\Models\Users\Templates\UserTemplateFilter $userTemplateFilter
     * @param \App\Models\Users\Templates\UserTemplate $userTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeUserTemplateModelFilter(UserTemplateFilter $userTemplateModelFilter, UserTemplate $userTemplate)
    {
        if($userTemplateModelFilter->delete()){
            return redirect()->route('users.templates.show', ['userTemplate' => $userTemplate])->with([
                'message' => [
                    'message' => 'Filtro eliminado de la plantilla de usuario exitosamente.',
                    'type' => 'success',
                ],
            ]);
        }

        return redirect()->route('users.templates.show', ['userTemplate' => $userTemplate])->with([
            'message' => [
                'message' => 'Error al eliminar el filtro de la plantilla de usuario.',
                'type' => 'error',
            ],
        ]);
    }
}
