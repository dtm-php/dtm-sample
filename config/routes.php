<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;

Router::addServer('grpc', function () {
    Router::addGroup('/busi.Busi', function () {
        Router::post('/TransOutTcc', 'App\Controller\TccGrpcController@transOutTcc');
        Router::post('/TransOutConfirm', 'App\Controller\TccGrpcController@transOutConfirm');
        Router::post('/TransOutRevert', 'App\Controller\TccGrpcController@transOutRevert');
        Router::post('/TransInTcc', 'App\Controller\TccGrpcController@transInTcc');
        Router::post('/TransInConfirm', 'App\Controller\TccGrpcController@transInConfirm');
        Router::post('/TransInRevert', 'App\Controller\TccGrpcController@transInRevert');
    });
});