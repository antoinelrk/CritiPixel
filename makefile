sass-build:
	@symfony console sass:build

create-db:
	@php bin/console doctrine:database:create --env=test

test:
	@php bin/console doctrine:migrations:migrate --env=test --no-interaction
	@php bin/console doctrine:fixtures:load --env=test --no-interaction
	@vendor/bin/phpunit