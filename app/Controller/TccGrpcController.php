<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use App\Grpc\GrpcClient;
use App\Grpc\Message\BusiReply;
use App\Grpc\Message\BusiReq;
use DtmClient\TCC;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/tcc-grpc')]
class TccGrpcController
{
    #[Inject]
    protected TCC $tcc;

    protected GrpcClient $grpcClient;

    #[Inject]
    protected ConfigInterface $config;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    protected string $serviceApi = '127.0.0.1:9503/busi.Busi/';

    public function __construct()
    {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        $this->tcc->globalTransaction(function (TCC $tcc) {
            $body = new BusiReq([
                'Amount' => 30,
            ]);
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

    public function transOutTcc(BusiReq $request)
    {
        return new BusiReply();
    }

    public function transOutConfirm(BusiReq $request)
    {
        return new BusiReply();
    }

    public function transOutCancel(BusiReq $request)
    {
        return new BusiReply();
    }

    public function transInTcc(BusiReq $request)
    {
        return new BusiReply();
    }

    public function transInConfirm(BusiReq $request)
    {
        return new BusiReply();
    }

    public function transInCancel(BusiReq $request)
    {
        return new BusiReply();
    }
}
