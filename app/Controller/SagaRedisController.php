<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use DtmClient\Saga;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Redis\Redis;

#[Controller(prefix: '/saga/redis')]
class SagaRedisController extends AbstractSagaController
{
    #[Inject]
    protected Redis $redis;

    protected int $barrierId = 0;

    #[RequestMapping(path: 'successCase')]
    public function successCase(Saga $saga): string
    {
        // Init Accounts
        $this->redis->set($this->getRedisAccountKey(self::TRANS_IN_ID), '100');
        $this->redis->set($this->getRedisAccountKey(self::TRANS_OUT_ID), '100');

        $payload = $this->buildPayload(50);
        $saga->init();
        $saga->add($this->serviceUri . '/saga/redis/transOut', $this->serviceUri . '/saga/redis/transOutCompensate', $payload);
        $saga->add($this->serviceUri . '/saga/redis/transIn', $this->serviceUri . '/saga/redis/transInCompensate', $payload);
        $saga->submit();
        return 'Submitted';
    }

    #[RequestMapping(path: 'rollbackCase')]
    public function rollbackCase(Saga $saga): string
    {
        // Init Accounts
        $this->redis->set($this->getRedisAccountKey(self::TRANS_IN_ID), '20');
        $this->redis->set($this->getRedisAccountKey(self::TRANS_OUT_ID), '20');

        $payload = $this->buildPayload(50);
        $saga->init();
        $saga->add($this->serviceUri . '/saga/redis/transOut', $this->serviceUri . '/saga/redis/transOutCompensate', $payload);
        $saga->add($this->serviceUri . '/saga/redis/transIn', $this->serviceUri . '/saga/redis/transInCompensate', $payload);
        $saga->submit();
        return 'Submitted';
    }

    #[RequestMapping(path: 'transOut')]
    public function transOut(RequestInterface $request, ResponseInterface $response): string|ResponseInterface
    {
        $amount = $request->input('amount');
        if (is_null($amount)) {
            return $response->withStatus(409, 'Amount is required');
        }
        $result = $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_OUT_ID), -$amount, 7 * 86400);
        if ($result === false) {
            return $response->withStatus(409);
        }
        return $response->withStatus(200);
    }

    #[RequestMapping(path: 'transOutCompensate')]
    public function transOutCompensate(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $amount = $request->input('amount');
        if (is_null($amount)) {
            return $response->withStatus(409, 'Amount is required');
        }
        $result = $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_OUT_ID), $amount, 7 * 86400);
        if ($result === false) {
            return $response->withStatus(409);
        }
        return $response->withStatus(200);
    }

    #[RequestMapping(path: 'transIn')]
    public function transIn(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $amount = $request->input('amount');
        if (is_null($amount)) {
            return $response->withStatus(409, 'Amount is required');
        }
        $result = $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_IN_ID), $amount, 7 * 86400);
        if ($result === false) {
            return $response->withStatus(409);
        }
        return $response->withStatus(200);
    }

    #[RequestMapping(path: 'transInCompensate')]
    public function transInCompensate(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $amount = $request->input('amount');
        if (is_null($amount)) {
            return $response->withStatus(409, 'Amount is required');
        }
        $result = $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_IN_ID), -$amount, 1800);
        if ($result === false) {
            return $response->withStatus(409);
        }
        return $response->withStatus(200);
    }
}
