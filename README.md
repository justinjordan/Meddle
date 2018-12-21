# Meddle

## What is Meddle?

Meddle is a server-side templating engine with the front-end developer in mind. It uses a syntax similar to VueJS or Angular having mustache tags for data interpolation and HTML attributes for control structures.

## Why Meddle?

There's already tons of PHP templating engines out there. Why do we need another one?

Put simply, Meddle is a PHP templating engine with front-end developers in mind. Unlike other back-end templating engines, Meddle syntax is kept in HTML attributes and mustache tags, which, if you're familiar with modern Javascript frameworks like VueJS, Angular, or React, this should be fairly straight forward.

## Basic Usage

### Template (mytemplate.html)
```
<ul>
  <li mdl-for="name in names">{{ name }}</li>
</ul>
```

### PHP (index.php)
```
<?php

require_once(__DIR__ . '/vendor/autoload.php');

$renderer = new Sxule\Meddle();

echo $renderer->render('mytemplate.html', [
    'names' => [
        'John',
        'Teddy',
        'Jane'
    ]
]);
```

### Output

```
<ul>
  <li>John</li>
  <li>Teddy</li>
  <li>Jane</li>
</ul>
```

# Read the [DOCS](https://github.com/sXule/Meddle/tree/master/docs/index.md)