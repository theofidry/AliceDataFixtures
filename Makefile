PHP_CS_FIXER=php -d zend.enable_gc=0 vendor-bin/php-cs-fixer/bin/php-cs-fixer
DOCKER_COMPOSE=docker compose
DOCKER_COMPOSE_EXEC=$(DOCKER_COMPOSE) exec --no-TTY
ifeq ("$(CI)", "true")
MYSQL_BIN=mysql --user=root --password=password --port=3307
MONGO_BIN=mongosh --username=root --password=password --port=27018
else
MYSQL_BIN=$(DOCKER_COMPOSE_EXEC) mysql mysql --user=root --password=password --host=host.docker.internal --port=3307
MONGO_BIN=$(DOCKER_COMPOSE_EXEC) mongo mongosh --username=root --password=password --host=host.docker.internal --port=27018
endif

RECTOR_BIN = vendor-bin/rector/vendor/bin/rector
RECTOR = $(RECTOR_BIN)

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'


#
# Commands
#---------------------------------------------------------------------------

.PHONY: clean
clean:			## Removes all created artefacts
clean:
	$(MYSQL_BIN) --execute="DROP DATABASE IF EXISTS fidry_alice_data_fixtures;"
	$(MAKE) refresh_mongodb_db

	git clean --exclude=.idea/ -ffdx

.PHONY: refresh_mysql_db
refresh_mysql_db:	## Refresh the MySQL database used
refresh_mysql_db:
	$(MYSQL_BIN) -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures; CREATE DATABASE fidry_alice_data_fixtures;"

.PHONY: refresh_mongodb_db
refresh_mongodb_db:	## Refresh the MongoDB database used
refresh_mongodb_db:
	$(MONGO_BIN) --eval "db.getMongo().getDBNames().filter(dbName => !['admin', 'config', 'local'].includes(dbName)).forEach(dbName => db.getSiblingDB(dbName).dropDatabase())"

.PHONY: refresh_phpcr
refresh_phpcr:		## Refresh the MongoDB PHPCR database used
refresh_phpcr: vendor-bin/doctrine_phpcr/bin/phpcrodm
	$(MYSQL_BIN) -e "DROP DATABASE IF EXISTS fidry_alice_data_fixtures; CREATE DATABASE fidry_alice_data_fixtures;"

	php vendor-bin/doctrine_phpcr/bin/phpcrodm jackalope:init:dbal --force
	php vendor-bin/doctrine_phpcr/bin/phpcrodm doctrine:phpcr:register-system-node-types

