<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use DtmClient\Constants\Branch;
use DtmClient\Constants\Operation;
use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/saga')]
class SagaController extends AbstractController
{

    public const TRANS_OUT_ID = 1;
    public const TRANS_IN_ID = 2;

    #[Inject]
    protected Redis $redis;

    #[RequestMapping(path: 'action')]
    public function action(Saga $saga)
    {
        $baseUri = 'http://127.0.0.1:9502';

        // Init Both Accounts
        $this->redis->set('account:1', '100');
        $this->redis->set('account:2', '100');

        $gid = $saga->generateGid();
        $saga->init($gid);
        $saga->add($baseUri . '/saga/redisTransOut', $baseUri . '/saga/redisTransOutCompensate', [
            'amount' => 50,
            'transInResult' => '',
            'transOutResult' => '',
            'store' => '',
        ]);
        $saga->submit();
        return 'Submitted';
    }

    #[RequestMapping(path: 'redisTransOut')]
    public function redisTransOut(ServerRequestInterface $request)
    {
        $amount = $request->getQueryParams()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_OUT_ID), -$amount, 7 * 86400);
        return 'ok';
    }

    #[RequestMapping(path: 'redisTransOutCompensate')]
    public function redisTransOutCompensate(ServerRequestInterface $request)
    {
        $amount = $request->getQueryParams()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_IN_ID), $amount, 7 * 86400);
        return 'ok';
    }

    protected $barrierId;

    protected function redisCheckAdjustAmount(string $key, int $amount, int $barrierExpire)
    {
        $this->barrierId += 1;
        $bkey1 = sprintf("%s-%s-%s-%02d", TransContext::getGid(), TransContext::getBranchId(), TransContext::getOp(), $this->barrierId);
        $originOp = [
                Branch::BranchCancel => Branch::BranchTry,
                Branch::BranchCompensate => Branch::BranchAction,
            ][TransContext::getOp()] ?? '';
        $bkey2 = sprintf("%s-%s-%s-%02d", TransContext::getGid(), TransContext::getBranchId(), $originOp, $this->barrierId);
        $result = $this->redis->eval(" -- RedisCheckAdjustAmount
local v = redis.call('GET', KEYS[1])
local e1 = redis.call('GET', KEYS[2])

if v == false or v + ARGV[1] < 0 then
	return 'FAILURE'
end

if e1 ~= false then
	return
end

redis.call('SET', KEYS[2], 'op', 'EX', ARGV[3])

if ARGV[2] ~= '' then
	local e2 = redis.call('GET', KEYS[3])
	if e2 == false then
		redis.call('SET', KEYS[3], 'rollback', 'EX', ARGV[3])
		return
	end
end
redis.call('INCRBY', KEYS[1], ARGV[1])
", [[$key, $bkey1, $bkey2], $amount, $originOp]);
        var_dump($result);
    }

    protected function getRedisAccountKey($uid)
    {
        return sprintf("{a}-redis-account-key-%d", $uid);
    }

}
