<?php
/* configuration for travis-CI */
    $GLOBALS['pomm_db1'] = [
        'dsn' => 'pgsql://postgres@127.0.0.1/pomm_test'
        ];
    $GLOBALS['pomm_db2'] = [
        'dsn' => 'pgsql://postgres@127.0.0.1/pomm_test',
        'date_implementation' => 'chronos'
        ];