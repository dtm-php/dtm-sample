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
use Hyperf\RpcServer\Annotation\RpcService;

#[Controller(prefix: '/tcc_json_rpc')]
#[RpcService(name: 'TccJsonRpc', protocol: 'jsonrpc-http', server: 'jsonrpc-http')]
class TccJsonRpcController
{
    #[Inject]
    protected TCC $tcc;

    #[Inject]
    protected ApiInterface $api;

    protected string $serviceApi = 'TccJsonRpc.';

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
                $this->serviceApi . 'transOutTcc',
                $this->serviceApi . 'transOutConfirm',
                $this->serviceApi . 'transOutRevert'
            );
            $tcc->callBranch(
                $body,
                $this->serviceApi . 'transInTcc',
                $this->serviceApi . 'transInConfirm',
                $this->serviceApi . 'transInRevert'
            );
        });
        return 'success';
    }

    #[RequestMapping(path: 'TransOutTcc')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutTcc(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transOutTcc'
        ];
    }

    #[RequestMapping(path: 'transOutConfirm')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutConfirm(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transOutConfirm'
        ];
    }

    #[RequestMapping(path: 'transOutCancel')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transOutCancel(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transOutCancel'
        ];
    }

    #[RequestMapping(path: 'transInTcc')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInTcc(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transInTcc'
        ];
    }

    #[RequestMapping(path: 'transInConfirm')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInConfirm(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transInConfirm'
        ];
    }

    #[RequestMapping(path: 'transInCancel')]
    #[Middleware(DtmMiddleware::class)]
    #[Barrier]
    public function transInCancel(BusiReq $request)
    {
        return [
            'dtm_result' => 'SUCCESS',
            'method' => 'transInCancel'
        ];
    }
}