<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {

        if (!auth()->attempt($request->only('email', 'password'))) {
            return Inertia::render('Auth/Login', [
                'errors' => [
                    'email' => 'بيانات الدخول email غير صحيحة',
                    'password' => 'بيانات الدخول password غير صحيحة',
                ]
            ]);
        }

        return redirect()->intended('/profile')
            ->with('success', 'تم تسجيل الدخول بنجاح');
    }

}
