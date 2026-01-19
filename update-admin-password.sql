-- Atualizar senha do admin
-- Nova senha: Admin@2026
-- Hash gerado com bcrypt (custo 10)

UPDATE users 
SET password = '$2y$10$xiAQ7ZESKJ6QHSaxJxuGsOiIeyR/Gl6f2ewcrgC17iLmmoD0un04y'
WHERE email = 'admin@consultoriawk.com';

-- Verificar atualização
SELECT id, name, email, created_at 
FROM users 
WHERE email = 'admin@consultoriawk.com';
