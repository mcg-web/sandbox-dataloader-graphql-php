<?php

use GraphQL\GraphQL;
use GraphQL\Executor\Promise\Adapter\ReactPromiseAdapter;
use GraphQL\Tests\StarWarsData;
use McGWeb\PromiseFactory\Factory\ReactPromiseFactory;
use Overblog\DataLoader\DataLoader;
use React\Promise\Promise;

require __DIR__.'/vendor/autoload.php';

$calls = 0;
$callsIds = [];
$promiseFactory = new ReactPromiseFactory();
$batchLoadFn = function ($ids) use (&$calls, &$callsIds, $promiseFactory) {
    $callsIds[] = $ids;
    ++$calls;
    $allCharacters = StarWarsData::humans() + StarWarsData::droids();
    $characters = array_intersect_key($allCharacters, array_flip($ids));

    return $promiseFactory->createAll(array_values($characters));
};
$dataLoader = new DataLoader($batchLoadFn, $promiseFactory);

$schema = createSchema(
    function ($character) use ($dataLoader) {
        $onFullFilled = function ($value) use ($dataLoader) {
            return $dataLoader->loadMany($value['friends']);
        };

        if ($character instanceof Promise) {
            return $character->then($onFullFilled);
        } else {
            return $onFullFilled($character);
        }
    },
    function ($root, $args) use ($dataLoader) {
        return $dataLoader->load($args['id']);
    }
);

echo "With DataLoader:\n\n";

GraphQL::setPromiseAdapter(new ReactPromiseAdapter());
executeQueries(
    $schema,
    $calls,
    $callsIds,
    function () use ($dataLoader) {
        $dataLoader->clearAll();
    },
    function ($promise)  {
        return DataLoader::await($promise);
    }
);

