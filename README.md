### Dockerize Me Bundle

![CI](https://img.shields.io/github/actions/workflow/status/thedevopser/dockerize-me/ci.yml?branch=main)

#### English

This Symfony bundle helps you dockerize a Symfony application quickly using FrankenPHP. It provides a Symfony Console command that asks you a few questions in English and generates a ready-to-use Docker setup under a docker/ folder at your project root.

- Multi-stage Dockerfile based on FrankenPHP (Docker Hub image: dunglas/frankenphp): builder, dev (with Xdebug), and stable
- Dev-only docker/compose.yml for local development
- Separate PHP INI files for dev and prod
- Caddyfile configured for a typical Symfony public/ entrypoint
- No APP_ENV is set in Docker or Compose; use your app’s .env.local
- Database connector and extra PHP extensions are configurable; you can choose whether to include a DB service in the dev compose
- Installs only in DEV via Composer (require it as a dev dependency)

Installation

```
composer require --dev thedevopser/dockerize-me
```

Usage

```
php bin/console dockerize:me
```

You will be prompted for:

- Database connector: mysql, mariadb, postgres, sqlite, or none
- Additional PHP extensions: space-separated list, e.g. intl gd redis
- PHP version for the FrankenPHP image, default 8.4 (you can override)
- HTTP port to expose, e.g. 8080

Generated files

- docker/Dockerfile
- docker/compose.yml (dev-only)
- docker/php/dev.ini
- docker/php/prod.ini
- docker/Caddyfile

Dev up

```
docker compose -f docker/compose.yml up -d
```

Contributing

See CONTRIBUTING.md for contributors’ guides: quality checks, tests, CI, commit convention, and Git hooks.

#### Français

Ce bundle Symfony permet de dockeriser rapidement une application Symfony avec FrankenPHP. Il fournit une commande Symfony Console qui pose quelques questions en anglais et génère une configuration Docker clé en main dans le dossier docker/ à la racine du projet.

- Dockerfile multi-stage basé sur FrankenPHP (image Docker Hub: dunglas/frankenphp): builder, dev (avec Xdebug), et stable
- docker/compose.yml dédié au mode dev
- Fichiers PHP INI séparés pour dev et prod
- Caddyfile configuré pour une application Symfony avec public/ comme racine
- Aucun APP_ENV n’est défini dans Docker ou Compose; utilisez les .env.local de l’application
- Connecteur base de données et extensions PHP supplémentaires configurables; vous pouvez choisir d’inclure ou non un service DB dans le compose de dev
- Installation uniquement en DEV via Composer (en dépendance de développement)

Installation

```
composer require --dev thedevopser/dockerize-me
```

Utilisation

```
php bin/console dockerize:me
```

La commande vous demandera :

- Le connecteur de base : mysql, mariadb, postgres, sqlite, ou none
- Des extensions PHP supplémentaires : liste séparée par des espaces, ex : intl gd redis
- La version de PHP pour l’image FrankenPHP, défaut 8.4 (surchage possible)
- Le port HTTP à exposer, ex : 8080

Fichiers générés

- docker/Dockerfile
- docker/compose.yml (mode dev)
- docker/php/dev.ini
- docker/php/prod.ini
- docker/Caddyfile

Démarrer le dev

```
docker compose -f docker/compose.yml up -d
```

Contribuer

Consultez CONTRIBUTING.md pour les informations destinées aux contributeurs : qualité, tests, CI, convention de commit et hooks Git.
