<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);

function fib()
{
    $uri = 'http://js-post-api.herokuapp.com/api/products?_limit=10';
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $uri);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

echo " [x] Awaiting RPC requests\n";
$callback = function ($req) {
    $n = (string)$req->body;
    echo ' [.] fib(', $n, ")\n";

    $b = json_decode(fib(), true);
    $msg = new AMQPMessage(
        (string) json_encode($b),
        array('correlation_id' => $req->get('correlation_id'))
    );

    $req->delivery_info['channel']->basic_publish(
        $msg,
        '',
        $req->get('reply_to')
    );
    $req->ack();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

//$channel->close();
//$connection->close();