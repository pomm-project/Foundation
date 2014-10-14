<?php
$loader = require __DIR__ . '/vendor/autoload.php';
$file = __DIR__.'/sources/tests/config.php';

if (file_exists($file)) {
    // custom configuration
    require $file;
} else {
    // we are using travis configuration by default
    $GLOBALS['pomm_db1'] = [
        'dsn' => 'pgsql://postgres@127.0.0.1/travis_ci_test'
        ];
}

