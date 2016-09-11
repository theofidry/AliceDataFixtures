# DoctrineFixturesBundle

This bundle is fully compatible with [DoctrineFixturesBundle][1] although it does not make much sense. It is worth noting that there is a difference between [DoctrineFixturesBundle][1] and this bundle in the way to deal with Fixtures. With [DoctrineFixturesBundle][1], you manipulate PHP objects and have to take care of the persistence. This bundle, which heavily relies on [Alice][2], encourage you to declare your fixtures in files (YAML, PHP, ...) without having to worry about the order, dependencies or persistence.

As a result, there is a slight incompatibility if you want to migrate from one to another. If you have a data loaders that implement one of the following interface:

* [`Doctrine\Common\DataFixtures\FixtureInterface`](https://github.com/doctrine/data-fixtures/blob/master/lib/Doctrine/Common/DataFixtures/FixtureInterface.php)
* [`Doctrine\Common\DataFixtures\SharedFixtureInterface`](https://github.com/doctrine/data-fixtures/blob/master/lib/Doctrine/Common/DataFixtures/SharedFixtureInterface.php)
* [`Doctrine\Common\DataFixtures\OrderedFixtureInterface`](https://github.com/doctrine/data-fixtures#orderedfixtureinterface)
* [`Doctrine\Common\DataFixtures\DependentFixtureInterface`](https://github.com/doctrine/data-fixtures#dependentfixtureinterface)

The `php app/console h:d:f:l` command will not work very well if you try to import them with `php app/console h:d:f:l`. If
you were simply using data loaders that was implementing [`Doctrine\Common\DataFixtures\FixtureInterface`](https://github.com/doctrine/data-fixtures/blob/master/lib/Doctrine/Common/DataFixtures/FixtureInterface.php) interface, then no issue should be encountered.

This bundle also provides data loaders and they are fully compatible with Doctrine data loaders and will perfectly work with the Doctrine command `php app/console doctrine:fixtures:load`. Beware that in this case, you will have to manually specify the path to your data loaders if you're using [environment specific fixtures](advanced-usage.md#environment-specific-fixtures).

As a conclusion: if you are using advanced features of [DoctrineFixturesBundle][1], there is a little bit of work to fully migrate from it. If it's too much work, you can always keep both and run the two commands to load all your fixtures.

Previous chapter: [Custom Alice Processors](alice-processors.md)<br />
[Back to Table of Contents](../README.md#documentation)

[1]: https://github.com/doctrine/DoctrineFixturesBundle
[2]: https://github.com/nelmio/alice
