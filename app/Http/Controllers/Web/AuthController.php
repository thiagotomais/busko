<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\DriverAuthService;
use App\Services\Auth\GuardianAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        ]);

        $userType = $this->resolveUserTypeByEmail($validated['email']);
        if (!$userType) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'As credenciais fornecidas estão incorretas.']);
        }

        if ($userType === UserType::ADMIN) {
            $result = $this->adminLogin($validated['email'], $validated['password']);
        } else {
            $authService = $userType === UserType::DRIVER
                ? $this->driverAuthService
                : $this->guardianAuthService;

            $result = $authService->login(
                $validated['email'],
                $validated['password'],
                $userType
            );
        }

        if ($result) {
            // Authenticate the user in the web session
            auth()->login($result['user']);
            $request->session()->regenerate();
            return redirect()->route('portal.dashboard')->with('success', 'Login realizado com sucesso!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'As credenciais fornecidas estão incorretas.']);
    }

    /**
     * Resolve account profile type by email.
     */
    private function resolveUserTypeByEmail(string $email): ?UserType
    {
        $user = User::query()->where('email', $email)->first(['id', 'type']);

        if (!$user) {
            return null;
        }

        if ($user->type instanceof UserType) {
            return $user->type;
        }

        return is_string($user->type) ? UserType::tryFrom($user->type) : null;
    }

    /**
     * Authenticate a global admin user.
     */
    private function adminLogin(string $email, string $password): ?array
    {
        $user = User::with('tenant')
            ->where('email', $email)
            ->where('type', UserType::ADMIN)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return null;
        }

        if ($user->tenant && !$user->tenant->is_active) {
            return null;
        }

        if ($user->tenant_id) {
            app()->bind('current_tenant_id', fn() => $user->tenant_id);
        }

        return ['user' => $user];
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
