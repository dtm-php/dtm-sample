<?php

use DtmClient\Api\JsonRpcHttpApi;

$nodes = [
    ['host' => '127.0.0.1', 'port' => 36791],
];

$options = [
    'connect_timeout' => 5.0,
    'recv_timeout' => 5.0,
    'settings' => [
        // 根据协议不同，区分配置
        'open_eof_split' => true,
        'package_eof' => "\r\n",
        // 'open_length_check' => true,
        // 'package_length_type' => 'N',
        // 'package_length_offset' => 0,
        // 'package_body_offset' => 4,
    ],
    // 重试次数，默认值为 2，收包超时不进行重试。暂只支持 JsonRpcPoolTransporter
    'retry_count' => 2,
    // 重试间隔，毫秒
    'retry_interval' => 100,
    // 当使用 JsonRpcPoolTransporter 时会用到以下配置
    'pool' => [
        'min_connections' => 1,
        'max_connections' => 32,
        'connect_timeout' => 10.0,
        'wait_timeout' => 3.0,
        'heartbeat' => -1,
        'max_idle_time' => 60.0,
    ],
];


$registry = [
//    'protocol' => 'consul',
//    'address' => 'http://127.0.0.1:8500',
];

return [
    // 此处省略了其它同层级的配置
    'consumers' => [
        [
            // name 需与服务提供者的 name 属性相同
            'name' => 'dtmserver',
            // 服务提供者的服务协议，可选，默认值为 jsonrpc-http
            // 可选 jsonrpc-http jsonrpc jsonrpc-tcp-length-check
            'protocol' => 'jsonrpc-http',
            // 负载均衡算法，可选，默认值为 random
            'load_balancer' => 'random',
            // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息
            'registry' => $registry,
            // 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
            'nodes' => $nodes,
            // 配置项，会影响到 Packer 和 Transporter
            'options' => $options,
        ]
    ],
];
