<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use DtmClient\Api\ApiInterface;
use DtmClient\Middleware\DtmMiddleware;
use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use DtmClient\Annotation\Barrier;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Throwable;

class TccController extends AbstractController
{
    #[Inject]
    protected TCC $tcc;

    #[Inject]
    protected ApiInterface $api;

    public function successCase()
    {

        try {
            $this->tcc->globalTransaction(function (TCC $tcc) {
                $tcc->callBranch(
                    ['trans_name' => 'trans_A'],
                    $this->serviceUri . '/tcc/transA/try',
                    $this->serviceUri . '/tcc/transA/confirm',
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                $tcc->callBranch(
                    ['trans_name' => 'trans_B'],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        return TransContext::getGid();
    }

    public function queryAllCase()
    {
        $result = $this->api->queryAll(['last_id' => '']);
        var_dump($result);
    }

    public function rollbackCase()
    {
        try {
            $this->tcc->globalTransaction(function (TCC $tcc) {
                $tcc->callBranch(
                    ['trans_name' => 'trans_A'],
                    $this->serviceUri . '/tcc/transA/try',
                    $this->serviceUri . '/tcc/transA/confirm',
                    $this->serviceUri . '/tcc/transA/cancel'
                );

                $tcc->callBranch(
                    ['trans_name' => 'trans_B'],
                    $this->serviceUri . '/tcc/transB/try/fail',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $exception) {
            // Do Nothing
        }
    }

    public function transATry(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[Barrier]
    public function transAConfirm(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[Barrier]
    public function transACancel(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    public function transBTry(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[Barrier]
    public function transBTryFail(ResponseInterface $response)
    {
        return $response->withStatus(409);
    }

    public function transBConfirm(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    public function transBCancel(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }
}
