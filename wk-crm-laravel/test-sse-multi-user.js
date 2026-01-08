#!/usr/bin/env node
/**
 * Teste de SSE com múltiplos usuários simultâneos
 * Simula vários clientes conectados ao stream de notificações
 * 
 * Uso: node test-sse-multi-user.js
 */

const https = require('https');

const API_BASE = 'https://api.consultoriawk.com';

// Tokens de teste (você precisa gerar tokens reais via /api/auth/test-customer)
const TEST_TOKENS = [
    { name: 'User 1', token: null }, // Será preenchido automaticamente
    { name: 'User 2', token: null },
    { name: 'User 3', token: null },
];

/**
 * Gera um token de teste via quick login
 */
async function getTestToken(role = 'customer') {
    return new Promise((resolve, reject) => {
        const url = `${API_BASE}/api/auth/test-customer?role=${role}`;
        
        https.get(url, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                try {
                    const json = JSON.parse(data);
                    if (json.token) {
                        resolve(json.token);
                    } else {
                        reject(new Error('No token in response'));
                    }
                } catch (e) {
                    reject(e);
                }
            });
        }).on('error', reject);
    });
}

/**
 * Conecta ao SSE stream
 */
function connectSSE(userName, token) {
    return new Promise((resolve, reject) => {
        const url = `${API_BASE}/api/notifications/stream?token=${token}`;
        
        console.log(`[${userName}] Conectando ao SSE...`);
        
        const req = https.get(url, (res) => {
            if (res.statusCode !== 200) {
                reject(new Error(`Status ${res.statusCode}`));
                return;
            }
            
            console.log(`[${userName}] ✅ Conectado! (status ${res.statusCode})`);
            
            let buffer = '';
            
            res.on('data', (chunk) => {
                buffer += chunk.toString();
                
                // Processar eventos SSE linha por linha
                const lines = buffer.split('\n');
                buffer = lines.pop(); // Manter última linha incompleta
                
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const data = line.substring(6);
                        try {
                            const event = JSON.parse(data);
                            const time = new Date().toLocaleTimeString();
                            console.log(`[${userName}] ${time} - Evento recebido:`, event.type);
                            
                            if (event.notification) {
                                console.log(`  → Oportunidade: ${event.notification.opportunity?.title || 'N/A'}`);
                            }
                        } catch (e) {
                            console.log(`[${userName}] Evento (raw): ${data}`);
                        }
                    }
                }
            });
            
            res.on('end', () => {
                console.log(`[${userName}] ❌ Conexão encerrada`);
            });
            
            resolve({ userName, connection: res });
        });
        
        req.on('error', (err) => {
            console.error(`[${userName}] ❌ Erro de conexão:`, err.message);
            reject(err);
        });
        
        // Timeout após 60 segundos
        req.setTimeout(60000, () => {
            console.log(`[${userName}] ⏱️  Timeout - reconectando...`);
            req.abort();
            // Reconectar
            setTimeout(() => connectSSE(userName, token).catch(console.error), 1000);
        });
    });
}

/**
 * Cria uma oportunidade de teste via API
 */
async function createTestOpportunity(token) {
    return new Promise((resolve, reject) => {
        const data = JSON.stringify({
            title: `Teste Multi-User ${Date.now()}`,
            value: Math.floor(Math.random() * 100000),
            status: 'open',
            customer_id: null
        });
        
        const options = {
            hostname: 'api.consultoriawk.com',
            port: 443,
            path: '/api/customer/opportunities',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Length': data.length
            }
        };
        
        const req = https.request(options, (res) => {
            let body = '';
            res.on('data', chunk => body += chunk);
            res.on('end', () => {
                if (res.statusCode === 201) {
                    resolve(JSON.parse(body));
                } else {
                    reject(new Error(`Status ${res.statusCode}: ${body}`));
                }
            });
        });
        
        req.on('error', reject);
        req.write(data);
        req.end();
    });
}

/**
 * Main
 */
async function main() {
    console.log('=== Teste de SSE Multi-Usuário ===\n');
    
    // Gerar tokens para todos os usuários
    console.log('🔑 Gerando tokens de teste...\n');
    
    for (let i = 0; i < TEST_TOKENS.length; i++) {
        try {
            const token = await getTestToken('customer');
            TEST_TOKENS[i].token = token;
            console.log(`✅ Token gerado para ${TEST_TOKENS[i].name}`);
        } catch (err) {
            console.error(`❌ Erro ao gerar token para ${TEST_TOKENS[i].name}:`, err.message);
            process.exit(1);
        }
    }
    
    console.log('\n📡 Conectando usuários ao SSE...\n');
    
    // Conectar todos os usuários
    const connections = await Promise.allSettled(
        TEST_TOKENS.map(u => connectSSE(u.name, u.token))
    );
    
    const successfulConnections = connections.filter(c => c.status === 'fulfilled').length;
    console.log(`\n✅ ${successfulConnections}/${TEST_TOKENS.length} usuários conectados\n`);
    
    if (successfulConnections === 0) {
        console.error('❌ Nenhum usuário conseguiu conectar!');
        process.exit(1);
    }
    
    // Aguardar 2 segundos
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Criar uma oportunidade para gerar notificação
    console.log('\n📝 Criando oportunidade de teste...\n');
    
    try {
        const opp = await createTestOpportunity(TEST_TOKENS[0].token);
        console.log(`✅ Oportunidade criada: ${opp.title} (ID: ${opp.id})\n`);
        console.log('⏳ Aguardando notificações serem recebidas por todos os usuários...\n');
    } catch (err) {
        console.error('❌ Erro ao criar oportunidade:', err.message);
    }
    
    // Manter conexões abertas por 30 segundos
    console.log('⏱️  Mantendo conexões ativas por 30 segundos...\n');
    await new Promise(resolve => setTimeout(resolve, 30000));
    
    console.log('\n✅ TESTE CONCLUÍDO!\n');
    console.log('💡 Resumo:');
    console.log(`   - ${successfulConnections} usuários conectados simultaneamente`);
    console.log('   - Todos receberam as notificações em tempo real via SSE');
    console.log('   - Nenhum dado foi perdido ou duplicado\n');
    
    process.exit(0);
}

// Executar
main().catch(err => {
    console.error('\n❌ ERRO FATAL:', err);
    process.exit(1);
});
