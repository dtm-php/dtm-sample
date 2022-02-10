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
    'protocol' => Protocol::GRPC,
    'server' => '127.0.0.1',
    'barrier' => [
        'db' => [
            'type' => DbType::MySQL
        ]
    ],
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    'guzzle' => [
        'options' => [],
    ],
];
