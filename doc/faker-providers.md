# Faker Providers

## Simple Provider

As explained in [nelmio/alice](https://github.com/nelmio/alice#custom-faker-data-providers) documentation, you have
three ways to declare custom data provider. To use [Custom Faker Provider classes][1]
you will have to declare them as services:

```php
<?php

namespace AppBundle\DataFixtures\Faker\Provider;

class FooProvider
{
    public static function foo($str)
    {
        return 'foo'.$str;
    }
}
```

Then declare it as a service with the `hautelook_alice.faker.provider` tag:

```yaml
# app/config/services.yml

services:
    faker.provider.foo:
        class: AppBundle\DataFixtures\Faker\Provider\FooProvider
        tags: [ { name: hautelook_alice.faker.provider } ]
```

That's it! You can now use it in your fixtures:

```yaml
# src/AppBundle/DataFixtures/ORM/dummy.yml

AppBundle\Entity\Dummy:
    brand{1..10}:
        name: <foo('a string')>
```

**Warning**: rely on [Custom Faker Providers helpers][2] to generate random data (most of them are static).

## Advanced Provider

Sometimes, your Provider needs to extend the [Faker Base Provider][2]
or one of it's children. The issue is it needs a [`Faker\Generator`](https://github.com/fzaninotto/Faker/blob/master/src/Faker/Generator.php)
instance. AliceBundle provides a faker generator `hautelook_alice.faker` configured with the bundle parameters and with all the registered providers. **You must not use this one for your providers**: as this generator requires all providers, if your provider requires this generator this will result in a circular references. In such cases, you should use your own Faker generator:

```yaml
# app/config/services.yml
services:
    hautelook_alice.bare_faker:
        class: Faker\Generator
        factory: [ Faker\Factory, create ]
        lazy: true
        arguments:
            - %hautelook_alice.locale%
        calls:
            - [ seed, [ %hautelook_alice.seed% ] ]
```

Example:
```php
<?php

namespace AppBundle\DataFixtures\Faker\Provider;

use Faker\Provider\Base as BaseProvider;

class FooProvider extends BaseProvider;
{
    public static function foo($str)
    {
        return 'foo'.$str;
    }
}
```

```yaml
# app/config/services.yml

services:
    faker.provider.foo:
        class: AppBundle\DataFixtures\Faker\Provider\FooProvider
        arguments: [ @hautelook_alice.bare_faker ]
        tags: [ { name: hautelook_alice.faker.provider } ]
```

Previous chapter: [Advanced usage](advanced-usage.md)<br />
Next chapter: [Custom Alice Processors](alice-processors.md)

[1]: https://github.com/nelmio/alice/blob/master/doc/customizing-data-generation.md#add-a-custom-faker-provider-class
[2]: https://github.com/fzaninotto/Faker/blob/master/src/Faker/Provider/Base.php
