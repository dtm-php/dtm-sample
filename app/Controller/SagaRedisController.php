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
use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/saga/redis')]
class SagaRedisController extends AbstractController
{
    public const TRANS_OUT_ID = 1;

    public const TRANS_IN_ID = 2;

    #[Inject]
    protected Redis $redis;

    protected $barrierId;

    #[RequestMapping(path: 'action')]
    public function action(Saga $saga): string
    {
        $baseUri = 'http://127.0.0.1:9502';

        // Init Accounts
        $this->redis->set($this->getRedisAccountKey(self::TRANS_IN_ID), '100');
        $this->redis->set($this->getRedisAccountKey(self::TRANS_OUT_ID), '100');

        $gid = $saga->generateGid();
        $saga->init($gid);
        $payload = [
            'amount' => 50,
            'transInResult' => '',
            'transOutResult' => '',
            'store' => '',
        ];
        $saga->add($baseUri . '/saga/redis/transOut', $baseUri . '/saga/redis/transOutCompensate', $payload);
        $saga->add($baseUri . '/saga/redis/transIn', $baseUri . '/saga/redis/transInCompensate', $payload);
        $saga->submit();
        return 'Submitted';
    }

    #[RequestMapping(path: 'transOut')]
    public function transOut(ServerRequestInterface $request): string
    {
        $amount = $request->getParsedBody()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_OUT_ID), -$amount, 7 * 86400);
        return 'ok';
    }

    #[RequestMapping(path: 'transOutCompensate')]
    public function transOutCompensate(ServerRequestInterface $request): string
    {
        $amount = $request->getParsedBody()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_OUT_ID), $amount, 7 * 86400);
        return 'ok';
    }

    #[RequestMapping(path: 'transIn')]
    public function transIn(ServerRequestInterface $request): string
    {
        $amount = $request->getParsedBody()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_IN_ID), $amount, 7 * 86400);
        return 'ok';
    }

    #[RequestMapping(path: 'transInCompensate')]
    public function transInCompensate(ServerRequestInterface $request): string
    {
        $amount = $request->getParsedBody()['amount'] ?? null;
        if (is_null($amount)) {
            return 'Amount is required';
        }
        $this->redisCheckAdjustAmount($this->getRedisAccountKey(self::TRANS_IN_ID), -$amount, 1800);
        return 'ok';
    }

    protected function redisCheckAdjustAmount(string $key, int $amount, int $barrierExpire): bool|string
    {
        ++$this->barrierId;
        $bkey1 = sprintf('%s-%s-%s-%02d', TransContext::getGid(), TransContext::getBranchId(), TransContext::getOp(), $this->barrierId);
        $originOp = [
            Branch::BranchCancel => Branch::BranchTry,
            Branch::BranchCompensate => Branch::BranchAction,
        ][TransContext::getOp()] ?? '';
        $bkey2 = sprintf('%s-%s-%s-%02d', TransContext::getGid(), TransContext::getBranchId(), $originOp, $this->barrierId);
        $lua = <<<'SCRIPT'
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
        return redis.call('INCRBY', KEYS[1], ARGV[1])
        SCRIPT;
        $result = $this->redis->eval($lua, [$key, $bkey1, $bkey2, $amount, $originOp, $barrierExpire], 3);
        if (is_numeric($result)) {
            return (string) $result;
        }
        return false;
    }

    protected function getRedisAccountKey($uid): string
    {
        return sprintf('{a}-redis-account-key-%d', $uid);
    }
}
