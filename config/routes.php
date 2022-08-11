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

Router::addGroup('/tcc', function () {
    Router::get('/success', 'App\Controller\TccController::successCase', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::get('/query_all', 'App\Controller\TccController@queryAllCase', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::get('/rollbackCase', 'App\Controller\TccController@rollbackCase', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transA/try', 'App\Controller\TccController@transATry', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transA/confirm', 'App\Controller\TccController@transAConfirm', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transA/cancel', 'App\Controller\TccController@transACancel', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transB/try', 'App\Controller\TccController@transBTry', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transB/try/fail', 'App\Controller\TccController@transBTryFail', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transB/confirm', 'App\Controller\TccController@transBConfirm', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transB/cancel', 'App\Controller\TccController@transBCancel', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
});