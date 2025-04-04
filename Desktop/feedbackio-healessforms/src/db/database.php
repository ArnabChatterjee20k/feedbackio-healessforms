<!-- dont use it -->
<!-- just a scrappy file -->
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Utopia\CLI\Console;
use Utopia\Database\Adapter\MySQL;
use Utopia\Database\Database;
use Utopia\Database\Document;
use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\Memory;
use Utopia\Database\Helpers\ID;

// Setup database connection
$pdo = new PDO(
    "",
    "",
    ""
);

$cache = new Cache(new Memory());
$database = new Database(new MySQL($pdo), $cache);

$databaseName = 'feedbackio';
$database->setDatabase($databaseName);
try {
    $database->create($databaseName);
    Console::success("Database '$databaseName' created successfully.");
} catch (\Exception $e) {
    Console::info("Database '$databaseName' already exists or could not be created.");
}

// Schema definition
$collections = [
    'form' => [
        '$collection' => ID::custom(Database::METADATA),
        '$id' => ID::custom('form'),
        'name' => 'form',
        'attributes' => [
            [
                '$id' => ID::custom('teamInternalId'),
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
                '$id' => ID::custom('teamId'),
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
                '$id' => ID::custom('indexTeamInternalId'),
                'type' => Database::INDEX_KEY,
                'attributes' => ['teamInternalId'],
                'lengths' => [Database::LENGTH_KEY],
                'orders' => ['ASC'],
            ]
        ],
    ],
];

// Create collections from metadata
foreach ($collections as $key => $collection) {
    if (($collection['$collection'] ?? '') !== Database::METADATA) {
        continue;
    }

    if (!$database->getCollection($key)->isEmpty()) {
        Console::info("Collection '$key' already exists. Skipping...");
        continue;
    }

    Console::info("Creating collection: {$collection['$id']}...");

    $attributes = array_map(fn ($attr) => new Document($attr), $collection['attributes']);
    $indexes = array_map(fn ($index) => new Document($index), $collection['indexes']);

    $database->createCollection($key, $attributes, $indexes);
}

Console::success("✅ Schema setup complete.");

return $database;
