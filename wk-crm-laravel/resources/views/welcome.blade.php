<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WK CRM Brasil - API Laravel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
        }

        .logo {
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #fff, #f0f8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            line-height: 1.6;
            opacity: 0.8;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .feature-desc {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        .links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .status {
            margin-top: 3rem;
            padding: 1rem;
            background: rgba(40, 167, 69, 0.2);
            border-radius: 10px;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .status-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .status-info {
            opacity: 0.8;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .logo {
                font-size: 2rem;
            }
            .subtitle {
                font-size: 1.2rem;
            }
            .features {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .links {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🚀 WK CRM Brasil</div>
        <div class="subtitle">API Laravel com Arquitetura DDD + SOLID + TDD</div>
        
        <div class="description">

            Sistema de CRM microservices desenvolvido especialmente para o mercado brasileiro, 
            com arquitetura moderna, escalável e totalmente localizada em português.
        </div>

        <!-- Projetos e Tecnologias -->
        <div style="margin-bottom: 3rem;">
            <h2 style="font-size:1.3rem; margin-bottom:1rem; font-weight:600;">Projetos & Tecnologias</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:1.5rem;">
                <div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:1rem;">
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">📊 Dashboard Admin</div>
                    <div style="font-size:0.95rem; opacity:0.85;">Laravel + Bootstrap</div>
                </div>
                <div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:1rem;">
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">👥 API Clientes</div>
                    <div style="font-size:0.95rem; opacity:0.85;">Laravel (DDD, SOLID, TDD)</div>
                </div>
                <div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:1rem;">
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">🎨 Painel Administrativo</div>
                    <div style="font-size:0.95rem; opacity:0.85;">.NET Core</div>
                </div>
                <div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:1rem;">
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">🌐 Frontend</div>
                    <div style="font-size:0.95rem; opacity:0.85;">Angular</div>
                </div>
                <div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:1rem;">
                    <div style="font-size:1.1rem; font-weight:600; margin-bottom:0.5rem;">🛠️ Outros Serviços</div>
                    <div style="font-size:0.95rem; opacity:0.85;">PHP, Microserviços, Integrações</div>
                </div>
            </div>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">🏗️</div>
                <div class="feature-title">Arquitetura DDD</div>
                <div class="feature-desc">Domain-Driven Design com camadas bem definidas</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">SOLID Principles</div>
                <div class="feature-desc">Código limpo e princípios sólidos de desenvolvimento</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🧪</div>
                <div class="feature-title">Test-Driven Development</div>
                <div class="feature-desc">TDD para garantir qualidade e confiabilidade</div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🇧🇷</div>
                <div class="feature-title">100% Brasileiro</div>
                <div class="feature-desc">Localização completa para o mercado nacional</div>
            </div>
        </div>

        <div class="links">
            <a href="/api/health" class="link">
                🏥 Status da API
            </a>
            
            <a href="/api/dashboard" class="link">
                📊 Dashboard Métricas
            </a>
            
            <a href="http://localhost:8001/admin" class="link">
                🎨 Painel Administrativo
            </a>
            
            <a href="/api/clientes" class="link">
                👥 API Clientes
            </a>
        </div>

        <div class="status">
            <div class="status-title">✅ Sistema Online</div>
            <div class="status-info">
                Versão: {{ config('app.version', '1.0.0') }} | 
                Ambiente: {{ config('app.env') }} | 
                Laravel: {{ app()->version() }} |
                PHP: {{ phpversion() }}
            </div>
        </div>
    </div>
</body>
</html>