<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

function message()
{
    global $channel;

    $channel->exchange_declare('logs', 'fanout', false, false, false);
    list($queue_name, ,$consumerCount) = $channel->queue_declare("", false, false, true, false);
    $channel->queue_bind($queue_name, 'logs');
    if ($consumerCount > 1) return;

    $callback = function ($msg) {
        echo ' [x] ', $msg->body, "\n";
        echo ' [x] ', $msg->delivery_info['routing_key'], "\n";
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); // Xác nhận đã xử lý queue use tag
    };

    $channel->basic_qos(null, 1, null); // 1 công nhân chỉ xử ly 1 queue
    $channel->basic_consume($queue_name, '', false, false, false, false, $callback);
}
function processQueue()
{
    global $channel;
    message();
    echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
    while ($channel->is_open()) {
        $channel->wait();
    }
}
processQueue();

$channel->close();
$connection->close();
