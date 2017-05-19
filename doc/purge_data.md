# Purge data

If you wish to purge the data of your database, you can use a purger.

```php
$loader = $container->get('fidry_alice_data_fixtures.doctrine.purger_loader');
// Or
$loader = $container->get('fidry_alice_data_fixtures.eloquent.purger_loader');

$loader->load([
    'path/to/src/AppBundle/Resources/fixtures/dummy.yml',
    'path/to/src/AppBundle/Resources/fixtures/related_dummy.yml',
]);

$loader->load([
    'path/to/src/AppBundle/Resources/fixtures/dummy.yml',
    'path/to/src/AppBundle/Resources/fixtures/related_dummy.yml',
    [],
    [],
    PurgerLoader::PURGE_MODE_DELETE
]);

$loader->load([
    'path/to/src/AppBundle/Resources/fixtures/dummy.yml',
    'path/to/src/AppBundle/Resources/fixtures/related_dummy.yml',
    [],
    [],
    PurgerLoader::PURGE_MODE_TRUNCATE
]);
```

If no purge mode is specified, the default purge mode is used. Otherwise you can use the `PurgerLoader` constants
to specify the purge mode (delete or truncate).

Beware that the truncate will often fail on the foreign keys check if you did not properly setup the cascade delete
and other issues you may encounter with relationships with Doctrine ORM. Note that they are neither this library or
Doctrine faults: those are issues related to your domain and you alone may solve them.

Purge mode is ignored when using Doctrine MongoDB or PHPCR ODMs.

Previous chapter: [Processors](processors.md)<br />
Go back to [Table of Contents](../README.md#table-of-contents)
