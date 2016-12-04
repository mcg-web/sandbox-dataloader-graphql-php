<?php

use GraphQL\Tests\StarWarsData;
use Overblog\DataLoader\DataLoader;
use Overblog\DataLoader\Promise\Adapter\Webonyx\GraphQL\SyncPromiseAdapter;
use Overblog\PromiseAdapter\Adapter\WebonyxGraphQLSyncPromiseAdapter;

require __DIR__.'/../vendor/autoload.php';

$calls = 0;
$callsIds = [];

$graphQLSyncPromiseAdapter = new SyncPromiseAdapter();
$promiseAdapter = new WebonyxGraphQLSyncPromiseAdapter($graphQLSyncPromiseAdapter);
$batchLoadFn = function ($ids) use (&$calls, &$callsIds, $promiseAdapter) {
    $callsIds[] = $ids;
    ++$calls;
    $allCharacters = StarWarsData::humans() + StarWarsData::droids();
    $characters = array_intersect_key($allCharacters, array_flip($ids));

    return $promiseAdapter->createAll(array_values($characters));
};
$dataLoader = new DataLoader($batchLoadFn, $promiseAdapter);

$schema = createSchema(
    function ($character) use ($dataLoader) {
        $promise = $dataLoader->loadMany($character['friends']);
        return $promise;
    },
    function ($root, $args) use ($dataLoader) {
        $promise = $dataLoader->load($args['id']);
        return $promise;
    }
);

echo "With DataLoader (Using native promise):\n\n";

executeQueries(
    $schema,
    $calls,
    $callsIds,
    $graphQLSyncPromiseAdapter,
    function () use ($dataLoader) {
        $dataLoader->clearAll();
    }
);
