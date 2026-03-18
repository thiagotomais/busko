<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\GuardianAuthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private DriverAuthService $driverAuthService,
        private GuardianAuthService $guardianAuthService
    ) {}

    /**
     * Show the login page
     */
    public function login(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request from web form
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'type' => 'required|in:driver,guardian',
        ]);

        $userType = UserType::from($validated['type']);
        $authService = $userType === UserType::DRIVER 
            ? $this->driverAuthService 
            : $this->guardianAuthService;

        $result = $authService->login(
            $validated['email'],
            $validated['password'],
            $userType
        );

        if ($result) {
            // Authenticate the user in the web session
            auth()->login($result['user']);
            $request->session()->regenerate();
            return redirect()->route('portal.dashboard')->with('success', 'Login realizado com sucesso!');
        }

        return back()
            ->withInput($request->only('email', 'type'))
            ->withErrors(['email' => 'As credenciais fornecidas estão incorretas.']);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('success', 'Logout realizado com sucesso!');
    }
}
