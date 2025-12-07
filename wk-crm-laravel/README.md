# WK CRM Laravel API

## Autenticação (Sanctum)
- Login: `POST /api/login` com `{ "email": "<email>", "password": "<senha>" }`
- Logout: `POST /api/logout` com header `Authorization: Bearer <token>`
- Perfil: `GET /api/me` com header `Authorization: Bearer <token>`
- Rotas protegidas: `/api/customers`, `/api/leads`, `/api/opportunities`, `/api/sellers`, `/api/dashboard`, `/api/vendedores`, `/api/simulate-update`

## Usuário admin padrão (seeder)
- Email: `admin@wkcrm.com`
- Senha: `Admin@12345`
- Criado pelo seeder `AdminUserSeeder`.

Para criar o admin em um ambiente limpo:
```bash
php artisan migrate --seed
# ou, se o banco já está migrado
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
```

## Passo a passo rápido (dev)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Testes
- Rodar suite: `php artisan test`
- Usa SQLite em memória (config no `phpunit.xml.dist`).

## Notas
- Tokens são do tipo Bearer gerados por Sanctum (`user->createToken('api-token')`).
- Se quiser alterar a senha/e-mail padrão, edite `database/seeders/AdminUserSeeder.php` e rode novamente o seeder.
