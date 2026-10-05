<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Handles user authentication, including login and logout functionality.
 */
class LoginController extends Controller
{
    /**
     * Authenticate the user and start a session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string'
        ]);

        if (Auth::attempt([
            'name' => $credentials['name'],
            'password' => $credentials['password'],
            'can_login' => true
        ])) {
            $request->session()->regenerate();
            return redirect()->route('panel');
        }

        return redirect()->route('/')->with([
            'error' => [
                'message' => 'Usuario o Contraseña incorrecta',
                'type' => 'warning'
            ],
        ]);
    }

    /**
     * Log out the user and invalidate the session.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
