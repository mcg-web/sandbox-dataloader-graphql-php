<?php

use GraphQL\GraphQL;
use GraphQL\Promise\PromiseInterface;
use GraphQL\Schema;
use GraphQL\Tests\StarWarsData;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\DataLoader\BatchLoadFn;
use Overblog\DataLoader\DataLoader;
use React\Promise\Promise;

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/webonyx/graphql-php/tests/StarWarsData.php';


class PromiseWrapper implements PromiseInterface
{
    /**
     * @var DataLoader
     */
    public static $dataLoader;

    /**
     * @var Promise
     */
    private $promise;

    /**
     * PromiseWrapper constructor.
     * @param Promise $promise
     */
    public function __construct($promise)
    {
        $this->promise = $promise;
    }

    /**
     * @param Promise $promise
     * @return self
     */
    public static function wrap($promise)
    {
        return new static($promise);
    }

    /**
     * Waits until the promise completes if possible.
     *
     * @return mixed
     * @throws \LogicException if the promise has no wait function or if the
     *                         promise does not settle after waiting.
     */
    public function wait()
    {
        return static::$dataLoader->await($this->promise);
    }

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns
     * a new promise resolving to the return value of the called handler.
     *
     * @param callable $onFulfilled Invoked when the promise fulfills
     * @param callable $onRejected Invoked when the promise is rejected
     *
     * @return $this
     */
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null
    )
    {
        $this->promise = $this->promise->then($onFulfilled, $onRejected);

        return $this;
    }

    /**
     * @return Promise
     */
    public function getPromise()
    {
        return $this->promise;
    }
}


$calls = 0;
$dataLoader = new DataLoader(new BatchLoadFn(
    function ($ids) use (&$calls) {
        ++$calls;
        $allCharacters = StarWarsData::humans() + StarWarsData::droids();
        $characters = array_intersect_key($allCharacters, array_flip($ids));

        return \React\Promise\all(array_values($characters));
    }
));

PromiseWrapper::$dataLoader = $dataLoader;

/**
 * This implements the following type system shorthand:
 *   type Character : Character {
 *     id: String!
 *     name: String
 *     friends: [Character]
 *   }
 */
$characterType = new ObjectType([
    'name' => 'Character',
    'fields' => function () use (&$characterType, $dataLoader) {
        return [
            'id' => [
                'type' => new NonNull(Type::string()),
                'description' => 'The id of the character.',
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'The name of the character.',
            ],
            'friends' => array(
                'type' => Type::listOf($characterType),
                'description' => 'The friends of the character, or an empty list if they have none.',
                'resolve' => function ($character) use ($dataLoader) {
                    $onFullFilled = function ($value) use ($dataLoader) {
                        return $dataLoader->loadMany($value['friends']);
                    };

                    if ($character instanceof Promise) {
                        return PromiseWrapper::wrap($character->then($onFullFilled));
                    } else {
                        return PromiseWrapper::wrap($onFullFilled($character));
                    }
                },
            ),
        ];
    },
]);

/**
 * This implements the following type system shorthand:
 *   type Query {
 *     character(id: String!): Character
 *   }
 *
 */
$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'character' => [
            'type' => $characterType,
            'args' => [
                'id' => [
                    'name' => 'id',
                    'description' => 'id of the character',
                    'type' => Type::nonNull(Type::string())
                ]
            ],
            'resolve' => function ($root, $args) use ($dataLoader) {
                return PromiseWrapper::wrap($dataLoader->load($args['id']));
            },
        ],
    ]
]);

$schema = new Schema(['query' => $queryType]);

$data = GraphQL::execute($schema, '{ character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}');

echo "With DataLoader:\n\n";
echo "Response:\n".json_encode($data, JSON_PRETTY_PRINT)."\n";
echo "Resolver calls: ".var_export($calls, true);

echo "\n\n\n";
