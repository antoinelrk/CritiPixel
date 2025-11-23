sass-build:
	@symfony console sass:build

create-db:
	@php bin/console doctrine:database:create --env=test

test:
	@composer db-test
	@vendor/bin/phpunit