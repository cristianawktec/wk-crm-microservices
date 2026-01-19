-- Atualizar senha do admin@consultoriawk.com
-- Senha: Admin@2025 (hash bcrypt)
UPDATE users 
SET password = '$2y$12$AbCdEfGhIjKlMnOpQrStUvWxYzAb123CdEfGhIjKlMnOpQrStUvWx'
WHERE email = 'admin@consultoriawk.com';

-- Se o usuário não existir, criar
INSERT INTO users (id, name, email, password, created_at, updated_at)
SELECT 
    gen_random_uuid(),
    'Administrador WK',
    'admin@consultoriawk.com',
    '$2y$12$AbCdEfGhIjKlMnOpQrStUvWxYzAb123CdEfGhIjKlMnOpQrStUvWx',
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@consultoriawk.com');
