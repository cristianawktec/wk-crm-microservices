-- Verificar URLs atuais
SELECT id, action_url FROM notifications WHERE action_url IS NOT NULL LIMIT 10;

-- Atualizar TODAS as URLs para remover protocolo e domínio
UPDATE notifications 
SET action_url = REGEXP_REPLACE(action_url, '^https?://[^#/]+/?#?', '/')
WHERE action_url LIKE 'http%';

-- Verificar resultado
SELECT id, action_url FROM notifications WHERE action_url IS NOT NULL LIMIT 10;
