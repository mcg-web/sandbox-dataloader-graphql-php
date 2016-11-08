<?php

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Tests\StarWarsData;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/webonyx/graphql-php/tests/StarWarsData.php';

$calls = 0;

$characterResolver = function ($id) use (&$calls) {
    ++$calls;
    return StarWarsData::getCharacter($id);
};

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
    'fields' => function () use (&$characterType, $characterResolver) {
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
                'resolve' => function ($character) use ($characterResolver) {
                    return array_map($characterResolver, $character['friends']);;
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
            'resolve' => function ($root, $args) use ($characterResolver) {
                return $characterResolver($args['id']);
            },
        ],
    ]
]);

$schema = new Schema(['query' => $queryType]);

$data = GraphQL::execute($schema, '{ character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}');

echo "Without DataLoader:\n\n";
echo "Response:\n".var_export($data, true)."\n";
echo "Resolver calls: ".var_export($calls, true);

echo "\n\n\n";
