# Changelog

## Version 1.x

* Upgraded [nelmio/alice](https://github.com/nelmio/alice) to the version [2.0.0](https://github.com/nelmio/alice/releases/tag/2.0.0)
* Add support for [Doctrine ODM (MongoDB)](http://doctrine-mongodb-odm.readthedocs.org/en/latest/)
* Add support for [Doctrine PHPCR](http://doctrine-phpcr-odm.readthedocs.org/en/latest/)
* Introduced commands:
	* `hautelook_alice:doctrine:fixtures:load`
	* `hautelook_alice:doctrine:mongodb:fixtures:load`
	* `hautelook_alice:doctrine:phpcr:fixtures:load`
* Possibility to [load fixtures by environment](https://github.com/hautelook/AliceBundle/blob/master/src/Resources/doc/advanced-usage.md#environment-specific-fixtures)
* Possibility to [load fixtures by bundle](https://github.com/hautelook/AliceBundle#basic-usage)
* No longer need to create a data loader to load fixtures
* Removed [DoctrineFixturesBundle](https://github.com/doctrine/DoctrineFixturesBundle) dependency
* Now possible to register [Alice Processors][1]
* Now possible to register [Custom Faker Data Providers][2]
* Enhanced documentation

## Version 0.2

Although a developement version, this version was the last one available for almost a year, hence the mention in the changelog.

* Extends [DoctrineFixturesBundle](https://github.com/doctrine/DoctrineFixturesBundle) to allow data loaders to load yaml fixtures files using [nelmio/alice 1.x](https://github.com/nelmio/alice/tree/1.x)
* Added Solarium loaders for Behat

[Back to the documentation](README.md)

[1]: https://github.com/nelmio/alice#processors
[2]: https://github.com/nelmio/alice#custom-faker-data-providers
