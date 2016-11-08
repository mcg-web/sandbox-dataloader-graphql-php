# Sandbox DataLoader And GraphQL PHP

`{ character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}`

```
$ php without-dataloader.php

Without DataLoader:

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
```

```
$ php with-dataloader.php

With DataLoader:

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
```
