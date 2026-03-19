<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Guardian;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\ValidCnh;
use App\Rules\ValidCpf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard home page
     */
    public function index(): View
    {
        $user = auth()->user();
        $tenant = $this->isSystemAdmin() ? null : $this->currentTenant();
        
        $stats = [
            'drivers' => Driver::count(),
            'guardians' => Guardian::count(),
            'users' => User::count(),
        ];

        return view('dashboard.index', compact('user', 'stats', 'tenant'));
    }

    /**
     * Show transport company management.
     */
    public function company(): View
    {
        if ($this->isSystemAdmin()) {
            $tenants = Tenant::withCount(['drivers', 'guardians', 'users'])
                ->orderBy('id', 'desc')
                ->paginate(15);

            return view('dashboard.company.index', compact('tenants'));
        }

        $tenant = $this->currentTenant()->loadCount(['drivers', 'guardians', 'addresses']);

        return view('dashboard.company.show', compact('tenant'));
    }

    /**
     * Show admin company edit page.
     */
    public function companyEdit(Tenant $tenant): View
    {
        abort_unless($this->isSystemAdmin(), 403, 'Acesso não autorizado.');

        $tenant->loadCount(['drivers', 'guardians', 'users']);

        return view('dashboard.company.edit', compact('tenant'));
    }

    /**
     * Update company details as system admin.
     */
    public function companyAdminUpdate(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($this->isSystemAdmin(), 403, 'Acesso não autorizado.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenant->id),
            ],
            'bank_code' => ['nullable', 'string', 'max:10'],
            'bank_branch' => ['nullable', 'string', 'max:10'],
            'bank_account' => ['nullable', 'string', 'max:20'],
            'bank_account_type' => ['nullable', 'in:corrente,poupanca'],
            'pix_key' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant->update($validated);

        return redirect()->route('portal.company.index')
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    /**
     * Toggle company status as system admin.
     */
    public function companyAdminToggleStatus(Tenant $tenant): RedirectResponse
    {
        abort_unless($this->isSystemAdmin(), 403, 'Acesso não autorizado.');

        $tenant->update(['is_active' => !$tenant->is_active]);

        return redirect()->route('portal.company.index')
            ->with('success', $tenant->is_active
                ? 'Empresa ativada com sucesso.'
                : 'Empresa desativada com sucesso.');
    }

    /**
     * Update transport company details.
     */
    public function companyUpdate(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('tenants', 'slug')->ignore($tenant->id),
            ],
            'bank_code' => ['nullable', 'string', 'max:10'],
            'bank_branch' => ['nullable', 'string', 'max:10'],
            'bank_account' => ['nullable', 'string', 'max:20'],
            'bank_account_type' => ['nullable', 'in:corrente,poupanca'],
            'pix_key' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant->update($validated);

        return redirect()->route('portal.company.show')
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    /**
     * Toggle company status.
     */
    public function companyToggleStatus(): RedirectResponse
    {
        $tenant = $this->currentTenant();
        $tenant->update(['is_active' => !$tenant->is_active]);

        return redirect()->route('portal.company.show')
            ->with('success', $tenant->is_active
                ? 'Empresa ativada com sucesso.'
                : 'Empresa desativada com sucesso.');
    }

    /**
     * Show drivers list
     */
    public function drivers(): View
    {
        $drivers = Driver::with(['user', 'address', 'tenant'])->paginate(15);
        return view('dashboard.drivers.index', compact('drivers'));
    }

    /**
     * Show driver registration form.
     */
    public function driverCreate(): View
    {
        return view('dashboard.drivers.create');
    }

    /**
     * Register a new driver for the current transport company.
     */
    public function driverStore(Request $request): RedirectResponse
    {
        $tenant = $this->currentTenant();

        $request->merge([
            'cpf' => preg_replace('/\D/', '', (string) $request->input('cpf')),
            'cnh' => preg_replace('/\D/', '', (string) $request->input('cnh')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cpf' => ['required', 'string', 'size:11', 'unique:drivers,cpf', new ValidCpf()],
            'cnh' => ['required', 'string', 'size:11', 'unique:drivers,cnh', new ValidCnh()],
        ]);

        DB::transaction(function () use ($tenant, $validated): void {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'type' => UserType::DRIVER,
            ]);

            Driver::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'cpf' => $validated['cpf'],
                'cnh' => $validated['cnh'],
            ]);
        });

        return redirect()->route('portal.drivers.index')
            ->with('success', 'Motorista cadastrado com sucesso.');
    }

    /**
     * Show unified user registration form.
     */
    public function userCreate(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $drivers = Driver::with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->get();

        $canCreateAdmin = $this->canCreateAdmins();

        return view('dashboard.users.create', compact('drivers', 'canCreateAdmin'));
    }

    /**
     * Show users list for the current company.
     */
    public function users(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();

        $users = User::with(['driver', 'guardian.primaryDriver.user'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Store a new user (driver, guardian, or admin).
     */
    public function userStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $allowedTypes = $this->canCreateAdmins()
            ? [UserType::DRIVER->value, UserType::GUARDIAN->value, UserType::ADMIN->value]
            : [UserType::DRIVER->value, UserType::GUARDIAN->value];

        $request->merge([
            'cpf' => $request->filled('cpf') ? preg_replace('/\D/', '', (string) $request->input('cpf')) : null,
            'cnh' => $request->filled('cnh') ? preg_replace('/\D/', '', (string) $request->input('cnh')) : null,
        ]);

        $validated = $request->validate([
            'type' => ['required', Rule::in($allowedTypes)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_company_manager' => ['nullable', 'boolean'],
            'cpf' => [
                Rule::requiredIf(fn() => in_array($request->input('type'), [UserType::DRIVER->value, UserType::GUARDIAN->value], true)),
                'nullable',
                'string',
                'size:11',
                Rule::unique('drivers', 'cpf'),
                Rule::unique('guardians', 'cpf'),
                new ValidCpf(),
            ],
            'cnh' => [
                Rule::requiredIf(fn() => $request->input('type') === UserType::DRIVER->value),
                'nullable',
                'string',
                'size:11',
                Rule::unique('drivers', 'cnh'),
                new ValidCnh(),
            ],
            'primary_driver_id' => [
                'nullable',
                Rule::exists('drivers', 'id')->where(fn($query) => $query->where('tenant_id', $tenant->id)),
            ],
        ]);

        DB::transaction(function () use ($tenant, $validated): void {
            $type = UserType::from($validated['type']);

            $user = User::create([
                'tenant_id' => $type === UserType::ADMIN ? null : $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'type' => $type,
                'is_company_manager' => $type === UserType::DRIVER ? (bool) ($validated['is_company_manager'] ?? false) : false,
            ]);

            if ($type === UserType::DRIVER) {
                Driver::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'cpf' => $validated['cpf'],
                    'cnh' => $validated['cnh'],
                ]);
                return;
            }

            if ($type === UserType::GUARDIAN) {
                $guardian = Guardian::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'primary_driver_id' => $validated['primary_driver_id'] ?? null,
                    'cpf' => $validated['cpf'],
                ]);

                if (!empty($validated['primary_driver_id'])) {
                    $guardian->drivers()->syncWithoutDetaching([
                        $validated['primary_driver_id'] => ['tenant_id' => $tenant->id],
                    ]);
                }
            }
        });

        return redirect()->route('portal.dashboard')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    /**
     * Show user edit form.
     */
    public function userEdit(User $user): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');

        $user->load(['driver', 'guardian']);

        $drivers = Driver::with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->get();

        $canCreateAdmin = $this->canCreateAdmins();

        return view('dashboard.users.edit', compact('user', 'drivers', 'canCreateAdmin'));
    }

    /**
     * Update user data and type-specific profile fields.
     */
    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');

        $user->loadMissing(['driver', 'guardian']);

        $allowedTypes = $this->canCreateAdmins()
            ? [UserType::DRIVER->value, UserType::GUARDIAN->value, UserType::ADMIN->value]
            : [UserType::DRIVER->value, UserType::GUARDIAN->value];

        $request->merge([
            'cpf' => $request->filled('cpf') ? preg_replace('/\D/', '', (string) $request->input('cpf')) : null,
            'cnh' => $request->filled('cnh') ? preg_replace('/\D/', '', (string) $request->input('cnh')) : null,
        ]);

        $targetTypeInput = (string) $request->input('type', $user->type?->value ?? $user->type);
        $targetType = UserType::tryFrom($targetTypeInput) ?? UserType::from($user->type?->value ?? $user->type);
        $isDriver = $targetType === UserType::DRIVER;
        $isGuardian = $targetType === UserType::GUARDIAN;

        $validated = $request->validate([
            'type' => ['required', Rule::in($allowedTypes)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_company_manager' => ['nullable', 'boolean'],
            'cpf' => [
                Rule::requiredIf(fn() => $isDriver || $isGuardian),
                'nullable',
                'string',
                'size:11',
                Rule::unique('drivers', 'cpf')->ignore($user->driver?->id),
                Rule::unique('guardians', 'cpf')->ignore($user->guardian?->id),
                new ValidCpf(),
            ],
            'cnh' => [
                Rule::requiredIf(fn() => $isDriver),
                'nullable',
                'string',
                'size:11',
                Rule::unique('drivers', 'cnh')->ignore($user->driver?->id),
                new ValidCnh(),
            ],
            'primary_driver_id' => [
                'nullable',
                Rule::exists('drivers', 'id')->where(fn($query) => $query->where('tenant_id', $tenant->id)),
            ],
        ]);

        DB::transaction(function () use ($tenant, $user, $validated, $targetType, $isDriver, $isGuardian): void {
            $user->update([
                'tenant_id' => $targetType === UserType::ADMIN ? null : $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'type' => $targetType,
                'is_company_manager' => $targetType === UserType::DRIVER ? (bool) ($validated['is_company_manager'] ?? false) : false,
                'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
            ]);

            if ($targetType === UserType::ADMIN) {
                if ($user->driver) {
                    Guardian::where('primary_driver_id', $user->driver->id)->update(['primary_driver_id' => null]);
                    $user->driver->guardians()->detach();
                    $user->driver->delete();
                }

                if ($user->guardian) {
                    $user->guardian->drivers()->detach();
                    $user->guardian->delete();
                }

                return;
            }

            if ($isDriver && $user->driver) {
                $user->driver->update([
                    'cpf' => $validated['cpf'],
                    'cnh' => $validated['cnh'],
                ]);
            } elseif ($isDriver && !$user->driver) {
                if ($user->guardian) {
                    $user->guardian->drivers()->detach();
                    $user->guardian->delete();
                }

                Driver::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'cpf' => $validated['cpf'],
                    'cnh' => $validated['cnh'],
                ]);

                return;
            }

            if ($isGuardian && $user->guardian) {
                $user->guardian->update([
                    'cpf' => $validated['cpf'],
                    'primary_driver_id' => $validated['primary_driver_id'] ?? null,
                ]);

                if (!empty($validated['primary_driver_id'])) {
                    $user->guardian->drivers()->syncWithoutDetaching([
                        $validated['primary_driver_id'] => ['tenant_id' => $tenant->id],
                    ]);
                }
            } elseif ($isGuardian && !$user->guardian) {
                if ($user->driver) {
                    Guardian::where('primary_driver_id', $user->driver->id)->update(['primary_driver_id' => null]);
                    $user->driver->guardians()->detach();
                    $user->driver->delete();
                }

                $guardian = Guardian::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'cpf' => $validated['cpf'],
                    'primary_driver_id' => $validated['primary_driver_id'] ?? null,
                ]);

                if (!empty($validated['primary_driver_id'])) {
                    $guardian->drivers()->syncWithoutDetaching([
                        $validated['primary_driver_id'] => ['tenant_id' => $tenant->id],
                    ]);
                }
            }
        });

        return redirect()->route('portal.users.index', $this->companyRouteParams())
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Toggle user active status.
     */
    public function userToggleStatus(User $user): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');

        if ((int) auth()->id() === (int) $user->id) {
            return redirect()->route('portal.users.index', $this->companyRouteParams())
                ->with('error', 'Não é permitido desativar o próprio usuário.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->route('portal.users.index', $this->companyRouteParams())
            ->with('success', $user->is_active ? 'Usuário ativado com sucesso.' : 'Usuário desativado com sucesso.');
    }

    /**
     * Show driver details
     */
    public function driverShow(Driver $driver): View
    {
        $driver->load(['user', 'address', 'tenant', 'guardians']);
        return view('dashboard.drivers.show', compact('driver'));
    }

    /**
     * Show guardians list
     */
    public function guardians(): View
    {
        $guardians = Guardian::with(['user', 'address', 'tenant'])->paginate(15);
        return view('dashboard.guardians.index', compact('guardians'));
    }

    /**
     * Show guardian details
     */
    public function guardianShow(Guardian $guardian): View
    {
        $guardian->load(['user', 'address', 'tenant', 'primaryDriver.user', 'drivers']);
        return view('dashboard.guardians.show', compact('guardian'));
    }

    /**
     * Search banks by code or name (for autocomplete).
     */
    public function searchBanks(Request $request)
    {
        $query = $request->query('q', '');
        $banks = \App\Services\BankService::search($query);

        return response()->json(array_values($banks));
    }

    /**
     * Get the transport company associated with the authenticated user.
     * Global admins must explicitly select a company via query parameter or route.
     */
    private function currentTenant(): Tenant
    {
        $user = auth()->user()?->loadMissing(['tenant', 'driver.tenant', 'guardian.tenant']);

        // Non-admin users get their tenant from their relations
        $tenant = $user?->tenant ?? $user?->driver?->tenant ?? $user?->guardian?->tenant;

        // Admins can explicitly select a company via query parameter
        if (!$tenant && $this->isSystemAdmin()) {
            $companyId = request()->integer('company_id');
            if ($companyId) {
                $tenant = Tenant::find($companyId);
            }
        }

        if (!$tenant) {
            $message = $this->isSystemAdmin() 
                ? 'Administradores globais devem selecionar uma empresa. Acesse a gestão de empresas.'
                : 'Empresa de transporte não encontrada.';
            abort(403, $message);
        }

        return $tenant;
    }

    /**
     * Check whether authenticated user is a system admin.
     */
    private function isSystemAdmin(): bool
    {
        return auth()->check() && auth()->user()?->type === UserType::ADMIN;
    }

    /**
     * Check whether authenticated user is a driver with company manager privileges.
     */
    private function isCompanyManager(): bool
    {
        return auth()->check()
            && auth()->user()?->type === UserType::DRIVER
            && (bool) auth()->user()?->is_company_manager;
    }

    /**
     * Check if the authenticated user can manage company users.
     */
    private function canManageUsers(): bool
    {
        return $this->isSystemAdmin() || $this->isCompanyManager();
    }

    /**
     * Check if authenticated user can create or promote admins.
     */
    private function canCreateAdmins(): bool
    {
        return $this->isSystemAdmin();
    }

    /**
     * Preserve company context for global admin redirects.
     *
     * @return array<string, int>
     */
    private function companyRouteParams(): array
    {
        return request()->filled('company_id')
            ? ['company_id' => request()->integer('company_id')]
            : [];
    }
}
