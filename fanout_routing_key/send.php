<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = json_encode(array(
    'id' => 1000,
    'name' => 'TEST'
));
$msg = new AMQPMessage($data, array('delivery_mode' => 2));

$channel->basic_publish($msg, 'logs', 'routing_key');
echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();



