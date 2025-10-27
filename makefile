setup:
	@echo "Setup the project..."
	@make create-db
	@make schema-update
	@make fixtures-load
	@make cache-clear

create-db:
	@echo "Creating databases..."
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS critipixel WITH (FORCE);"
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "CREATE DATABASE critipixel;"
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS critipixel_test WITH (FORCE);"
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "CREATE DATABASE critipixel_test;"

schema-update:
	@docker exec -it critipixel-app php bin/console doctrine:schema:update --force

make-migration:
	@docker exec -it critipixel-app php bin/console make:migration

fixtures-load:
	@docker exec -it critipixel-app php bin/console doctrine:fixtures:load -n --purge-with-truncate

cache-clear:
	@docker exec -it critipixel-app php bin/console cache:clear

build-scss:
	@docker exec -it critipixel-app php bin/console sass:build

test:
	echo "Running tests..."
	@docker exec -it critipixel-app php bin/console doctrine:schema:update --force --env=test
	@docker exec -it critipixel-app php bin/console doctrine:fixtures:load -n --purge-with-truncate --env=test
	@docker exec -it critipixel-app php bin/phpunit