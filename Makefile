COMPOSE=docker compose
WP=$(COMPOSE) run --rm wpcli

.PHONY: up down logs shell install-wp activate build test zip

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

logs:
	$(COMPOSE) logs -f wordpress

shell:
	$(COMPOSE) exec wordpress bash

install-wp:
	$(WP) core install \
		--url=http://localhost:8080 \
		--title="AI Page Assistant Dev" \
		--admin_user=admin \
		--admin_password=admin \
		--admin_email=admin@example.com \
		--skip-email

activate:
	$(WP) plugin activate ai-page-assistant

build:
	npm install
	npm run build

test:
	$(COMPOSE) exec wordpress bash -lc "cd wp-content/plugins/ai-page-assistant && composer install && vendor/bin/phpunit"

zip:
	git archive --format=zip --output=ai-page-assistant.zip HEAD
