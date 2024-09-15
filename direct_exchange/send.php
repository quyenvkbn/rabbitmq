<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$routingKey = $_SERVER['argv'][1] ?? 'routing_key';

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', "direct", false, false, false);

$data = json_encode(array(
    'id' => 1000,
    'name' => 'TEST'
));
$msg = new AMQPMessage($data, array('delivery_mode' => 2));

$channel->basic_publish($msg, '', $routingKey);
echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();



