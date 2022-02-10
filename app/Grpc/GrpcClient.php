<?php

namespace App\Grpc;


use Hyperf\GrpcClient\BaseClient;

class GrpcClient extends BaseClient
{

    public function transOutTcc()
    {
        return $this->_simpleRequest(
            '/busi.Busi/transOutTcc',
            []
        );
    }

}