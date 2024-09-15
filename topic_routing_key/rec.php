<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->exchange_declare('topic_logs', 'topic', false, false, false);

list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($queue_name, 'topic_logs', "routing_key.*.#");
// các từ hợp lệ VD: routing_key.e, routing_key.e.e
// 1. ( * ) có nghĩa là chính xác một từ được phép
// 2. ( # ) có nghĩa là số lượng từ được phép bằng không hoặc nhiều hơn
// 3. ( . ) Có nghĩa là - dấu phân cách từ

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
