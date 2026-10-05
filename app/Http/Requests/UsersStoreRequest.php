<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class UsersStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if the user is authorized, otherwise false.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'password' => 'required',
            'role' => 'required|in:0,1',
            'can_login' => 'required|in:0,1',
            'estado' => 'required|in:activo,inactivo',
            'id_area' => 'nullable|exists:areas,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string> The custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de usuario es requerido',
            'password.required' => 'La contraseña es requerida',
            'role.required' => 'El rol es requerido',
            'role.in' => 'El rol seleccionado no es válido',
            'can_login.required' => 'El campo "Puede iniciar sesión" es requerido',
            'can_login.in' => 'El valor del campo "Puede iniciar sesión" no es válido',
            'estado.required' => 'El estado es requerido',
            'estado.in' => 'El estado seleccionado no es válido',
            'id_area.exists' => 'El área seleccionada no existe',
        ];
    }
}