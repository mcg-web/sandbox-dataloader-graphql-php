# Sandbox DataLoader And GraphQL PHP

## Queries

```graphql
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
```

```graphql
{
  character1: character(id: "1000") {
    name
  }
  character2: character(id: "1002") {
    name
  }
}
```

## Results

```sh
$ php without-dataloader.php

Without DataLoader:

Query: { character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}
Response:
{
    "data": {
        "character1": {
            "name": "Luke Skywalker",
            "friends": [
                {
                    "name": "Han Solo"
                },
                {
                    "name": "Leia Organa"
                },
                {
                    "name": "C-3PO"
                },
                {
                    "name": "R2-D2"
                }
            ]
        },
        "character2": {
            "name": "Han Solo",
            "friends": [
                {
                    "name": "Luke Skywalker"
                },
                {
                    "name": "Leia Organa"
                },
                {
                    "name": "R2-D2"
                }
            ]
        }
    }
}
Resolver calls: 9
calls ids: array (
  0 => '1000',
  1 => '1002',
  2 => '1003',
  3 => '2000',
  4 => '2001',
  5 => '1002',
  6 => '1000',
  7 => '1003',
  8 => '2001',
)


Without DataLoader:

Query: { character1: character(id: "1000") { name } character2: character(id: "1002") { name }}
Response:
{
    "data": {
        "character1": {
            "name": "Luke Skywalker"
        },
        "character2": {
            "name": "Han Solo"
        }
    }
}
Resolver calls: 2
calls ids: array (
  0 => '1000',
  1 => '1002',
)
```

```sh
$ php with-dataloader.php

With DataLoader:

Query: { character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}
Response:
{
    "data": {
        "character1": {
            "name": "Luke Skywalker",
            "friends": [
                {
                    "name": "Han Solo"
                },
                {
                    "name": "Leia Organa"
                },
                {
                    "name": "C-3PO"
                },
                {
                    "name": "R2-D2"
                }
            ]
        },
        "character2": {
            "name": "Han Solo",
            "friends": [
                {
                    "name": "Luke Skywalker"
                },
                {
                    "name": "Leia Organa"
                },
                {
                    "name": "R2-D2"
                }
            ]
        }
    }
}
Resolver calls: 2
calls ids: array (
  0 => 
  array (
    0 => '1000',
    1 => '1002',
  ),
  1 => 
  array (
    0 => '1003',
    1 => '2000',
    2 => '2001',
  ),
)


With DataLoader:

Query: { character1: character(id: "1000") { name } character2: character(id: "1002") { name }}
Response:
{
    "data": {
        "character1": {
            "name": "Luke Skywalker"
        },
        "character2": {
            "name": "Han Solo"
        }
    }
}
Resolver calls: 1
calls ids: array (
  0 => 
  array (
    0 => '1000',
    1 => '1002',
  ),
)
```
