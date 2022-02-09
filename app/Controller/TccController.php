<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use DtmClient\Api\ApiInterface;
use DtmClient\Middleware\BarrierMiddleware;
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

#[Controller(prefix: '/tcc')]
class TccController extends AbstractController
{
    #[Inject]
    protected TCC $tcc;

    #[Inject]
    protected ApiInterface $api;

    #[GetMapping(path: 'successCase')]
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

    #[GetMapping(path: 'query_all')]
    public function queryAllCase()
    {
        $result = $this->api->queryAll(['last_id' => '']);
        var_dump($result);
    }

    #[GetMapping(path: 'rollbackCase')]
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

    #[PostMapping(path: 'transA/try')]
    #[Middleware(BarrierMiddleware::class)]
    public function transATry(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[PostMapping(path: 'transA/confirm')]
    #[Middleware(BarrierMiddleware::class)]
    #[Barrier]
    public function transAConfirm(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[PostMapping(path: 'transA/cancel')]
    #[Middleware(BarrierMiddleware::class)]
    #[Barrier]
    public function transACancel(RequestInterface $request): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[PostMapping(path: 'transB/try')]
    #[Middleware(BarrierMiddleware::class)]
    public function transBTry(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[PostMapping(path: 'transB/try/fail')]
    #[Middleware(BarrierMiddleware::class)]
    #[Barrier]
    public function transBTryFail(ResponseInterface $response)
    {
        return $response->withStatus(409);
    }

    #[PostMapping(path: 'transB/confirm')]
    #[Middleware(BarrierMiddleware::class)]
    #[Barrier]
    public function transBConfirm(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }

    #[PostMapping(path: 'transB/cancel')]
    #[Middleware(BarrierMiddleware::class)]
    #[Barrier]
    public function transBCancel(): array
    {
        return [
            'dtm_result' => 'SUCCESS',
        ];
    }
}
