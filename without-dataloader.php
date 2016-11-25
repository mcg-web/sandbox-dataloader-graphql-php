<?php

use GraphQL\Tests\StarWarsData;

require __DIR__.'/vendor/autoload.php';

$calls = 0;
$callsIds = [];

$characterResolver = function ($id) use (&$calls, &$callsIds) {
    $callsIds[] = $id;
    ++$calls;
    return StarWarsData::getCharacter($id);
};

$schema = createSchema(
    function ($character) use ($characterResolver) {
        return array_map($characterResolver, $character['friends']);
    },
    function ($root, $args) use ($characterResolver) {
        return $characterResolver($args['id']);
    }
);

echo "Without DataLoader:\n\n";

executeQueries(
    $schema,
    $calls,
    $callsIds
);
