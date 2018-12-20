# Meddle Usage

## Downloading

Run the following in your terminal:

**Composer**
```
$ composer require sxule/meddle
```

**Git**
```
$ git clone https://github.com/sXule/Meddle.git
```

## Including

**Composer**
```
<?php

require_once('path/to/vendor/autoload.php');
```

**Native**
```
<?php

require_once('path/to/meddle/autoload.php');
```

# Rendering Templates

*To use the Meddle library, either require `autoload.php` or require Composer's autoloader.*

```
Sxule\Meddle::render( string $templatePath [[, array $variables ], array $options ]);
```

* $templatePath
    * Path to template to be rendered.
* $variables [optional]
    * Associative array of values and/or callable functions.
* $options [optional]
    * Associative array of options

## Availablie Options

* cacheDir [string] - Path to cache directory. Default: `meddle/cache`
* devMode [boolean] - Transpiles template every load when true.

### Basic Rendering
```
<?php

$output = (new Sxule\Meddle())->render('mytemplate.html');
```

### Render with Variables
```
<?php

$output = (new Meddle())->render('mytemplate.html', [
    'myVariable'    => "Hello, world!",
    'myFunction'    => function ($input) {

        /** do some stuff to $input */

        return $input;
    }
]);
```

***Note:** Variables may contain values or callable functions, which can be used inside the template. Templates only have access to this scope and will not be able to call native constants, variables, or functions (i.e. exec).*

### Render with Options
```
<?php

$output = (new Meddle())->render('mytemplate.html', [], [
    'cacheDir'  => 'path/to/cache/dir'
]);
```

# Mustache Tag Interpolation

Values inside of mustache tags will be outputted and rendered to the document.

**Input**
```
<p>{{ "My name is " . toUpper(myName) }}</p>
```

***Note:** Use periods to concatenate strings.*

**Output**
```
<p>My name is SXULE</p>
```

***WARNING:** Currently, interpolation only works in DOM text; thus, they're unavailable in regular attributes. Attribute interpolation will be included in a future version.*

# Conditionals

Any element containing an `mdl-if` will be evaluated for falsity. If false, the element will be removed from the document.

**Input**
```
<p mdl-if="true">This will be rendered.</p>
<p mdl-if="false">This will NOT be rendered.</p>
```

**Output**
```
<p>This will be rendered.</p>
```

# For Loops

Any element containing an `mdl-for` attribute will be looped and duplicated for every iteration.

**Input**
```
<ul>
  <li mdl-for="i = 1; i <= 3; i++">{{ i }}</li>
</ul>
```

**Output**
```
<ul>
  <li>1</li>
  <li>2</li>
  <li>3</li>
</ul>
```

# Foreach Loops

Any element containing an `mdl-foreach` attribute will be looped and duplicated for every iteration.

**Input**
```
<ul>
  <li mdl-foreach="[1,2,3] as number">{{ number }}</li>
</ul>
```

**Output**
```
<ul>
  <li>1</li>
  <li>2</li>
  <li>3</li>
</ul>
```

