sass-build:
	@symfony console sass:build

create-db:
	@php bin/console doctrine:database:create --env=test

test:
	@composer db-test
	@vendor/bin/phpunit
	@php -d memory_limit=1G vendor/bin/phpstan analyse

stan:
	@php vendor/bin/phpstan clear-result-cache
	@php -d memory_limit=1G vendor/bin/phpstan analyse