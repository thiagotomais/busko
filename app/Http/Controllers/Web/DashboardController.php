<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\FinancialEntry;
use App\Models\Guardian;
use App\Models\Passenger;
use App\Models\Tenant;
use App\Models\TransportRoute;
use App\Models\User;
use App\Rules\ValidCnh;
use App\Rules\ValidCpf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
        $tenant = ($this->isSystemAdmin() && ! request()->filled('company') && ! request()->filled('company_id'))
            ? null
            : $this->currentTenant();
        
        $stats = [
            'drivers' => Driver::count(),
            'guardians' => Guardian::count(),
            'passengers' => Passenger::count(),
            'users' => $tenant
                ? User::query()->where('tenant_id', $tenant->id)->count()
                : User::count(),
            'financial_receivable' => 0.0,
            'financial_received_month' => 0.0,
            'financial_overdue' => 0.0,
        ];

        if ($tenant) {
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $stats['financial_receivable'] = (float) FinancialEntry::query()
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount');

            $stats['financial_received_month'] = (float) FinancialEntry::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $stats['financial_overdue'] = (float) FinancialEntry::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->whereDate('due_date', '<', $today)
                ->sum('amount');
        }

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
        $canCreateCompanyAdmin = $this->canCreateCompanyAdmins();

        return view('dashboard.users.create', compact('drivers', 'canCreateAdmin', 'canCreateCompanyAdmin'));
    }

    /**
     * Show users list for the current company.
     */
    public function users(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        if ($this->isSystemAdmin() && ! request()->filled('company') && ! request()->filled('company_id')) {
            $tenants = Tenant::query()
                ->withCount(['users', 'drivers', 'guardians'])
                ->orderBy('name')
                ->get();

            return view('dashboard.users.select-company', compact('tenants'));
        }

        $query = User::with(['driver', 'guardian.primaryDriver.user'])->orderBy('id', 'desc');

        if ($this->isSystemAdmin() && (request()->filled('company') || request()->filled('company_id'))) {
            // Admin viewing a specific company: filter by selected tenant
            $query->where('tenant_id', $this->currentTenant()->id);
        } elseif (!$this->isSystemAdmin()) {
            // Non-admin: always filter by their own tenant
            $tenant = $this->currentTenant();
            $query->where('tenant_id', $tenant->id);
        }
        // System admin without selected company is handled by the selector view above

        $users = $query->paginate(15);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Store a new user (driver, guardian, or admin).
     */
    public function userStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $allowedTypes = [UserType::DRIVER->value, UserType::GUARDIAN->value];
        if ($this->canCreateAdmins()) {
            $allowedTypes[] = UserType::ADMIN->value;
        }
        if ($this->canCreateCompanyAdmins()) {
            $allowedTypes[] = UserType::COMPANY_ADMIN->value;
        }

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

            if ($type === UserType::COMPANY_ADMIN) {
                // No additional profile record needed for company admin
                return;
            }

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

        $tenant = $this->isSystemAdmin()
            ? (Tenant::find($user->tenant_id) ?? $this->currentTenant())
            : $this->currentTenant();

        if (!$this->isSystemAdmin()) {
            abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');
        }

        $user->load(['driver', 'guardian']);

        $drivers = Driver::with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->get();

        $canCreateAdmin = $this->canCreateAdmins();
        $canCreateCompanyAdmin = $this->canCreateCompanyAdmins();

        return view('dashboard.users.edit', compact('user', 'drivers', 'canCreateAdmin', 'canCreateCompanyAdmin'));
    }

    /**
     * Update user data and type-specific profile fields.
     */
    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->isSystemAdmin()
            ? (Tenant::find($user->tenant_id) ?? $this->currentTenant())
            : $this->currentTenant();

        if (!$this->isSystemAdmin()) {
            abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');
        }

        $user->loadMissing(['driver', 'guardian']);

        $allowedTypes = [UserType::DRIVER->value, UserType::GUARDIAN->value];
        if ($this->canCreateAdmins()) {
            $allowedTypes[] = UserType::ADMIN->value;
        }
        if ($this->canCreateCompanyAdmins()) {
            $allowedTypes[] = UserType::COMPANY_ADMIN->value;
        }

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

            if ($targetType === UserType::COMPANY_ADMIN) {
                // Clean up any existing driver/guardian profile
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

        if (!$this->isSystemAdmin()) {
            $tenant = $this->currentTenant();
            abort_unless((int) $user->tenant_id === (int) $tenant->id, 403, 'Acesso não autorizado.');
        }

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
        if ($this->isSystemAdmin() && ! request()->filled('company') && ! request()->filled('company_id')) {
            $tenants = Tenant::query()
                ->withCount(['guardians', 'passengers', 'users'])
                ->orderBy('name')
                ->get();

            return view('dashboard.guardians.select-company', compact('tenants'));
        }

        $tenant = $this->currentTenant();
        $guardians = Guardian::with(['user', 'address', 'tenant'])
            ->where('tenant_id', $tenant->id)
            ->paginate(15);

        return view('dashboard.guardians.index', compact('guardians'));
    }

    /**
     * Show guardian details
     */
    public function guardianShow(Guardian $guardian): View
    {
        $tenant = $this->currentTenant();
        abort_unless((int) $guardian->tenant_id === (int) $tenant->id, 404, 'Guardião não encontrado.');

        $guardian->load([
            'user',
            'address',
            'tenant',
            'primaryDriver.user',
            'drivers',
            'passengers' => fn ($query) => $query
                ->with(['transportRoutes.driver.user'])
                ->orderBy('id', 'desc'),
        ]);

            $missingMonthlyFeeCount = $guardian->passengers->filter(fn ($passenger) => $passenger->monthly_fee === null)->count();

            return view('dashboard.guardians.show', compact('guardian', 'missingMonthlyFeeCount'));
    }

    /**
     * Show passengers list for current tenant.
     */
    public function passengers(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        if ($this->isSystemAdmin() && ! request()->filled('company') && ! request()->filled('company_id')) {
            $tenants = Tenant::query()
                ->withCount(['passengers', 'guardians', 'users'])
                ->orderBy('name')
                ->get();

            return view('dashboard.passengers.select-company', compact('tenants'));
        }

        $tenant = $this->currentTenant();

        $passengers = Passenger::with(['guardian.user', 'transportRoutes.driver.user'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $missingMonthlyFeeCount = Passenger::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('monthly_fee')
            ->count();

        return view('dashboard.passengers.index', compact('passengers', 'missingMonthlyFeeCount'));
    }

    /**
     * Show passenger create form.
     */
    public function passengerCreate(Request $request): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $guardianId = $request->integer('guardian_id');

        abort_unless($guardianId > 0, 404, 'Guardião responsável financeiro não informado.');

        $guardian = Guardian::with('user')
            ->where('tenant_id', $tenant->id)
            ->findOrFail($guardianId);

        ['idaRoutes' => $idaRoutes, 'voltaRoutes' => $voltaRoutes] = $this->passengerRouteOptions($tenant);

        return view('dashboard.passengers.create', compact('guardian', 'idaRoutes', 'voltaRoutes'));
    }

    /**
     * Store a new passenger linked to a guardian.
     */
    public function passengerStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();

        $this->normalizePassengerInput($request);
        $validated = $this->validatePassengerPayload($request, $tenant);

        $passenger = Passenger::create([
            ...$validated,
            'tenant_id' => $tenant->id,
        ]);

        $this->syncPassengerTransportRoutes($passenger, $tenant, $validated);

        return redirect()->route('portal.passengers.index', $this->companyRouteParams())
            ->with('success', 'Passageiro cadastrado com sucesso.');
    }

    /**
     * Show passenger edit form.
     */
    public function passengerEdit(Passenger $passenger): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $passenger = $this->findTenantPassenger($passenger);
        $passenger->load(['guardian.user', 'transportRoutes.driver.user']);
        $guardian = $passenger->guardian;
        ['idaRoutes' => $idaRoutes, 'voltaRoutes' => $voltaRoutes] = $this->passengerRouteOptions($this->currentTenant());

        return view('dashboard.passengers.create', compact('passenger', 'guardian', 'idaRoutes', 'voltaRoutes'));
    }

    /**
     * Update an existing passenger.
     */
    public function passengerUpdate(Request $request, Passenger $passenger): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $passenger = $this->findTenantPassenger($passenger);

        $this->normalizePassengerInput($request);
        $validated = $this->validatePassengerPayload($request, $tenant);
        $validated['guardian_id'] = $passenger->guardian_id;

        $passenger->update($validated);
        $this->syncPassengerTransportRoutes($passenger, $tenant, $validated);

        return redirect()->route('portal.passengers.index', $this->companyRouteParams())
            ->with('success', 'Passageiro atualizado com sucesso.');
    }

    /**
     * Show transport routes list.
     */
    public function transportRoutes(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        if ($this->isSystemAdmin() && ! request()->filled('company') && ! request()->filled('company_id')) {
            $tenants = Tenant::query()
                ->withCount(['drivers', 'passengers', 'users'])
                ->orderBy('name')
                ->get();

            return view('dashboard.transport-routes.select-company', compact('tenants'));
        }

        $tenant = $this->currentTenant();

        $transportRoutes = TransportRoute::with(['driver.user', 'passengers.guardian.user'])
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->paginate(15);

        return view('dashboard.transport-routes.index', compact('transportRoutes'));
    }

    /**
     * Show transport route creation form.
     */
    public function transportRouteCreate(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $drivers = Driver::with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get();
        $passengers = Passenger::with('guardian.user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();
        $transportRoute = new TransportRoute([
            'period' => 'tarde',
            'direction' => 'ida',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'sunday' => false,
            'is_active' => true,
        ]);

        return view('dashboard.transport-routes.form', compact('transportRoute', 'drivers', 'passengers'));
    }

    /**
     * Store a new transport route.
     */
    public function transportRouteStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $validated = $this->validateTransportRoutePayload($request, $tenant);

        $transportRoute = TransportRoute::create([
            ...$validated['route'],
            'tenant_id' => $tenant->id,
        ]);

        $transportRoute->passengers()->sync($validated['assignments']);

        return redirect()->route('portal.transport-routes.index', $this->companyRouteParams())
            ->with('success', 'Rota cadastrada com sucesso.');
    }

    /**
     * Show transport route operational details.
     */
    public function transportRouteShow(TransportRoute $transportRoute): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $transportRoute = $this->findTenantTransportRoute($transportRoute);
        $transportRoute->load([
            'driver.user',
            'passengers.guardian.user',
        ]);

        return view('dashboard.transport-routes.show', compact('transportRoute'));
    }

    /**
     * Show transport route edit form.
     */
    public function transportRouteEdit(TransportRoute $transportRoute): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $transportRoute = $this->findTenantTransportRoute($transportRoute);
        $transportRoute->load(['passengers', 'driver.user']);

        $drivers = Driver::with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get();
        $passengers = Passenger::with('guardian.user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('dashboard.transport-routes.form', compact('transportRoute', 'drivers', 'passengers'));
    }

    /**
     * Update a transport route.
     */
    public function transportRouteUpdate(Request $request, TransportRoute $transportRoute): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $transportRoute = $this->findTenantTransportRoute($transportRoute);
        $validated = $this->validateTransportRoutePayload($request, $tenant, $transportRoute);

        $transportRoute->update($validated['route']);
        $transportRoute->passengers()->sync($validated['assignments']);

        return redirect()->route('portal.transport-routes.index', $this->companyRouteParams())
            ->with('success', 'Rota atualizada com sucesso.');
    }

    /**
     * Show financial entries list.
     */
    public function financialEntries(Request $request): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        if ($this->isSystemAdmin() && ! $request->filled('company') && ! $request->filled('company_id')) {
            $tenants = Tenant::query()
                ->withCount(['drivers', 'guardians', 'users'])
                ->orderBy('name')
                ->get();

            return view('dashboard.financial.select-company', compact('tenants'));
        }

        $tenant = $this->currentTenant();
        $status = (string) $request->query('status', '');
        $competence = (string) $request->query('competence', '');
        $guardianId = $request->integer('guardian_id');

        $financialEntries = $this->financialEntriesQuery($request, $tenant)
            ->orderByDesc('competence_month')
            ->orderByDesc('due_date')
            ->paginate(20)
            ->withQueryString();

        $guardians = Guardian::query()
            ->with('user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get();

        $passengersWithoutMonthlyFeeCount = Passenger::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('monthly_fee')
            ->count();

        return view('dashboard.financial.index', compact('financialEntries', 'guardians', 'status', 'competence', 'guardianId', 'passengersWithoutMonthlyFeeCount'));
    }

    /**
     * Export filtered financial entries as CSV.
     */
    public function financialEntriesExport(Request $request): StreamedResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();

        $entries = $this->financialEntriesQuery($request, $tenant)
            ->orderByDesc('competence_month')
            ->orderByDesc('due_date')
            ->get();

        $filename = 'financeiro_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($entries): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Competencia',
                'Passageiro',
                'Guardiao',
                'Vencimento',
                'Valor',
                'Status',
                'Forma de pagamento',
                'Pago em',
                'Observacoes',
            ], ';');

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->competence_month?->format('m/Y'),
                    $entry->passenger?->name,
                    $entry->guardian?->user?->name,
                    $entry->due_date?->format('d/m/Y'),
                    number_format((float) $entry->amount, 2, ',', '.'),
                    strtoupper($entry->effectiveStatus()),
                    $entry->payment_method,
                    $entry->paid_at?->format('d/m/Y H:i'),
                    $entry->notes,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Show monthly billing creation form.
     */
    public function financialEntryCreate(): View
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();
        $passengers = Passenger::query()
            ->with('guardian.user')
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('dashboard.financial.create', compact('passengers'));
    }

    /**
     * Store a monthly billing entry.
     */
    public function financialEntryStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();

        $validated = $request->validate([
            'passenger_id' => [
                'required',
                'integer',
                Rule::exists('passengers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'competence_month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $passenger = Passenger::query()
            ->where('tenant_id', $tenant->id)
            ->findOrFail((int) $validated['passenger_id']);

        $competenceDate = Carbon::createFromFormat('Y-m', $validated['competence_month'])->startOfMonth()->toDateString();

        $alreadyExists = FinancialEntry::query()
            ->where('tenant_id', $tenant->id)
            ->where('passenger_id', $passenger->id)
            ->whereDate('competence_month', $competenceDate)
            ->exists();

        if ($alreadyExists) {
            return redirect()->back()
                ->withErrors([
                    'passenger_id' => 'Já existe uma mensalidade para este passageiro na competência informada.',
                ])
                ->withInput();
        }

        FinancialEntry::create([
            'tenant_id' => $tenant->id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => $competenceDate,
            'amount' => $validated['amount'],
            'due_date' => $validated['due_date'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('portal.financial.index', $this->companyRouteParams())
            ->with('success', 'Mensalidade cadastrada com sucesso.');
    }

    /**
     * Generate monthly billing entries in bulk for all passengers in tenant.
     */
    public function financialEntryBulkStore(Request $request): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $tenant = $this->currentTenant();

        $validated = $request->validate([
            'competence_month' => ['required', 'date_format:Y-m'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $competenceDate = Carbon::createFromFormat('Y-m', $validated['competence_month'])->startOfMonth()->toDateString();

        $passengers = Passenger::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('guardian_id')
            ->get(['id', 'guardian_id', 'monthly_fee']);

        if ($passengers->isEmpty()) {
            return redirect()->route('portal.financial.index', $this->companyRouteParams())
                ->with('error', 'Nenhum passageiro disponível para gerar mensalidades.');
        }

        $existingPassengerIds = FinancialEntry::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('competence_month', $competenceDate)
            ->pluck('passenger_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingLookup = array_flip($existingPassengerIds);
        $rows = [];
        $created = 0;
        $skipped = 0;
        $skippedWithoutAmount = 0;

        foreach ($passengers as $passenger) {
            if (isset($existingLookup[(int) $passenger->id])) {
                $skipped++;
                continue;
            }

            $resolvedAmount = $passenger->monthly_fee ?? ($validated['amount'] ?? null);

            if ($resolvedAmount === null) {
                $skippedWithoutAmount++;
                continue;
            }

            $rows[] = [
                'tenant_id' => $tenant->id,
                'guardian_id' => $passenger->guardian_id,
                'passenger_id' => $passenger->id,
                'competence_month' => $competenceDate,
                'amount' => $resolvedAmount,
                'due_date' => $validated['due_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $created++;
        }

        if ($rows !== []) {
            FinancialEntry::insert($rows);
        }

        if ($created === 0 && $skippedWithoutAmount > 0) {
            return redirect()->route('portal.financial.index', $this->companyRouteParams())
                ->with('error', 'Nenhuma mensalidade foi criada. Defina a mensalidade padrão dos passageiros ou informe um valor padrão no lote.');
        }

        return redirect()->route('portal.financial.index', $this->companyRouteParams())
            ->with('success', "Geração em lote concluída. Criados: {$created}. Ignorados por duplicidade: {$skipped}. Ignorados sem valor: {$skippedWithoutAmount}.");
    }

    /**
     * Mark financial entry as paid.
     */
    public function financialEntryMarkPaid(Request $request, FinancialEntry $financialEntry): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $entry = $this->findTenantFinancialEntry($financialEntry);

        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['pix', 'dinheiro', 'transferencia', 'cartao', 'boleto', 'outro'])],
            'paid_at' => ['nullable', 'date'],
        ]);

        $entry->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'paid_at' => $validated['paid_at'] ?? now(),
        ]);

        return redirect()->route('portal.financial.index', $this->companyRouteParams())
            ->with('success', 'Pagamento registrado com sucesso.');
    }

    /**
     * Revert a paid financial entry back to pending.
     */
    public function financialEntryMarkPending(FinancialEntry $financialEntry): RedirectResponse
    {
        abort_unless($this->canManageUsers(), 403, 'Acesso não autorizado.');

        $entry = $this->findTenantFinancialEntry($financialEntry);

        $entry->update([
            'status' => 'pending',
            'payment_method' => null,
            'paid_at' => null,
        ]);

        return redirect()->route('portal.financial.index', $this->companyRouteParams())
            ->with('success', 'Lançamento retornou para pendente.');
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
     * Lookup CEP data for address auto-fill.
     */
    public function lookupCep(string $cep): JsonResponse
    {
        $normalizedCep = preg_replace('/\D/', '', $cep);

        if (strlen((string) $normalizedCep) !== 8) {
            return response()->json([
                'message' => 'CEP inválido.',
            ], 422);
        }

        $response = Http::timeout(8)->get("https://viacep.com.br/ws/{$normalizedCep}/json/");

        if ($response->failed()) {
            return response()->json([
                'message' => 'Falha na consulta de CEP.',
            ], 502);
        }

        $data = $response->json();

        if (($data['erro'] ?? false) === true) {
            return response()->json([
                'message' => 'CEP não encontrado.',
            ], 404);
        }

        return response()->json([
            'zip' => $normalizedCep,
            'street' => $data['logradouro'] ?? '',
            'neighborhood' => $data['bairro'] ?? '',
            'city' => $data['localidade'] ?? '',
            'state' => strtoupper((string) ($data['uf'] ?? '')),
            'complement' => $data['complemento'] ?? '',
        ]);
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
            $companyUid = (string) request()->query('company', '');
            if ($companyUid !== '') {
                $tenant = Tenant::query()->where('uid', $companyUid)->first();
            }

            if (!$tenant) {
                $companyId = request()->integer('company_id');
                if ($companyId) {
                    $tenant = Tenant::find($companyId);
                }
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
     * Check whether authenticated user is an admin (global or tenant).
     */
    private function isAdmin(): bool
    {
        return auth()->check() && auth()->user()?->type === UserType::ADMIN;
    }

    /**
     * Check whether authenticated user is a global system admin.
     */
    private function isSystemAdmin(): bool
    {
        return $this->isAdmin() && is_null(auth()->user()?->tenant_id);
    }

    /**
     * Check whether authenticated user is a company-level admin (scoped to their tenant).
     */
    private function isCompanyAdmin(): bool
    {
        return auth()->check()
            && auth()->user()?->type === UserType::COMPANY_ADMIN
            && ! is_null(auth()->user()?->tenant_id);
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
        return $this->isAdmin() || $this->isCompanyAdmin() || $this->isCompanyManager();
    }

    /**
     * Check if authenticated user can create or promote global admins.
     */
    private function canCreateAdmins(): bool
    {
        return $this->isSystemAdmin();
    }

    /**
     * Check if authenticated user can create company-level admins.
     * Both global admins and existing company admins can do this.
     */
    private function canCreateCompanyAdmins(): bool
    {
        return $this->isSystemAdmin() || $this->isCompanyAdmin();
    }

    /**
     * Ensure the passenger belongs to the current tenant.
     */
    private function findTenantPassenger(Passenger $passenger): Passenger
    {
        $tenant = $this->currentTenant();

        abort_unless((int) $passenger->tenant_id === (int) $tenant->id, 404, 'Passageiro não encontrado.');

        return $passenger;
    }

    /**
     * Ensure the transport route belongs to the current tenant.
     */
    private function findTenantTransportRoute(TransportRoute $transportRoute): TransportRoute
    {
        $tenant = $this->currentTenant();

        abort_unless((int) $transportRoute->tenant_id === (int) $tenant->id, 404, 'Rota não encontrada.');

        return $transportRoute;
    }

    /**
     * Ensure the financial entry belongs to the current tenant.
     */
    private function findTenantFinancialEntry(FinancialEntry $financialEntry): FinancialEntry
    {
        $tenant = $this->currentTenant();

        abort_unless((int) $financialEntry->tenant_id === (int) $tenant->id, 404, 'Lançamento financeiro não encontrado.');

        return $financialEntry;
    }

    /**
     * Build base filtered query for financial entries.
     */
    private function financialEntriesQuery(Request $request, Tenant $tenant)
    {
        $status = (string) $request->query('status', '');
        $competence = (string) $request->query('competence', '');
        $guardianId = $request->integer('guardian_id');

        return FinancialEntry::query()
            ->with(['passenger', 'guardian.user'])
            ->where('tenant_id', $tenant->id)
            ->when($guardianId > 0, fn ($query) => $query->where('guardian_id', $guardianId))
            ->when($competence !== '', function ($query) use ($competence) {
                try {
                    $competenceDate = Carbon::createFromFormat('Y-m', $competence)->startOfMonth()->toDateString();
                    $query->whereDate('competence_month', $competenceDate);
                } catch (\Throwable) {
                    // Ignore invalid filter values and keep query running.
                }
            })
            ->when($status !== '', function ($query) use ($status) {
                if ($status === 'overdue') {
                    $query->where('status', 'pending')
                        ->whereDate('due_date', '<', now()->toDateString());

                    return;
                }

                $query->where('status', $status);
            });
    }

    /**
     * Normalize passenger address input before validation.
     */
    private function normalizePassengerInput(Request $request): void
    {
        $request->merge([
            'residential_zip' => preg_replace('/\D/', '', (string) $request->input('residential_zip')),
            'residential_state' => strtoupper((string) $request->input('residential_state')),
            'pickup_zip' => preg_replace('/\D/', '', (string) $request->input('pickup_zip')),
            'pickup_state' => strtoupper((string) $request->input('pickup_state')),
            'dropoff_zip' => preg_replace('/\D/', '', (string) $request->input('dropoff_zip')),
            'dropoff_state' => strtoupper((string) $request->input('dropoff_state')),
            'school_zip' => preg_replace('/\D/', '', (string) $request->input('school_zip')),
            'school_state' => strtoupper((string) $request->input('school_state')),
        ]);
    }

    /**
     * Validate passenger payload.
     *
     * @return array<string, mixed>
     */
    private function validatePassengerPayload(Request $request, Tenant $tenant): array
    {
        $validated = $request->validate([
            'guardian_id' => [
                'required',
                Rule::exists('guardians', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'service_type' => ['required', Rule::in(['ida', 'volta', 'ida_volta'])],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'school_grade' => ['required', 'string', 'max:255'],
            'period' => ['required', Rule::in(['manha', 'tarde', 'noite'])],
            'rg' => ['required', 'string', 'max:20'],
            'residential_zip' => ['required', 'string', 'size:8'],
            'residential_street' => ['required', 'string', 'max:255'],
            'residential_number' => ['required', 'string', 'max:20'],
            'residential_complement' => ['nullable', 'string', 'max:255'],
            'residential_neighborhood' => ['required', 'string', 'max:255'],
            'residential_city' => ['required', 'string', 'max:255'],
            'residential_state' => ['required', 'string', 'size:2'],
            'pickup_zip' => ['required', 'string', 'size:8'],
            'pickup_street' => ['required', 'string', 'max:255'],
            'pickup_number' => ['required', 'string', 'max:20'],
            'pickup_complement' => ['nullable', 'string', 'max:255'],
            'pickup_neighborhood' => ['required', 'string', 'max:255'],
            'pickup_city' => ['required', 'string', 'max:255'],
            'pickup_state' => ['required', 'string', 'size:2'],
            'dropoff_zip' => ['required', 'string', 'size:8'],
            'dropoff_street' => ['required', 'string', 'max:255'],
            'dropoff_number' => ['required', 'string', 'max:20'],
            'dropoff_complement' => ['nullable', 'string', 'max:255'],
            'dropoff_neighborhood' => ['required', 'string', 'max:255'],
            'dropoff_city' => ['required', 'string', 'max:255'],
            'dropoff_state' => ['required', 'string', 'size:2'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_zip' => ['required', 'string', 'size:8'],
            'school_street' => ['required', 'string', 'max:255'],
            'school_number' => ['required', 'string', 'max:20'],
            'school_complement' => ['nullable', 'string', 'max:255'],
            'school_neighborhood' => ['required', 'string', 'max:255'],
            'school_city' => ['required', 'string', 'max:255'],
            'school_state' => ['required', 'string', 'size:2'],
            'entry_time' => ['required', 'date_format:H:i'],
            'exit_time' => ['required', 'date_format:H:i'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0.01'],
            'ida_route_id' => [
                'nullable',
                'integer',
                Rule::exists('transport_routes', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenant->id)
                    ->where('direction', 'ida')),
            ],
            'volta_route_id' => [
                'nullable',
                'integer',
                Rule::exists('transport_routes', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenant->id)
                    ->where('direction', 'volta')),
            ],
        ]);

        $routeSelections = [
            'ida_route_id' => [
                'direction' => 'ida',
                'service_types' => ['ida', 'ida_volta'],
                'label' => 'ida',
            ],
            'volta_route_id' => [
                'direction' => 'volta',
                'service_types' => ['volta', 'ida_volta'],
                'label' => 'volta',
            ],
        ];

        foreach ($routeSelections as $field => $config) {
            $routeId = $validated[$field] ?? null;

            if (! $routeId) {
                continue;
            }

            if (! in_array($validated['service_type'], $config['service_types'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => "O serviço contratado não permite selecionar uma rota de {$config['label']}.",
                ]);
            }

            $route = TransportRoute::query()
                ->where('tenant_id', $tenant->id)
                ->find($routeId);

            if (! $route || $route->period !== $validated['period']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $field => "A rota de {$config['label']} deve ter o mesmo período do passageiro.",
                ]);
            }
        }

        return $validated;
    }

    /**
     * Get transport route options for passenger create/edit forms.
     *
     * @return array{idaRoutes: \Illuminate\Support\Collection<int, TransportRoute>, voltaRoutes: \Illuminate\Support\Collection<int, TransportRoute>}
     */
    private function passengerRouteOptions(Tenant $tenant): array
    {
        $routesByDirection = TransportRoute::with('driver.user')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_active')
            ->orderBy('period')
            ->orderBy('name')
            ->get()
            ->groupBy('direction');

        return [
            'idaRoutes' => $routesByDirection->get('ida', collect()),
            'voltaRoutes' => $routesByDirection->get('volta', collect()),
        ];
    }

    /**
     * Keep passenger route assignments aligned with selected ida/volta routes.
     */
    private function syncPassengerTransportRoutes(Passenger $passenger, Tenant $tenant, array $validated): void
    {
        $selectedRouteIds = [
            'ida' => in_array($validated['service_type'], ['ida', 'ida_volta'], true)
                ? ($validated['ida_route_id'] ?? null)
                : null,
            'volta' => in_array($validated['service_type'], ['volta', 'ida_volta'], true)
                ? ($validated['volta_route_id'] ?? null)
                : null,
        ];

        $currentRouteIdsByDirection = $passenger->transportRoutes()
            ->select('transport_routes.id', 'transport_routes.direction')
            ->get()
            ->groupBy('direction')
            ->map(fn ($routes) => $routes->pluck('id')->all())
            ->all();

        foreach (['ida', 'volta'] as $direction) {
            $selectedRouteId = $selectedRouteIds[$direction];
            $currentRouteIds = $currentRouteIdsByDirection[$direction] ?? [];

            $routeIdsToDetach = $selectedRouteId
                ? array_values(array_diff($currentRouteIds, [$selectedRouteId]))
                : $currentRouteIds;

            if ($routeIdsToDetach !== []) {
                $passenger->transportRoutes()->detach($routeIdsToDetach);
            }

            if (! $selectedRouteId || in_array($selectedRouteId, $currentRouteIds, true)) {
                continue;
            }

            $route = TransportRoute::query()
                ->where('tenant_id', $tenant->id)
                ->find($selectedRouteId);

            if (! $route) {
                continue;
            }

            $nextStopOrder = ((int) $route->passengers()->max('transport_route_passengers.stop_order')) + 1;

            $passenger->transportRoutes()->attach($route->id, [
                'tenant_id' => $tenant->id,
                'stop_order' => $nextStopOrder,
            ]);
        }
    }

    /**
     * Validate transport route data and passenger assignments.
     *
    * @return array{route: array<string, mixed>, assignments: array<int, array<string, int>>}
     */
    private function validateTransportRoutePayload(Request $request, Tenant $tenant, ?TransportRoute $currentRoute = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'driver_id' => [
                'required',
                Rule::exists('drivers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'direction' => ['required', Rule::in(['ida', 'volta'])],
            'period' => ['required', Rule::in(['manha', 'tarde', 'noite'])],
            'vehicle_name' => ['nullable', 'string', 'max:255'],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
            'monday' => ['nullable', 'boolean'],
            'tuesday' => ['nullable', 'boolean'],
            'wednesday' => ['nullable', 'boolean'],
            'thursday' => ['nullable', 'boolean'],
            'friday' => ['nullable', 'boolean'],
            'saturday' => ['nullable', 'boolean'],
            'sunday' => ['nullable', 'boolean'],
            'passenger_ids' => ['required', 'array', 'min:1'],
            'passenger_ids.*' => [
                'integer',
                Rule::exists('passengers', 'id')->where(fn ($query) => $query->where('tenant_id', $tenant->id)),
            ],
            'stop_orders' => ['required', 'array'],
        ]);

        $weekdayFields = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hasSelectedWeekday = false;

        foreach ($weekdayFields as $field) {
            if ($request->boolean($field)) {
                $hasSelectedWeekday = true;
                break;
            }
        }

        if (! $hasSelectedWeekday) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'weekdays' => 'Selecione ao menos um dia da semana para a rota.',
            ]);
        }

        $assignments = [];
        $usedOrders = [];
        $direction = $validated['direction'];
        $period = $validated['period'];
        $eligiblePassengerIds = Passenger::query()
            ->where('tenant_id', $tenant->id)
            ->where('period', $period)
            ->whereIn('service_type', $direction === 'ida' ? ['ida', 'ida_volta'] : ['volta', 'ida_volta'])
            ->pluck('id')
            ->all();

        $conflictingPassengerIds = DB::table('transport_route_passengers')
            ->join('transport_routes', 'transport_routes.id', '=', 'transport_route_passengers.transport_route_id')
            ->where('transport_route_passengers.tenant_id', $tenant->id)
            ->where('transport_routes.direction', $direction)
            ->where('transport_routes.period', $period)
            ->when($currentRoute, fn ($query) => $query->where('transport_routes.id', '!=', $currentRoute->id))
            ->pluck('transport_route_passengers.passenger_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $conflictingPassengerLookup = array_flip($conflictingPassengerIds);

        foreach ($validated['passenger_ids'] as $passengerId) {
            $orderValue = $request->input("stop_orders.{$passengerId}");

            if (! is_numeric($orderValue) || (int) $orderValue < 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "stop_orders.{$passengerId}" => 'Informe uma ordem de parada válida para cada passageiro selecionado.',
                ]);
            }

            $stopOrder = (int) $orderValue;

            if (isset($usedOrders[$stopOrder])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "stop_orders.{$passengerId}" => 'A ordem de parada não pode se repetir na mesma rota.',
                ]);
            }

            if (! in_array($passengerId, $eligiblePassengerIds, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'passenger_ids' => 'Há passageiros incompatíveis com a direção e o período selecionados para a rota.',
                ]);
            }

            if (isset($conflictingPassengerLookup[$passengerId])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'passenger_ids' => 'Um ou mais passageiros já estão vinculados a outra rota com a mesma direção e período.',
                ]);
            }

            $usedOrders[$stopOrder] = true;
            $assignments[$passengerId] = [
                'tenant_id' => $tenant->id,
                'stop_order' => $stopOrder,
            ];
        }

        return [
            'route' => [
                'name' => $validated['name'],
                'driver_id' => $validated['driver_id'],
                'direction' => $validated['direction'],
                'period' => $validated['period'],
                'vehicle_name' => $validated['vehicle_name'] ?? null,
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'monday' => $request->boolean('monday'),
                'tuesday' => $request->boolean('tuesday'),
                'wednesday' => $request->boolean('wednesday'),
                'thursday' => $request->boolean('thursday'),
                'friday' => $request->boolean('friday'),
                'saturday' => $request->boolean('saturday'),
                'sunday' => $request->boolean('sunday'),
            ],
            'assignments' => $assignments,
        ];
    }

    /**
     * Preserve company context for global admin redirects.
     *
     * @return array<string, int|string>
     */
    private function companyRouteParams(): array
    {
        if (request()->filled('company')) {
            return ['company' => (string) request()->query('company')];
        }

        return request()->filled('company_id')
            ? ['company_id' => request()->integer('company_id')]
            : [];
    }
}
