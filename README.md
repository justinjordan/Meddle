# Meddle

## What is Meddle?

Meddle is a server-side templating engine with the front-end developer in mind. It uses a syntax similar to VueJS or Angular having mustache tags for data interpolation and HTML attributes for control structures.

## Why Meddle?

There's already tons of PHP templating engines out there. Why do we need another one?

Put simply, Meddle is a PHP templating engine with clean HTML in mind. The other templating engines are great, but, let's be honest, they don't really get you away from the perils of mixing PHP into your templates.

## Templating Examples

**Vanilla PHP**

```
<ul>
  <? foreach ($names as $name): ?>
    <li><?= $name ?></li>
  <? endforeach; ?>
</ul>
```

Ya know, this actually isn't that bad. If you're using shorthand tags, vanilla PHP may be all you need for a project. However, when written poorly, PHP can get quite verbose and, not to mention, the fears of letting a novice front-end developer get their hands on functions like `exec()` or `unlink()` are too real.

**Twig**

```
<ul>
  {% for name in names %}
    <li>{{ name }}</li>
  {% endfor %}
</ul>
```

Twig is actually a nice templating engine. It's a bit verbose, but powerful and pretty fast too. The drawback really is that it's not super approachable for front-end developers.

**Meddle**

```
<ul>
  <li mdl-for="name in names">{{ name }}</li>
</ul>
```

If you're a front-end developer, you're probably comfortable with Meddle syntax thanks to Angular or VueJS. Meddle protects the server from script kiddie horrors like other templating systems, but is far more approachable for front-end developers.

# Read the [DOCS](https://github.com/sXule/Meddle/tree/master/docs/index.md)