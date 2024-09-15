<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();
//'header_key' => 1
$bindArguments = array(
    'x-match' => 'any',
    'delay' => 'delay',
    'header_key' => 2,
);
$bindArguments1 = array(
    'x-match' => 'any',
    'header_key' => 1,
    'delay2' => 'delay2',
);
$bindArguments2 = array(
    'x-match' => 'any',
    'delay' => 1,
    'delay4' => 'delay4',
);
$bindArguments3 = array(
    'x-match' => 'any',
    'header_key' => 1
);
$ar = array(
    $bindArguments, $bindArguments1, $bindArguments2, $bindArguments3
);
foreach ($ar  as $key => $value) {
    $channel->exchange_declare('headers_logs', AMQPExchangeType::HEADERS, false, false, false);

    list($queue_name,,) = $channel->queue_declare("", false, false, true, false);

    $channel->queue_bind($queue_name, 'headers_logs', "", false, new AMQPTable($value));

    $callback = function (AMQPMessage $msg) {
        echo ' [x] ', $msg->getRoutingKey(), ':', $msg->body, "\n";
    };

    $channel->basic_consume($queue_name, '', false, true, false, false, $callback);
}
echo " [*] Waiting for logs. To exit press CTRL+C\n";


while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
