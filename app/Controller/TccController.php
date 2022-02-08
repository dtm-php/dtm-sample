<?php

namespace App\Controller;

use DtmClient\TCC;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller(prefix: '/tcc')]
class TccController extends AbstractController
{
    #[GetMapping(path: "successCase")]
    public function successCase()
    {
        $tcc = make(TCC::class);
        $gid = $tcc->generateGid();
        $tcc->tccGlobalTransaction($gid, function (TCC $TCC) {
            $TCC->callBranch([
                'trans_name' => 'trans_A'
            ], 'http://127.0.0.1:9502/tcc/transA/try', 'http://127.0.0.1:9502/tcc/transA/confirm', 'http://127.0.0.1:9502/tcc/transA/cancel');

            $TCC->callBranch([
                'trans_name' => 'trans_B'
            ], 'http://127.0.0.1:9502/tcc/transB/try', 'http://127.0.0.1:9502/tcc/transB/confirm', 'http://127.0.0.1:9502/tcc/transB/cancel');
        });
    }

    #[GetMapping(path: "rollbackCase")]
    public function rollbackCase()
    {
        $tcc = make(TCC::class);
        $gid = $tcc->generateGid();
        $tcc->tccGlobalTransaction($gid, function (TCC $TCC) {
            $TCC->callBranch([
                'trans_name' => 'trans_A'
            ], 'http://127.0.0.1:9502/tcc/transA/try', 'http://127.0.0.1:9502/tcc/transA/confirm', 'http://127.0.0.1:9502/tcc/transA/cancel');

            $TCC->callBranch([
                'trans_name' => 'trans_B'
            ], 'http://127.0.0.1:9502/tcc/transB/try/fail', 'http://127.0.0.1:9502/tcc/transB/confirm', 'http://127.0.0.1:9502/tcc/transB/cancel');
            throw new \Exception('fail');
        });
    }

    #[PostMapping(path: "transA/try")]
    public function TransATry()
    {
        var_dump('trans_A_try');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }

    #[PostMapping(path: "transA/confirm")]
    public function TransAConfirm()
    {
        var_dump('trans_A_confirm');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }

    #[PostMapping(path: "transA/cancel")]
    public function TransACancel()
    {
        var_dump('trans_A_cancel');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }

    #[PostMapping(path: "transB/try")]
    public function TransBTry()
    {
        var_dump('trans_B_try');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }

    #[PostMapping(path: "transB/try/fail")]
    public function TransBTryFail()
    {
        var_dump('trans_B_try_fail');
        return [
            'dtm_result' => 'ERROR'
        ];
    }

    #[PostMapping(path: "transB/confirm")]
    public function transBConfirm()
    {
        var_dump('trans_B_confirm');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }

    #[PostMapping(path: "transB/cancel")]
    public function transBCancel()
    {
        var_dump('trans_B_cancel');
        return [
            'dtm_result' => 'SUCCESS'
        ];
    }


}