.PHONY: remove_sf_cache
remove_sf_cache:	## Removes cache generated by Symfony
remove_sf_cache:
	rm -rf fixtures/Bridge/Symfony/cache/*

.PHONY: cs
cs:             	## Run the CS Fixer
cs: remove_sf_cache \
    vendor/bamarni \
	vendor-bin/php-cs-fixer/vendor
	$(PHP_CS_FIXER) fix

.PHONY: rector_lint
rector_lint: $(RECTOR_BIN)
	$(RECTOR) --dry-run

.PHONY: rector
rector: $(RECTOR_BIN)
	$(RECTOR)

.PHONY: start_databases
start_databases:             	## Start Docker containers
start_databases:
	$(DOCKER_COMPOSE) up --detach --build --force-recreate --renew-anon-volumes

.PHONY: stop_databases
stop_databases:             	## Stop Docker containers
stop_databases:
	$(DOCKER_COMPOSE) stop

#
# Tests
#---------------------------------------------------------------------------

.PHONY: test
test:           				## Run all the tests
test: test_core	\
	  test_doctrine_bridge \
	  test_doctrine_odm_bridge \
	  test_doctrine_phpcr_bridge \
	  test_eloquent_bridge \
	  test_symfony_bridge \
	  test_symfony_doctrine_bridge \
	  test_symfony_doctrine_bridge_proxy_manager \
	  test_symfony_eloquent_bridge \
	  test_symfony_eloquent_bridge_proxy_manager

.PHONY: test_core
test_core:             				## Run the tests for the core library
test_core: vendor/phpunit
	bin/phpunit

.PHONY: test_doctrine_bridge
test_doctrine_bridge:				## Run the tests for the Doctrine bridge
test_doctrine_bridge: vendor/bamarni \
					  vendor-bin/doctrine/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db

	vendor-bin/doctrine/bin/doctrine orm:schema-tool:create

	vendor-bin/doctrine/bin/phpunit -c phpunit_doctrine.xml.dist

.PHONY: test_doctrine_odm_bridge
test_doctrine_odm_bridge:			## Run the tests for the Doctrine ODM bridge
test_doctrine_odm_bridge: vendor/bamarni \
						  vendor-bin/doctrine_mongodb/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mongodb_db

	vendor-bin/doctrine_mongodb/bin/phpunit -c phpunit_doctrine_mongodb.xml.dist

.PHONY: test_doctrine_phpcr_bridge
test_doctrine_phpcr_bridge:			## Run the tests for the Doctrine Mongodb PHPCR bridge
test_doctrine_phpcr_bridge: vendor/bamarni \
							vendor-bin/doctrine_phpcr/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_phpcr

	vendor-bin/doctrine_phpcr/bin/phpunit -c phpunit_doctrine_phpcr.xml.dist

.PHONY: test_eloquent_bridge
test_eloquent_bridge:				## Run the tests for the Eloquent bridge
test_eloquent_bridge: vendor/bamarni \
					  vendor-bin/eloquent/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db

	php bin/eloquent_migrate

	vendor-bin/eloquent/bin/phpunit -c phpunit_eloquent.xml.dist


.PHONY: test_symfony_bridge
test_symfony_bridge:				## Run the tests for the Symfony bridge
test_symfony_bridge: vendor/bamarni \
					 vendor-bin/symfony/vendor/phpunit
	$(MAKE) remove_sf_cache

	vendor-bin/symfony/bin/phpunit -c phpunit_symfony.xml.dist

.PHONY: test_symfony_doctrine_bridge
test_symfony_doctrine_bridge:			## Run the tests for the Symfony Doctrine bridge
test_symfony_doctrine_bridge: vendor/bamarni \
							  vendor-bin/symfony/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db
	$(MAKE) refresh_mongodb_db
	$(MAKE) refresh_phpcr

	php bin/console doctrine:schema:create --kernel=DoctrineKernel

	vendor-bin/symfony/bin/phpunit -c phpunit_symfony_doctrine.xml.dist

.PHONY: test_symfony_eloquent_bridge
test_symfony_eloquent_bridge:			## Run the tests for the Symfony Eloquent bridge
test_symfony_eloquent_bridge: vendor/bamarni \
							  bin/console \
							  vendor-bin/symfony/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db

	php bin/console eloquent:migrate:install --kernel=EloquentKernel

	vendor-bin/symfony/bin/phpunit -c phpunit_symfony_eloquent.xml.dist

.PHONY: test_symfony_doctrine_bridge_proxy_manager
test_symfony_doctrine_bridge_proxy_manager:	## Run the tests for the Symfony Doctrine bridge with Proxy Manager
test_symfony_doctrine_bridge_proxy_manager: vendor/bamarni \
										    bin/console \
											vendor-bin/proxy-manager/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db
	$(MAKE) refresh_mongodb_db
	$(MAKE) refresh_phpcr

	php bin/console doctrine:schema:create --kernel=DoctrineKernel

	vendor-bin/proxy-manager/bin/phpunit -c phpunit_symfony_proxy_manager_with_doctrine.xml.dist

.PHONY: test_symfony_eloquent_bridge_proxy_manager
test_symfony_eloquent_bridge_proxy_manager:	## Run the tests for the Symfony Eloquent bridge with Proxy Manager
test_symfony_eloquent_bridge_proxy_manager: vendor/bamarni \
											bin/console \
											vendor-bin/proxy-manager/vendor/phpunit
	$(MAKE) remove_sf_cache
	$(MAKE) refresh_mysql_db

	php bin/console eloquent:migrate:install --kernel=EloquentKernel

	vendor-bin/proxy-manager/bin/phpunit -c phpunit_symfony_proxy_manager_with_eloquent.xml.dist


#
# Rules from files
#---------------------------------------------------------------------------

composer.lock: composer.json
	@echo composer.lock is not up to date.

vendor/phpunit: composer.lock
	composer update $(COMPOSER_FLAGS)
	touch $@

vendor/bamarni: composer.lock
	composer update $(COMPOSER_FLAGS)
	touch $@


vendor-bin/php-cs-fixer/composer.lock: vendor-bin/php-cs-fixer/composer.json
	@echo php-cs-fixer composer.lock is not up to date.

vendor-bin/php-cs-fixer/vendor: vendor-bin/php-cs-fixer/composer.lock
	composer bin php-cs-fixer update $(COMPOSER_FLAGS)
	touch $@


vendor-bin/doctrine/composer.lock: vendor-bin/doctrine/composer.json
	@echo vendor-bin/doctrine/composer.lock is not up to date.

vendor-bin/doctrine/vendor/phpunit: vendor-bin/doctrine/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin doctrine update $(COMPOSER_FLAGS) \
	fi
	touch $@


vendor-bin/doctrine_mongodb/composer.lock: vendor-bin/doctrine_mongodb/composer.json
	@echo vendor-bin/doctrine_mongodb/composer.lock is not up to date.

vendor-bin/doctrine_mongodb/vendor/phpunit: vendor-bin/doctrine_mongodb/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin doctrine_mongodb update $(COMPOSER_FLAGS) || true \
		composer bin doctrine_mongodb update $(COMPOSER_FLAGS) \
	fi
	touch $@


vendor-bin/doctrine_phpcr/composer.lock: vendor-bin/doctrine_phpcr/composer.json
	@echo vendor-bin/doctrine_phpcr/composer.lock is not up to date.

vendor-bin/doctrine_phpcr/vendor/phpunit: vendor-bin/doctrine_phpcr/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin doctrine_phpcr update $(COMPOSER_FLAGS) \
	fi
	touch $@

vendor-bin/doctrine_phpcr/bin/phpcrodm: vendor-bin/doctrine_phpcr/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin doctrine_phpcr update $(COMPOSER_FLAGS) \
	fi
	touch $@


vendor-bin/eloquent/composer.lock: vendor-bin/eloquent/composer.json
	@echo vendor-bin/eloquent/composer.lock is not up to date.

vendor-bin/eloquent/vendor/phpunit: vendor-bin/eloquent/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin eloquent update $(COMPOSER_FLAGS) || true \
		composer bin eloquent update $(COMPOSER_FLAGS) \
	fi
	touch $@


vendor-bin/symfony/composer.lock: vendor-bin/symfony/composer.json
	@echo vendor-bin/symfony/composer.lock is not up to date.

vendor-bin/symfony/vendor/phpunit: vendor-bin/symfony/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin symfony update $(COMPOSER_FLAGS) || true \
		composer bin symfony update $(COMPOSER_FLAGS) \
	fi
	touch $@

bin/console: vendor-bin/symfony/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin symfony update $(COMPOSER_FLAGS) || true \
		composer bin symfony update $(COMPOSER_FLAGS) \
	fi
	touch $@


vendor-bin/proxy-manager/composer.lock: vendor-bin/proxy-manager/composer.json
	@echo vendor-bin/proxy-manager/composer.lock is not up to date.

vendor-bin/proxy-manager/vendor/phpunit: vendor-bin/proxy-manager/composer.lock
	@if [ -z "$$CI" ]; then \
		composer bin proxy-manager update $(COMPOSER_FLAGS) || true \
		composer bin proxy-manager update $(COMPOSER_FLAGS) \
	fi
	touch $@

.PHONY: rector_install
rector_install: $(RECTOR_BIN)

$(RECTOR_BIN): vendor-bin/rector/vendor
	touch -c $@
vendor-bin/rector/vendor: vendor-bin/rector/composer.lock
	composer bin rector install
	touch -c $@
vendor-bin/rector/composer.lock: vendor-bin/rector/composer.json
	@echo "$(@) is not up to date. You may want to run the following command:"
	@echo "$$ composer bin rector update --lock && touch -c $(@)"
