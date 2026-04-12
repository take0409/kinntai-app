init:
	docker compose up -d --build
	docker compose exec php composer install
	docker compose exec php cp .env.example .env
	docker compose exec php php artisan key:generate
	docker compose exec php php artisan migrate:fresh --seed

fresh:
	docker compose exec php php artisan migrate:fresh --seed

test:
	docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS demo_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
	docker compose exec php php artisan config:clear
	docker compose exec php php artisan migrate:fresh --env=testing
	docker compose exec php ./vendor/bin/phpunit

up:
	docker compose up -d

down:
	docker compose down --remove-orphans

restart:
	@make down
	@make up

cache:
	docker compose exec php php artisan cache:clear
	docker compose exec php php artisan config:clear
