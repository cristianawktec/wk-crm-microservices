$PHPCODE = @'
<?php
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$env = [
  'MAIL_MAILER' => getenv('MAIL_MAILER'),
  'MAIL_HOST' => getenv('MAIL_HOST'),
  'MAIL_PORT' => getenv('MAIL_PORT'),
  'MAIL_ENCRYPTION' => getenv('MAIL_ENCRYPTION'),
  'MAIL_FROM_ADDRESS' => getenv('MAIL_FROM_ADDRESS'),
  'MAIL_FROM_NAME' => getenv('MAIL_FROM_NAME'),
  'MAIL_USERNAME' => getenv('MAIL_USERNAME'),
  'MAIL_PASSWORD' => getenv('MAIL_PASSWORD') ? '[set]' : '[empty]',
];

$configFrom = config('mail.from');

echo "=== ENV ===\n";
foreach ($env as $k=>$v) {
  echo $k.'='.$v."\n";
}

echo "\n=== CONFIG mail.from ===\n";
echo 'address=' . ($configFrom['address'] ?? '') . "\n";
echo 'name=' . ($configFrom['name'] ?? '') . "\n";

'@

$PHPCODE | ssh root@72.60.254.100 "cat > /tmp/check-mail.php && docker cp /tmp/check-mail.php wk_crm_laravel:/tmp/ && docker exec wk_crm_laravel php /tmp/check-mail.php"