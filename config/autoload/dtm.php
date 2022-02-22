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
    'protocol' => Protocol::JSONRPC_HTTP,
    'server' => '127.0.0.1',
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    'barrier' => [
        'db' => [
            'type' => DbType::MySQL
        ],
        'redis' => [
            'expire_seconds' => 7 * 86400,
        ],
        'apply' => [
            \App\Controller\TccController::class . '::transBConfirm',
            'App\Controller\TccController::transBCancel',
        ],
    ],
    'guzzle' => [
        'options' => [],
    ],
];
