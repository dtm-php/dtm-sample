<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
use App\Middleware\ShowRequestMiddleware;
use DtmClient\Middleware\DtmMiddleware;

return [
    'http' => [
        ShowRequestMiddleware::class,
        DtmMiddleware::class,
    ],
    'grpc' => [
        ShowRequestMiddleware::class,
    ],
];
