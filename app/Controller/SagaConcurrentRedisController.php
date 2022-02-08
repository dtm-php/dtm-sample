<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use DtmClient\Saga;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller(prefix: '/saga/concurrent/redis')]
class SagaConcurrentRedisController extends SagaRedisController
{
    #[RequestMapping(path: 'successsCase')]
    public function successConcurrentCase(Saga $saga): string
    {
        // Init Accounts
        $this->redis->set($this->getRedisAccountKey(self::TRANS_IN_ID), '100');
        $this->redis->set($this->getRedisAccountKey(self::TRANS_OUT_ID), '100');

        $payload = $this->buildPayload(50);
        $saga->init();
        $saga->add($this->serviceUri . '/saga/redis/transOut', $this->serviceUri . '/saga/redis/transOutCompensate', $payload);
        $saga->add($this->serviceUri . '/saga/redis/transOut', $this->serviceUri . '/saga/redis/transOutCompensate', $payload);
        $saga->add($this->serviceUri . '/saga/redis/transIn', $this->serviceUri . '/saga/redis/transInCompensate', $payload);
        $saga->add($this->serviceUri . '/saga/redis/transIn', $this->serviceUri . '/saga/redis/transInCompensate', $payload);
        $saga->enableConcurrent();
        $saga->addBranchOrder(2, [0, 1]);
        $saga->addBranchOrder(3, [0, 1]);
        $saga->submit();
        return 'Submitted';
    }
}
