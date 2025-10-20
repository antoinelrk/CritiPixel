setup:
	@echo "Setup the project..."
	@make create-db
	@make schema-update
	@make fixtures-load
	@make build-scss

create-db:
	@echo "Creating databases..."
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "DROP DATABASE IF EXISTS critipixel WITH (FORCE);"
	@docker exec -it critipixel-database psql -U postgres -d postgres -c "CREATE DATABASE critipixel;"

schema-update:
	@docker exec -it critipixel-app php bin/console doctrine:schema:update --force

make-migration:
	@docker exec -it critipixel-app php bin/console make:migration

fixtures-load:
	@docker exec -it critipixel-app php bin/console doctrine:fixtures:load

cache-clear:
	@docker exec -it critipixel-app php bin/console cache:clear

build-scss:
	@docker exec -it critipixel-app php bin/console sass:build