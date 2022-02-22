<?php

namespace App\Controller;

use App\Grpc\Message\BusiReply;
use App\Grpc\Message\BusiReq;
use DtmClient\Annotation\Barrier;
use DtmClient\Api\ApiInterface;
use DtmClient\Middleware\DtmMiddleware;
use DtmClient\TCC;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: '/tcc-json-rpc')]
class TccJsonRpcController
{
    #[Inject]
    protected TCC $tcc;

    #[Inject]
    protected ApiInterface $api;

    protected string $serviceApi = '127.0.0.1:9503/tcc-json-rpc/';

    #[RequestMapping(path: 'gid')]
    public function getGid()
    {
        return $this->api->generateGid();
    }

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        $this->tcc->globalTransaction(function (TCC $tcc) {
            $body = [
                'Amount' => 30,
            ];
            $tcc->callBranch(
                $body,
                $this->serviceApi . 'TransOutTcc',
                $this->serviceApi . 'TransOutConfirm',
                $this->serviceApi . 'TransOutRevert'
            );
            $tcc->callBranch(
                $body,
                $this->serviceApi . 'TransInTcc',
                $this->serviceApi . 'TransInConfirm',
                $this->serviceApi . 'TransInRevert'
            );
        });
        return 'success';
    }

    #[RequestMapping(path: 'TransOutTcc')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutTcc(BusiReq $request)
    {
        return new BusiReply();
    }

    #[RequestMapping(path: 'transOutConfirm')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutConfirm(BusiReq $request)
    {
        return new BusiReply();
    }

    #[RequestMapping(path: 'transOutCancel')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutCancel(BusiReq $request)
    {
        return new BusiReply();
    }

    #[RequestMapping(path: 'transInTcc')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInTcc(BusiReq $request)
    {
        return new BusiReply();
    }

    #[RequestMapping(path: 'transInConfirm')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInConfirm(BusiReq $request)
    {
        return new BusiReply();
    }

    #[RequestMapping(path: 'transInCancel')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInCancel(BusiReq $request)
    {
        return new BusiReply();
    }
}