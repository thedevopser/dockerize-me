### Contributing

Thank you for considering a contribution! This repository keeps a strict quality bar and a simple, developer-friendly workflow.

#### Quick start

- Install dependencies: `composer install`
- Run quality: `make quality`
- Run tests: `make test`

If you prefer running tools directly with Docker:

```
docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 \
  phpstan analyse -c phpstan.neon.dist --memory-limit=1G

docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 \
  ./vendor/bin/phpunit --testdox
```

#### Commit message convention

Use the following format:

```
<type>(<scope>): <message>
```

Allowed types: `feat` | `doc` | `ops` | `fix` | `refacto`

Examples:

```
feat(generator): add dev compose flag
fix(ci): stabilize phpstan memory limit
doc(readme): add usage for docker compose
```

#### Git hooks

Install local hooks to ensure quality before every commit and to enforce commit message format:

```
make hooks
```

The `pre-commit` hook runs PHPStan and PHPUnit. The `commit-msg` hook validates the message format. Commits are blocked if checks fail.

#### Pull Requests and CI

- Every push and PR triggers GitHub Actions for quality (PHPStan) and tests (PHPUnit).
- PRs should be mergeable only when both checks are green. In repository settings, enable branch protection and select the checks `quality` and `tests` as required.

#### Coding standards

- No inline comments in source code.
- No `elseif`/`else` and no nested `if`.
- Defensive programming and object calisthenics mindset.
- Prefer precise PHPDoc for types; keep PHPStan at max level.

#### Release and packaging

Files meant only for contributors (CI, hooks, tests, QA configs) are excluded from Composer distribution via `.gitattributes`.

---

#### Contribuer (Français)

Merci de votre contribution ! Ce dépôt applique une qualité stricte avec un flux simple pour les développeurs.

##### Démarrage rapide

- Installer les dépendances : `composer install`
- Lancer la qualité : `make quality`
- Lancer les tests : `make test`

Exécution directe avec Docker :

```
docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 \
  phpstan analyse -c phpstan.neon.dist --memory-limit=1G

docker run --rm -t -v ${PWD}:/project -w /project jakzal/phpqa:php8.4 \
  ./vendor/bin/phpunit --testdox
```

##### Convention de commit

Format :

```
<type>(<scope>): <message>
```

Types autorisés : `feat` | `doc` | `ops` | `fix` | `refacto`

Exemples :

```
feat(generator): ajout de l’option compose dev
fix(ci): stabilisation de la limite mémoire phpstan
doc(readme): ajout de l’utilisation docker compose
```

##### Hooks Git

Installer les hooks locaux pour vérifier la qualité et valider le format des messages de commit :

```
make hooks
```

`pre-commit` exécute PHPStan et PHPUnit. `commit-msg` valide le format du message. Le commit est bloqué en cas d’échec.

##### Pull Requests et CI

- Chaque push et PR lance la CI GitHub (qualité et tests).
- Configurez la protection de branche pour exiger que `quality` et `tests` soient au vert avant fusion.

##### Règles de code

- Pas de commentaires en ligne dans le code source.
- Pas de `elseif`/`else` et pas d’`if` imbriqués.
- Programmation défensive et principes d’object calisthenics.
- PHPDoc précis, PHPStan au niveau maximum.

##### Release et packaging

Les fichiers réservés aux contributeurs (CI, hooks, tests, qualité) sont exclus du paquet Composer via `.gitattributes`.
