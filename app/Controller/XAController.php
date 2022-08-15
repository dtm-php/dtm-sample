<?php

namespace App\Controller;

use App\Grpc\GrpcClient;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/xa')]
class XAController
{

    private GrpcClient $grpcClient;

    protected string $serviceUri = 'http://127.0.0.1:9502';

    public function __construct(
        private XA $xa,
        protected ConfigInterface $config,
    ) {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }


    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // 开启Xa 全局事物
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // 调用子事物接口
            var_dump('gloabl 1');
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            var_dump('gloabl 1-1');
            // XA http模式下获取子事物返回结构
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // 调用子事物接口
            $payload = ['amount' => 10];
            var_dump('gloabl 2');
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
            var_dump('gloabl 3');
        });
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        var_dump('transIn', $content);
        $amount = $content['amount'] ?? 50;
        // 模拟分布式系统下transIn方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 请使用 DBTransactionInterface 处理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` + ? where id = 1', [$amount]);
            var_dump('====transIn');
        });

        var_dump('return====transIn');
        return ['status' => 0, 'message' => 'ok'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transOut')]
    public function transOut(RequestInterface $request): array
    {
        $content = $request->post('amount');
        var_dump('transOut', $content);
        $amount = $content['amount'] ?? 10;
        // 模拟分布式系统下transOut方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 请使用 DBTransactionInterface 处理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
            var_dump('====transOut');
        });

        var_dump('return====transOut');
        return ['status' => 0, 'message' => 'ok'];
    }
}