<?php
namespace Arnab\FeedbackioHealessforms\db;

use PDO;
use Utopia\App;
use Utopia\Cache\Adapter\Memory;
use Utopia\Cache\Cache;
use Utopia\CLI\Console;
use Utopia\Database\Adapter\MySQL;
use Utopia\Database\Database;
use Utopia\Database\Document;
use Utopia\Database\Helpers\ID;
class ORM{
    public Database $database;
    public function __construct(string $db_name){
        $username = App::getEnv("username");
        $password = App::getEnv("password");
        $dsn = App::getEnv("dsn");
        $pdo = new PDO(
            $dsn,
            $username,
            $password
        );
        $adapter = new MySQL($pdo);
        $cache = new Cache(new Memory());
        $this->database = new Database($adapter,$cache);
        $this->database->setDatabase($db_name);
        if(!$this->database->exists()){
            $this->database->create();
        }
    }

    public function setup_schema(){
        $collections = $this->get_schema_definition();
        foreach ($collections as $key => $collection) {
            if (($collection['$collection'] ?? '') !== Database::METADATA) {
                continue;
            }
        
            if (!$this->database->getCollection($key)->isEmpty()) {
                Console::info("Collection '$key' already exists. Skipping...");
                continue;
            }
        
            Console::info("Creating collection: {$collection['$id']}...");
        
            $attributes = array_map(fn ($attr) => new Document($attr), $collection['attributes']);
            $indexes = array_map(fn ($index) => new Document($index), $collection['indexes']);
        
            $this->database->createCollection($key, $attributes, $indexes);
        }
        
        Console::success("✅ Schema setup complete.");
    }

    protected function get_schema_definition(): array
    {
        return [
            'form' => [
                '$collection' => ID::custom(Database::METADATA),
                '$id' => ID::custom('form'),
                'name' => 'form',
                'attributes' => [
                    [
                        '$id' => ID::custom('form_id'),
                        'type' => Database::VAR_STRING,
                        'format' => '',
                        'size' => Database::LENGTH_KEY,
                        'signed' => true,
                        'required' => true,
                        'default' => null,
                        'array' => false,
                        'filters' => [],
                    ],
                    [
                        '$id' => ID::custom('name'),
                        'type' => Database::VAR_STRING,
                        'format' => '',
                        'size' => Database::LENGTH_KEY,
                        'signed' => true,
                        'required' => false,
                        'default' => null,
                        'array' => false,
                        'filters' => [],
                    ],
                ],
                'indexes' => [
                    [
                        '$id' => ID::custom('indexFormId'),
                        'type' => Database::INDEX_KEY,
                        'attributes' => ['form_id'],
                        'lengths' => [Database::LENGTH_KEY],
                        'orders' => ['ASC'],
                    ]
                ],
            ],
            'form_data' => [
                '$collection' => ID::custom(Database::METADATA),
                '$id' => ID::custom('form_data'),
                'name' => 'form',
                'attributes' => [
                    [
                        '$id' => ID::custom('form_id'),
                        'type' => Database::VAR_STRING,
                        'format' => '',
                        'size' => Database::LENGTH_KEY,
                        'signed' => true,
                        'required' => true,
                        'default' => null,
                        'array' => false,
                        'filters' => [],
                    ],
                    [
                        '$id' => ID::custom('key'),
                        'type' => Database::VAR_STRING,
                        'format' => '',
                        'size' => Database::LENGTH_KEY,
                        'signed' => true,
                        'required' => false,
                        'default' => null,
                        'array' => false,
                        'filters' => [],
                    ],
                    [
                        '$id' => ID::custom('value'),
                        'type' => Database::VAR_STRING,
                        'format' => '',
                        'size' => Database::LENGTH_KEY,
                        'signed' => true,
                        'required' => false,
                        'default' => null,
                        'array' => false,
                        'filters' => [],
                    ],
                ],
                'indexes' => [
                    [
                        '$id' => ID::custom('indexFormId'),
                        'type' => Database::INDEX_KEY,
                        'attributes' => ['form_id'],
                        'lengths' => [Database::LENGTH_KEY],
                        'orders' => ['ASC'],
                    ]
                ],
            ],
        ];
    }
}