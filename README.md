# Sandbox DataLoader And GraphQL PHP

`{ character1: character(id: "1000") { name friends { name }} character2: character(id: "1002") { name friends { name }}}`

```
$ php without-dataloader.php

Without DataLoader:

Response:
array (
  'data' => 
  array (
    'character1' => 
    array (
      'name' => 'Luke Skywalker',
      'friends' => 
      array (
        0 => 
        array (
          'name' => 'Han Solo',
        ),
        1 => 
        array (
          'name' => 'Leia Organa',
        ),
        2 => 
        array (
          'name' => 'C-3PO',
        ),
        3 => 
        array (
          'name' => 'R2-D2',
        ),
      ),
    ),
    'character2' => 
    array (
      'name' => 'Han Solo',
      'friends' => 
      array (
        0 => 
        array (
          'name' => 'Luke Skywalker',
        ),
        1 => 
        array (
          'name' => 'Leia Organa',
        ),
        2 => 
        array (
          'name' => 'R2-D2',
        ),
      ),
    ),
  ),
)
Resolver calls: 9
```

```
$ php with-dataloader.php

With DataLoader:

Response:
array (
  'data' => 
  array (
    'character1' => 
    array (
      'name' => 'Luke Skywalker',
      'friends' => 
      array (
        0 => 
        array (
          'name' => 'Han Solo',
        ),
        1 => 
        array (
          'name' => 'Leia Organa',
        ),
        2 => 
        array (
          'name' => 'C-3PO',
        ),
        3 => 
        array (
          'name' => 'R2-D2',
        ),
      ),
    ),
    'character2' => 
    array (
      'name' => 'Han Solo',
      'friends' => 
      array (
        0 => 
        array (
          'name' => 'Luke Skywalker',
        ),
        1 => 
        array (
          'name' => 'Leia Organa',
        ),
        2 => 
        array (
          'name' => 'R2-D2',
        ),
      ),
    ),
  ),
)
Resolver calls: 2
```
