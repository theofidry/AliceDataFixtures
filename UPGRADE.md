# Upgrading guide

## From 0.2 to 1.x

### Upgrading the data loaders

1. You data loader should now either extend [`Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader`](src/Doctrine/DataFixtures/AbstractLoader.php) or implement [`Hautelook\AliceBundle\Doctrine\DataFixtures\LoaderInterface`](src/Doctrine/DataFixtures/LoaderInterface.php).

2. If you were overriding the `::load()` function of the data loader, you should not need it anymore now:
  * Custom Faker providers can now be registered, cf [Custom Faker Providers](src/Resources/doc/faker-providers.md).
  * Custom Alice processors can now be registered, cf [Custom Processors](src/Resources/doc/alice-processors.md).

3. If you had very long path for some fixtures because you needed to refer to the fixtures of another bundle, you can now use the bundle annotation `@Bundlename`.

4. If you had several data loaders to manage different set of fixtures depending of your environment, now you can [devide your fixtures by environment](src/Resources/doc/advanced-usage.md#environment-specific-fixtures) instead of having to use and specify a data loader for that.


### Doctrine command

You should now rely on the bundle command `hautelook_alice:doctrine:fixtures:load` (or `h:d:f:l`) instead of `doctrine:fixtures:load`.


### Remove DoctrineFixturesBundle

As explained [here](src/Resources/doc/doctrine-fixtures-bundle.md), there is no obligation to do so. HautelookAliceBundle is fully compatible with it. However it does not make sense to use the both of them together. It is recommended to
choose only one.

[Back to the documentation](README.md)
