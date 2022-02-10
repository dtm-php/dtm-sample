<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    'protocol' => Protocol::HTTP,
    'server' => '127.0.0.1',
    'barrier_db_type' => DbType::MySql,
    'barrier_redis_expire' => 7 * 86400,
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    'guzzle' => [
        'options' => [],
    ],
    'barrier' => [
        \App\Controller\TccController::class . '::transBConfirm',
        'App\Controller\TccController::transBCancel',
    ]
];
