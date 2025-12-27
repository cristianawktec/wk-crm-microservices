<?php
$dir = __DIR__;
$port = 8081;
$server = "0.0.0.0:$port";

echo "Serving $dir on http://localhost:$port\n";

if (php_sapi_name() != 'cli-server') {
    echo "This script must be run with PHP's built-in web server\n";
    exit(1);
}
