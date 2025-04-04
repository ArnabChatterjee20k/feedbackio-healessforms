<?php
use Utopia\App;
use Utopia\CLI\Console;
use Utopia\Database\Database;
use Utopia\Database\Validator\Authorization;
use Utopia\Database\Helpers\ID;
use Utopia\Database\Document;
use Utopia\Database\Query;
use Utopia\Validator\Text;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;
App::init(function($request){},["request"]);

App::post('/form')
    ->groups(["form"])
    ->param("name", "", new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("database")
    ->action(function (string $name, Request $request, Response $response, Database $database) {
        $id = ID::unique();
        $database->createDocument("form", new Document([
            "name" => $name,
            "form_id" => $id
        ]));
        $response->json(["form_id" => $id]);
    });


App::post('/submit/:id')
    ->groups(["submit"])
    ->param("id", "", new Text(255))
    ->param("key", "", new Text(255))
    ->param("value", "", new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("database")
    ->action(function (string $id,string $key,string $value, Request $request, Response $response, Database $database) {
        $database->createDocument("form_data", new Document([
            "form_id" => $id,
            "key" => $key,
            "value"=>$value
        ]));
        $response->json(["form_id" => $id]);
    });


App::get("/form/:id/result")
    ->groups(["form"])
    ->param("id","",new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("database")
    ->action(function (string $id, Request $request, Response $response, Database $database) {
        $queries = [
            Query::equal("form_id",[$id])
        ];
        $submissions = Authorization::skip(
            fn () => $database->find("form_data",$queries));
        Console::log($id);
        var_dump($submissions);
        $response->json($submissions);
    });