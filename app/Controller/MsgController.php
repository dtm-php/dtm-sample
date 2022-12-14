<?php

namespace App\Controller;

use DtmClient\Barrier;
use DtmClient\Msg;
use DtmClient\TransContext;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/msg')]
class MsgController extends AbstractSagaController
{
    private Msg $msg;

    public function __construct(Msg $msg, private Barrier $barrier)
    {
        $this->msg = $msg;
    }

    #[RequestMapping(path: 'msg')]
    public function msg()
    {
        $gid = $this->msg->generateGid();
        TransContext::setGid($gid);
        $this->msg->doAndSubmit($this->serviceUri . '/msg/queryPrepared', function () {
            $this->msg->add('test', ['name' => 'dtmMsg']);
        });
    }

    #[RequestMapping(path: 'queryPrepared')]
    public function queryPrepared(RequestInterface $request): string
    {
        $queryParams = $request->query();
        $gid = $queryParams['gid'];
        $transType = $queryParams['trans_type'];

        return $this->barrier->queryPrepared($transType, $gid);
    }


}