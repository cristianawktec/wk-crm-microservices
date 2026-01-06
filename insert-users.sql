INSERT INTO users (id, name, email, password, created_at, updated_at) 
VALUES (gen_random_uuid(), 'Administrador', 'admin@consultoriawk.com', '$2y$12$ixKXQAqL0N6LMBvZJwXcG.Qc6PzBlQxT9FZApS1xOk8VRpjRdnwWm', NOW(), NOW());

INSERT INTO users (id, name, email, password, created_at, updated_at) 
VALUES (gen_random_uuid(), 'Cliente', 'customer@consultoriawk.com', '$2y$12$ixKXQAqL0N6LMBvZJwXcG.Qc6PzBlQxT9FZApS1xOk8VRpjRdnwWm', NOW(), NOW());

SELECT 'Usuarios inseridos: ' || COUNT(*) FROM users;
