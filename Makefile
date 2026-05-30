.PHONY: up down install migrate fresh test

up: ; docker compose up -d
down: ; docker compose down
install:
	docker compose exec backend composer install
	docker compose exec backend php artisan key:generate
	docker compose exec frontend npm install
migrate: ; docker compose exec backend php artisan migrate
fresh: ; docker compose exec backend php artisan migrate:fresh --seed
test: ; docker compose exec backend ./vendor/bin/pest
