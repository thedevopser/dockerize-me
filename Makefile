.PHONY: quality test hooks rector

quality:
	@docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 phpstan analyse -c phpstan.neon.dist --memory-limit=1G
	@docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 /tools/rector process src --dry-run -c rector.php --ansi

test:
	@docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 ./vendor/bin/phpunit --testdox

hooks:
	@chmod +x .githooks/* 2>/dev/null || true
	@git config core.hooksPath .githooks
	@echo "Git hooks installed (pre-commit & commit-msg)."

rector-dry:
	@docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 /tools/rector process src --dry-run -c rector.php --ansi

rector:
	@docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 /tools/rector process src -c rector.php --ansi
