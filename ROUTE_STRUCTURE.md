# 📋 Estrutura de Rotas - Separação PORTAL vs API

## Visão Geral

A aplicação Busko está dividida em **DOIS sistemas distintos**:

1. **PORTAL** - Interface web para gestão (Blade + Sessions)
2. **API** - Endpoints REST para integração (JSON + Bearer Tokens)

---

## 🌐 PORTAL - Web e Sessões

**Localização:** `/portal`  
**Autenticação:** Sessões do Laravel (Cookie)  
**Views:** Blade Templates em `resources/views/`  
**Controllers:** `app/Http/Controllers/Web/`  

### Rotas Públicas (Sem autenticação)

```
GET   /portal/login          (Exibir formulário de login)
POST  /portal/login          (Submeter credenciais)
```

### Rotas Protegidas (Requer autenticação)

```
GET   /portal/dashboard                    (Home da gestão)
GET   /portal/drivers                      (Listagem de motoristas)
GET   /portal/drivers/{id}                 (Detalhes do motorista)
GET   /portal/guardians                    (Listagem de guardiões)
GET   /portal/guardians/{id}               (Detalhes do guardião)
POST  /portal/logout                       (Fazer logout)
```

### Fluxo de Autenticação Portal

```
1. Usuário acessa /portal/login
2. Preenche email, senha e seleciona tipo (driver/guardian)
3. POST /portal/login → valida credenciais
4. Cria sessão com auth()->login($user)
5. Redireciona para /portal/dashboard
6. Pode acessar dashboard e listar dados
7. POST /portal/logout → marca sessão como inválida
8. Redireciona para /portal/login
```

### Exemplo de Uso - Templates

```blade
<!-- Link para dashboard -->
<a href="{{ route('portal.dashboard') }}">Dashboard</a>

<!-- Link para motoristas -->
<a href="{{ route('portal.drivers.index') }}">Ver Motoristas</a>

<!-- Form de logout -->
<form action="{{ route('portal.logout') }}" method="POST">
    @csrf
    <button type="submit">Sair</button>
</form>
```

---

## 🔌 API - REST Endpoints

**Localização:** `/api/v1`  
**Autenticação:** Bearer Token (Laravel Sanctum)  
**Formato:** JSON  
**Controllers:** `app/Http/Controllers/Auth/`  

### Rotas Públicas (Sem autenticação)

```
POST  /api/v1/auth/drivers/register     (Registrar novo motorista)
POST  /api/v1/auth/guardians/register   (Registrar novo guardião)
POST  /api/v1/auth/login                (Fazer login e obter token)
```

### Rotas Protegidas (Requer Bearer Token)

```
GET   /api/v1/auth/me       (Informações do usuário autenticado)
POST  /api/v1/auth/logout   (Fazer logout/revogar token)
```

### Fluxo de Autenticação API

```
1. Cliente faz POST /api/v1/auth/login com email + password
2. API valida credenciais
3. Retorna token em { "plainTextToken": "..." }
4. Cliente armazena token
5. Nas requisições subsequentes, envia: Authorization: Bearer {token}
6. API valida token via middleware 'auth:sanctum'
7. Para logout, POST /api/v1/auth/logout com header Authorization
```

### Exemplo de Uso - Cliente HTTP

```bash
# 1. Fazer login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"driver@test.com","password":"password"}'

# Resposta:
# {
#   "token": "1|gPsKf...",
#   "user": { "id": 1, "name": "...", ... }
# }

# 2. Usar o token
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer 1|gPsKf..."

# 3. Fazer logout
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer 1|gPsKf..."
```

---

## 📁 Estrutura de Arquivos

### Controllers

```
app/Http/Controllers/
├── Web/
│   ├── AuthController.php        ← Login/logout web
│   └── DashboardController.php   ← Páginas do portal
└── Auth/
    ├── LoginController.php       ← Login API
    ├── LogoutController.php      ← Logout API
    ├── MeController.php          ← Info do usuário
    ├── DriverRegisterController.php
    └── GuardianRegisterController.php
```

### Routes

```
routes/
├── web.php                       ← PORTAL (Blade + Sessions)
└── api.php                       ← API v1 (JSON + Tokens)
```

### Views

```
resources/views/
├── layouts/
│   └── app.blade.php            ← Layout principal do portal
├── auth/
│   └── login.blade.php          ← Página de login web
└── dashboard/
    ├── index.blade.php
    ├── drivers/
    │   ├── index.blade.php
    │   └── show.blade.php
    └── guardians/
        ├── index.blade.php
        └── show.blade.php
```

---

## 🔐 Diferenças de Autenticação

| Aspecto | PORTAL (Web) | API (REST) |
|--------|---------|--------|
| **Guard** | `web` | `api` |
| **Driver** | Session (Cookie) | Sanctum (Bearer Token) |
| **Storage** | `sessions` table | `personal_access_tokens` table |
| **Identificação** | `auth()->user()` | `Auth::guard('api')->user()` |
| **Headers** | Automáticos (Cookie) | `Authorization: Bearer {token}` |
| **Acesso** | Navegador (HTML) | HTTP Client/App/Mobile |

---

## ✅ Endpoints em Produção

### Portal
- `https://seu-dominio.com/portal/login`
- `https://seu-dominio.com/portal/dashboard`
- etc.

### API
- `https://seu-dominio.com/api/v1/auth/login`
- `https://seu-dominio.com/api/v1/auth/me`
- etc.

---

## 🧪 Dados de Teste

### Credenciais Padrão (seeder)

**Motorista:**
```
Email: driver@test.com
Senha: password
Tipo: driver
```

**Guardião:**
```
Email: guardian@test.com
Senha: password
Tipo: guardian
```

### Testar Portal
1. Acesse `http://localhost:8000/portal/login`
2. Insira `driver@test.com` / `password` / tipo `driver`
3. Clique em "Entrar"
4. Explore o dashboard

### Testar API
```bash
# Obter token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"driver@test.com","password":"password","type":"driver"}'

# Usar token obtido
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {TOKEN_AQUI}"
```

---

## 📝 Notas

- 🚀 **HomeRoute**: A raiz `/` redireciona automaticamente para `/portal/login` ou `/portal/dashboard`
- 🔄 **Token do API**: Armazenado em `personal_access_tokens` com `tenant_id`
- 🎯 **Multi-tenant**: Ambos os sistemas respeitam isolamento por `tenant_id`
- ⚡ **Assets Vite**: Compilados em `public/build/` (rodar `npm run build`)

---

## 🔧 Comandos Úteis

```bash
# Ver todas as rotas
php artisan route:list

# Ver rotas do portal apenas
php artisan route:list | grep portal

# Ver rotas da API apenas
php artisan route:list | grep "api/v1"

# Limpar cache de rotas
php artisan route:cache

# Executar seeder
php artisan migrate:fresh --seed

# Iniciar servidor
php artisan serve
```
