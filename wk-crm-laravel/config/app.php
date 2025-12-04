<?php
/**
 * Configuração da Aplicação - WK CRM Brasil
 * 
 * Este arquivo contém as configurações principais da aplicação Laravel,
 * incluindo nome, versão, ambiente e outras configurações essenciais.
 * 
 * Arquitetura: DDD + SOLID + TDD
 * Localização: Brasil - Português Brasileiro
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Nome da Aplicação
    |--------------------------------------------------------------------------
    |
    | Este valor é o nome da sua aplicação, usado quando o framework
    | precisa colocar o nome da aplicação em uma notificação ou outra UI.
    |
    */

    'name' => env('APP_NAME', 'WK CRM Brasil'),

    /*
    |--------------------------------------------------------------------------
    | Versão da Aplicação
    |--------------------------------------------------------------------------
    |
    | Esta versão é utilizada para identificar a build atual da aplicação.
    |
    */

    'version' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Ambiente da Aplicação
    |--------------------------------------------------------------------------
    |
    | Este valor determina o "ambiente" em que sua aplicação está sendo executada.
    | Isso pode determinar como você prefere configurar vários serviços
    | que a aplicação utiliza.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Modo Debug da Aplicação
    |--------------------------------------------------------------------------
    |
    | Quando sua aplicação está em modo debug, mensagens de erro detalhadas
    | com stack traces serão mostradas em cada erro que ocorrer dentro da
    | sua aplicação. Se desabilitado, uma página de erro genérica simples é mostrada.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL da Aplicação
    |--------------------------------------------------------------------------
    |
    | Esta URL é usada pelo console para gerar URLs corretamente quando usar
    | a ferramenta de linha de comando Artisan. Você deve definir isso para a
    | raiz da aplicação para que seja usada ao executar tarefas Artisan.
    |
    */

    'url' => env('APP_URL', 'https://api.consultoriawk.com'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Fuso Horário da Aplicação
    |--------------------------------------------------------------------------
    |
    | Aqui você pode especificar o fuso horário padrão para sua aplicação, que
    | será usado pelas funções de data e hora do PHP. O fuso horário está
    | definido para "America/Sao_Paulo" por padrão.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'America/Sao_Paulo'),

    /*
    |--------------------------------------------------------------------------
    | Configuração de Localização da Aplicação
    |--------------------------------------------------------------------------
    |
    | A localização padrão da aplicação que será usada pelo provedor de
    | tradução. Você pode alterar este valor para qualquer uma das localizações
    | que serão suportadas pela aplicação.
    |
    */

    'locale' => env('APP_LOCALE', 'pt_BR'),

    /*
    |--------------------------------------------------------------------------
    | Localização de Fallback da Aplicação
    |--------------------------------------------------------------------------
    |
    | A localização de fallback determina qual localização usar quando a atual
    | não estiver disponível. Você pode alterar o valor para corresponder a
    | qualquer uma das pastas de idioma fornecidas através da aplicação.
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Localização Faker
    |--------------------------------------------------------------------------
    |
    | Este valor determina a localização que será usada pela biblioteca Faker
    | ao gerar dados falsos para suas seeds de banco de dados. Por exemplo,
    | isso será usado para obter números de telefone, endereços e etc localizados.
    |
    */

    'faker_locale' => env('APP_FAKER_LOCALE', 'pt_BR'),

    /*
    |--------------------------------------------------------------------------
    | Chave de Criptografia
    |--------------------------------------------------------------------------
    |
    | Esta chave é usada pelos serviços de criptografia do Laravel e deve ser
    | definida como uma string aleatória de 32 caracteres para garantir que
    | todos os valores criptografados sejam seguros. Faça isso antes de implantar!
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

        'previous_keys' => array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),

    /*
    |--------------------------------------------------------------------------
    | Driver de Manutenção
    |--------------------------------------------------------------------------
    |
    | Essas opções de configuração determinam o driver usado para determinar e
    | gerenciar o status de "modo de manutenção" do Laravel. O driver "cache"
    | permitirá que o modo de manutenção seja controlado em múltiplas máquinas.
    |
    | Drivers suportados: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE'),
    ],

];