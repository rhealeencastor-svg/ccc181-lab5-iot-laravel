<?php

// Creates local configuration only. Never prints the device token.
$path = dirname(__DIR__).'/.env';
if (! file_exists($path)) {
    copy(dirname(__DIR__).'/.env.example', $path);
}
$content = file_get_contents($path);
if (preg_match('/^IOT_DEVICE_TOKEN=[ \t]*$/m', $content)) {
    $content = preg_replace('/^IOT_DEVICE_TOKEN=[ \t]*$/m', 'IOT_DEVICE_TOKEN='.bin2hex(random_bytes(32)), $content);
} elseif (! preg_match('/^IOT_DEVICE_TOKEN=/m', $content)) {
    $content .= "\nIOT_DEVICE_TOKEN=".bin2hex(random_bytes(32))."\n";
}
file_put_contents($path, $content);
echo "Local .env is ready. Set your MySQL details there, then run php artisan key:generate and php artisan migrate.\n";
