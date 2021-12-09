# Installation

1. [Without Symfony](#without-symfony)
1. [Symfony with Flex](#symfony-with-flex)
1. [Symfony without flex](#symfony-without-flex)
    1. [Doctrine ORM](#doctrine-orm)
    1. [Doctrine ODM](#doctrine-odm)
    1. [Doctrine PHPCR](#doctrine-phpcr)
    1. [Eloquent ORM](#eloquent-orm)

You can use [Composer](https://getcomposer.org/) to install the library to your project:

## Without Symfony

```bash
#
# Without any bridge
#
composer require --dev theofidry/alice-data-fixtures

#
# With Doctrine ORM
#
composer require --dev theofidry/alice-data-fixtures \
                       doctrine/orm \
                       doctrine/data-fixtures


#
# With Doctrine ODM
#
composer require --dev theofidry/alice-data-fixtures \
                       alcaeus/mongo-php-adapter \
                       doctrine/data-fixtures \
                       doctrine/mongodb-odm

#
# With Doctrine PHPCR
#
composer require --dev theofidry/alice-data-fixtures \
                       doctrine/phpcr-odm \
                       jackalope/jackalope-doctrine-dbal

#
# With Eloquent
#
composer require --dev theofidry/alice-data-fixtures \
                       illuminate/database
```

## Symfony with Flex

```bash
#
# Without any bridge
#
composer require --dev theofidry/alice-data-fixtures

#
# With Doctrine ORM
#
composer require --dev theofidry/alice-data-fixtures \
                       doctrine-orm \
                       doctrine/data-fixtures


#
# With Doctrine ODM
#
composer require --dev theofidry/alice-data-fixtures \
                       alcaeus/mongo-php-adapter \
                       doctrine/data-fixtures \
                       doctrine/mongodb-odm

#
# With Doctrine PHPCR
#
composer require --dev theofidry/alice-data-fixtures \
                       doctrine/phpcr-odm \
                       jackalope/jackalope-doctrine-dbal

#
# With Eloquent
#
composer require --dev theofidry/alice-data-fixtures \
                       illuminate/database
```


## Symfony without Flex

This library ships with a Symfony bundle `FidryAliceDataFixturesBundle`.


### Doctrine ORM

To use it with Doctrine do not forget to install `doctrine/doctrine-bundle`
and enable the `DoctrineBundle` (done by default in Symfony Standard Edition).

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


### Doctrine ODM

To use it with Doctrine do not forget to install `doctrine/mongodb-odm`
and enable the `DoctrineMongoDBBundle`.

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


### Doctrine PHPCR

To use it with Doctrine do not forget to install `doctrine/doctrine-bundle`
and enable the `DoctrineBundle` (done by default in Symfony Standard Edition)
and `DoctrinePHPCRBundle` (from `doctrine/phpcr-odm`)

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
        new Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```


### Eloquent ORM

To use it with Eloquent do not forget to install `illuminate/database` and
`WouterJEloquentBundle` (`wouterj/eloquent-bundle`).

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ...
        new WouterJ\EloquentBundle\WouterJEloquentBundle(),
    ];

    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle();
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    //...
    if (in_array($this->getEnvironment(), ['dev', 'test'])) {
        //...
        $bundles[] = new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle();
    }

    return $bundles;
}
```

« [Back to README](../README.md#documentation) • [Usage](../README.md#basic-usage) »
