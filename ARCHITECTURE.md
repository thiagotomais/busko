# 🏗️ Arquitetura - PORTAL vs API

## 🎯 Princípio de Separação

O projeto Busko implementa **separação clara** entre:

- **PORTAL** - Interfáce web para gestão interna (Dashboard)
- **API** - Endpoints REST para integrações externas

Essa divisão garante:
- ✅ Segurança (diferentes métodos de autenticação)
- ✅ Manutenção (código organizado por contexto)
- ✅ Escalabilidade (fácil expandir cada sistema independentemente)

---

## 📦 Sistema de Autenticação Compartilhado

Ambos os sistemas usam **serviços de autenticação comuns**:

### `app/Services/Auth/DriverAuthService.php`

Responsável por **autenticar motoristas** (usada por PORTAL e API)

```php
public function login(string $email, string $password): ?array {
    // Valida credenciais
    // Retorna: ['user' => User, 'token' => string, 'tenant_id' => int]
}
```

**Usada por:**
- PORTAL: `Web/AuthController@store()` → cria sessão
- API: `Auth/LoginController@store()` → retorna token

### `app/Services/Auth/GuardianAuthService.php`

Responsável por **autenticar guardiões** (mesmo padrão de DriverAuthService)

### `app/Services/Auth/TokenService.php`

Responsável por **criar tokens Sanctum** com `tenant_id`

```php
public function createToken(User $user): string {
    // Cria token via Sanctum
    // Seta tenant_id no token
    // Retorna plainTextToken
}
```

**Compartilhado por:**
- API: `Auth/LoginController` cria tokens para requisições HTTP
- PORTAL: Na verdade, o portal **não usa tokens** (usa sessões)

---

## 🌐 Controllers do PORTAL

Localização: `app/Http/Controllers/Web/`

### `Web/AuthController.php`

**Responsabilidades:**
- Exibir formulário de login
- Processar submissão de login
- Fazer logout

**Métodos:**

```php
public function login(): View {
    return view('auth.login');
}

public function store(Request $request) {
    // Valida credenciais
    $authService->login($email, $password, $userType);
    
    // ⚠️ Para PORTAL: cria SESSION, não token
    auth()->login($user);
    
    return redirect()->route('portal.dashboard');
}

public function logout(Request $request) {
    auth()->logout();
    return redirect()->route('portal.login');
}
```

**Utiliza:**
- `DriverAuthService` / `GuardianAuthService`
- `auth()` facade (guard 'web')
- Views: `auth/login.blade.php`

### `Web/DashboardController.php`

**Responsabilidades:**
- Exibir dashboard (home)
- Listar motoristas
- Mostrar detalhes de motoristas
- Listar guardiões
- Mostrar detalhes de guardiões

**Métodos:**

```php
public function index(): View {
    $stats = [...];
    return view('dashboard.index', compact('stats'));
}

public function drivers(): View {
    $drivers = Driver::paginate(15);
    return view('dashboard.drivers.index', compact('drivers'));
}

public function driverShow(Driver $driver): View {
    $driver->load([...]);
    return view('dashboard.drivers.show', compact('driver'));
}

// ... guardians, guardianShow
```

**Utiliza:**
- Modelos: `Driver`, `Guardian`, `User`
- Views: `dashboard/*.blade.php`

---

## 🔌 Controllers da API

Localização: `app/Http/Controllers/Auth/`

### `Auth/LoginController.php`

**Responsabilidades:**
- Autenticar driver ou guardião
- Retornar token JSON

**Método:**

```php
public function store(Request $request) {
    // Valida credenciais
    $authService->login($email, $password, $userType);
    
    // ⚠️ Para API: cria TOKEN, não sessão
    $token = $this->tokenService->createToken($user);
    
    return response()->json([
        'token' => $token,
        'user' => $user
    ]);
}
```

**Utiliza:**
- `DriverAuthService` / `GuardianAuthService`
- `TokenService`
- Response JSON

### `Auth/LogoutController.php`

**Responsabilidades:**
- Revogar token do usuário

**Método:**

```php
public function store(Request $request) {
    // Encontra todos os tokens do usuário
    // Deleta-os (LogoutController)
    
    return response()->json(['message' => 'Logout successfully']);
}
```

### `Auth/MeController.php`

**Responsabilidades:**
- Retornar dados do usuário autenticado

**Método:**

```php
public function index() {
    return response()->json(auth()->user());
}
```

### `Auth/DriverRegisterController.php` & `GuardianRegisterController.php`

**Responsabilidades:**
- Registrar novo motorista/guardião
- Criar usuário associado
- Retornar token

---

## 🔄 Fluxos de Autenticação

### Fluxo PORTAL (Web)

