UPDATE notifications 
SET action_url = REGEXP_REPLACE(action_url, '^https?://[^/]+', '') 
WHERE action_url LIKE 'http%';
