<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->exchange_declare('headers_logs', AMQPExchangeType::HEADERS, false, false, false);

$data = json_encode(array(
    'id' => 1000,
    'name' => 'TEST'
));
$msg = new AMQPMessage($data, array('delivery_mode' => 2));

$msg->set('application_headers', new AMQPTable(array(
    'header_key' => 1
)));
$channel->basic_publish($msg, 'headers_logs');
echo " [x] Sent 'Hello World!'\n";

$channel->close();
$connection->close();