```
1. GET  /portal/login
   └─ Web/AuthController@login
      └─ Exibe form de login (auth/login.blade.php)

2. POST /portal/login [email, password, type]
   └─ Web/AuthController@store
      ├─ DriverAuthService::login() ou GuardianAuthService::login()
      ├─ auth()->login($user)  ← CRIA SESSÃO (não token)
      └─ Redireciona /portal/dashboard

3. GET  /portal/dashboard (com autenticação)
   └─ Web/DashboardController@index
      └─ Exibe dashboard (precisa de auth()->user())

4. POST /portal/logout
   └─ Web/AuthController@logout
      ├─ auth()->logout()
      ├─ Invalida sessão
      └─ Redireciona /portal/login
```

### Fluxo API (REST)

```
1. POST /api/v1/auth/login [email, password, type]
   └─ Auth/LoginController@store
      ├─ DriverAuthService::login() ou GuardianAuthService::login()
      ├─ TokenService::createToken($user)  ← CRIA TOKEN (não sessão)
      └─ Retorna JSON { "token": "...", "user": {...} }

2. GET  /api/v1/auth/me [com header Authorization: Bearer {token}]
   └─ Auth/MeController@index
      ├─ middleware 'auth:sanctum'  ← Valida token
      └─ Retorna JSON do usuário

3. POST /api/v1/auth/logout [com header Authorization: Bearer {token}]
   └─ Auth/LogoutController@store
      ├─ middleware 'auth:sanctum'
      ├─ Deleta todos os tokens do usuário
      └─ Retorna JSON { "message": "..." }
```

---

## 🔐 Guards e Middleware

### PORTAL

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    // Usa guard 'web' por padrão
    // Valida sessão (cookie)
});

// Middleware: app/Http/Middleware/Authenticate.php
protected function redirectTo($request) {
    return route('portal.login');  // Redireciona para /portal/login
}
```

### API

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Usa guard 'api' (Sanctum)
    // Valida bearer token
});
```

---

## 🗂️ Hierarquia Completa

```
┌─ PORTAL (Web Interface)
│  ├─ routes/web.php
│  ├─ app/Http/Controllers/Web/
│  │  ├─ AuthController.php
│  │  └─ DashboardController.php
│  └─ resources/views/
│     ├─ auth/login.blade.php
│     └─ dashboard/
│        ├─ index.blade.php
│        ├─ drivers/
│        └─ guardians/
│
├─ API (REST Endpoints)
│  ├─ routes/api.php
│  ├─ app/Http/Controllers/Auth/
│  │  ├─ LoginController.php
│  │  ├─ LogoutController.php
│  │  ├─ MeController.php
│  │  ├─ DriverRegisterController.php
│  │  └─ GuardianRegisterController.php
│  └─ (Respostas JSON)
│
└─ Serviços Compartilhados (app/Services/Auth/)
   ├─ DriverAuthService.php
   ├─ GuardianAuthService.php
   └─ TokenService.php
```

---

## 💡 Decisões de Design

### Por que separar Controllers?

❌ **SEM separação** - Um único LoginController que precisa:
- Decidir se retorna HTML ou JSON
- Decidir se cria sessão ou token
- Decidir para onde redirecionar ou o que retornar

✅ **COM separação** - Cada sistema tem seu próprio controller:
- `Web/AuthController` - sempre retorna view/redirect
- `Auth/LoginController` - sempre retorna JSON

### Por que compartilhar Services?

✅ A lógica de **validação de credenciais** é a mesma
- Ambos checam email + password
- Ambos retornam user + tenant_id
- Reutilização de código

Cada sistema usa o resultado diferente:
- PORTAL: cria SESSION
- API: cria TOKEN

---

## 🚀 Expandindo o Projeto

### Adicionar nova rota ao PORTAL

```php
// 1. Adicione em routes/web.php
Route::prefix('portal')->middleware('auth')->group(function () {
    Route::get('perfil', [ProfileController::class, 'show'])->name('profile.show');
});

// 2. Crie o controller em app/Http/Controllers/Web/ProfileController.php
// 3. Crie a view em resources/views/profile/show.blade.php
```

### Adicionar novo endpoint à API

```php
// 1. Adicione em routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
});

// 2. Crie o controller em app/Http/Controllers/Api/ProfileController.php
// 3. Retorne JSON response
```

---

## ✨ Resumo

| Aspecto | PORTAL | API |
|--------|--------|-----|
| **Localização** | `/portal` | `/api/v1` |
| **Controllers** | `Web/` | `Auth/` |
| **Views** | Blade Templates | JSON |
| **Auth Method** | Session (Cookie) | Bearer Token |
| **Guard** | `web` | `api` (Sanctum) |
| **Usefulness** | Gestão interna | Integrações externas |
| **Usuários** | Administradores/Gerentes | Apps/Clientes |
