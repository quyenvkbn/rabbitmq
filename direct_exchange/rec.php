<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

function message($key) {
    global $channel;
    $channel->exchange_declare('direct_logs', 'direct', false, false, false);
    list(,,$consumerCount) = $channel->queue_declare($key, false, false, false, false);
    if ($consumerCount > 1) return;

    $callback = function ($msg) use($key){
        echo ' [x] ', $msg->body, " - $key - \n";
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Xác nhận đã xử lý queue use tag
    };

    $channel->basic_qos(null, 1, null); // 1 công nhân chỉ xử ly 1 queue
    $channel->basic_consume($key, '', false, false, false, false, $callback);
}
function processQueue() {
    global $channel;

    $queues = array(
        'routing_key', 'routing_key_test'
    );
    foreach ($queues as $key) {
        message($key);
    }
    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
    while ($channel->is_open()) {
        $channel->wait();
    }
    
}
processQueue();

$channel->close();
$connection->close();