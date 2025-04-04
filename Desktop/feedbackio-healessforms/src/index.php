<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Arnab\FeedbackioHealessforms\db\ORM;
use Utopia\App;
use Utopia\Registry\Registry;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;
use Utopia\CLI\Console;
use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Utopia\Database\Database;

Console::log("Startingggg");

$registry = new Registry();
$registry->set("db", function () {
    $orm = new ORM("feedbackio");
    $orm->setup_schema();
    return $orm->database;
});

App::setResource("database", function () use ($registry) {
    return $registry->get("db");
});

App::error()
    ->inject('error')
    ->inject('response')
    ->action(function (Throwable $error, Response $response) {
        $response->json([
            'message' => $error->getMessage(),
            'line' => $error->getLine(),
            'file' => $error->getFile(),
            'trace' => $error->getTraceAsString(),
        ]);
    });

App::get('/health')
    ->inject('request')
    ->inject('response')
    ->inject("database")
    ->action(function ($request, $response, Database $database) {
        $response->send("Ok");
    });
include_once "Controllers/Forms.php";
$http = new Server("0.0.0.0", 8001);


$http->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {

        $request = new Request($swooleRequest);
        $response = new Response($swooleResponse);
        $app = new App('America/Toronto');
    
        try {
            $app->run($request, $response);
        } catch (\Throwable $th) {
            Console::error('There\'s a problem with '.$request->getURI());
            $swooleResponse->end('500: Server Error');
        }
    });
    
$http->start();