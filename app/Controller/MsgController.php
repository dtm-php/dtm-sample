<?php

namespace App\Controller;

use DtmClient\Msg;
use DtmClient\TransContext;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: '/msg')]
class MsgController extends AbstractSagaController
{
    private Msg $msg;

    public function __construct(Msg $msg)
    {
        $this->msg = $msg;
    }

    #[RequestMapping(path: 'msg')]
    public function msg()
    {
        $gid = $this->msg->generateGid();
        TransContext::setGid($gid);
        $this->msg->doAndSubmit('http_msg_doAndCommit', function () {
            $this->msg->add('test', ['name' => 'dtmMsg']);
        });
    }


}