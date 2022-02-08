<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-sample/blob/master/LICENSE
 */
namespace App\Controller;

use DtmClient\Constants\Branch;
use DtmClient\TransContext;
use JetBrains\PhpStorm\ArrayShape;

class AbstractSagaController extends AbstractController
{
    protected const TRANS_OUT_ID = 1;

    protected const TRANS_IN_ID = 2;

    protected int $barrierId = 0;

    protected function buildPayload(int $amount): array
    {
        return [
            'amount' => $amount,
            'transInResult' => '',
            'transOutResult' => '',
            'store' => '',
        ];
    }

    protected function initAccountAmount(int $amount): void
    {
        $this->redis->set($this->getRedisAccountKey(self::TRANS_IN_ID), $amount);
        $this->redis->set($this->getRedisAccountKey(self::TRANS_OUT_ID), $amount);
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
        redis.call('INCRBY', KEYS[1], ARGV[1])
        SCRIPT;
        $result = $this->redis->eval($lua, [$key, $bkey1, $bkey2, $amount, $originOp, $barrierExpire], 3);
        if ($result === 'FAILURE') {
            return false;
        }
        return true;
    }

    protected function getRedisAccountKey($uid): string
    {
        return sprintf('{a}-redis-account-key-%d', $uid);
    }
}
