<?php

use GraphQL\GraphQL;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Schema;

function renderMessage($query, $data, $calls, $callsIds)
{
    echo "Query:\n $query\n\n";
    echo "Response:\n".json_encode($data, JSON_PRETTY_PRINT)."\n";
    echo "Resolver calls: ".var_export($calls, true)."\n";
    echo "calls ids: ".var_export($callsIds, true)."\n";

    echo "\n\n";
}

function createSchema(callable $friendsResolver, callable $characterResolver)
{
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
        'fields' => function () use (&$characterType, $friendsResolver) {
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
                    'resolve' => $friendsResolver,
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
                'resolve' => $characterResolver,
            ],
        ]
    ]);

    return new Schema(['query' => $queryType]);
}

function executeQueries(Schema $schema, &$calls, &$callsIds,  callable $beforeExecute = null, callable $afterExecute = null)
{
    foreach (getQueries() as $query) {
        $calls = 0;
        $callsIds = [];

        if (null !== $beforeExecute) { $beforeExecute(); }
        $result = GraphQL::execute($schema, $query);
        if (null !== $afterExecute) { $result = $afterExecute($result); }

        renderMessage($query, $result, $calls, $callsIds);
    }
}

function getQueries()
{
    $queries = [];
    $queries[] = <<<QUERY
{
  character1: character(id: "1000") {
    name
    friends {
      name
    }
  }
  character2: character(id: "1002") {
    name
    friends {
      name
    }
  }
}
QUERY;

    $queries[] = <<<QUERY
{
  character1: character(id: "1000") {
    name
  }
  character2: character(id: "1002") {
    name
  }
}
QUERY;

    return $queries;
}
