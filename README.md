# CalcWise – Financial Calculators

Financial calculators for SIP, FD interest and more, with animated charts. A visitor
fills in a short form, and the result appears as an animated chart.

**Status:** early development, built from
[wordpress-plugin-boilerplate](https://github.com/wpgenius/wordpress-plugin-boilerplate).
Work is planned in [milestones](https://github.com/wpgenius/calcwise/milestones): v1.1.0 brings
the calculators as a block and a shortcode. Formulas come from
[financial-calculators-js](https://github.com/wpgenius/financial-calculators-js).

- Slug and text domain: `calcwise`
- PHP namespace: `WPGenius\CalcWise`, prefix `calcwise_`, constants `CALCWISE_*`

## Try it in your browser

[Open CalcWise in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/wpgenius/calcwise/main/.wordpress-org/blueprints/blueprint.json)
(works from the first release on).

## Requirements

- **To run the plugin:** WordPress 6.5 or later and PHP 7.4 or later.
- **For development (optional):** Composer for the PHP checks and tests,
  Node 20.19+ or 22.12+ (`.nvmrc`) for the block build and JS/CSS lint, and
  Docker for the local WordPress site and the integration tests.

## Local development

**WordPress in Docker** (mounts this repo as the plugin):

```bash
cp .env.example .env
```

```bash
docker compose up -d
```

WordPress runs at http://localhost:8000 and phpMyAdmin at http://localhost:8080.
Change `WP_HTTP_PORT` / `PMA_HTTP_PORT` in `.env` if those ports are taken.

**Code checks** (Composer and npm are for development tools only):

```bash
composer install
```

| Command | What it does |
|---|---|
| `composer lint` | PHP syntax check |
| `composer phpcs` / `composer phpcbf` | WordPress Coding Standards (check / auto-fix) |
| `composer phpstan` | Static analysis: wrong types, missing methods, unchecked `false`/`WP_Error` (level in `phpstan.neon.dist`) |
| `composer test` | Unit tests (PHPUnit + Brain Monkey, no WordPress needed) |
| `npm run env -- start` then `npm run test:integration` | Integration tests in a real WordPress (wp-env, needs Docker): activation, settings and uninstall. `npm run test:integration:multisite` runs them as a network. `npm run env -- stop` when done |
| `composer make-pot` | Regenerate `languages/*.pot` |
| `npm ci` | Install the JS tools |
| `npm run build` / `npm start` | Build the blocks into `build/` (once / watch) |
| `npm run lint` | JS and CSS lint |

Commit `build/` after changing a block; CI fails if it is out of date.

## How changes ship

```
issue  →  branch  →  pull request (Closes #N)  →  squash merge to main  →  automatic release  →  deploy (if enabled)
```

- **`main` is protected.** Changes go through pull requests, merged with squash.
  Repository admins can still bypass the rules (e.g. a quick edit on GitHub).
- **Every PR closes an issue** (you can switch this off: `bin/setup-github.sh --linked-issue off`). The *Linked issue* check fails a PR whose
  description has no `Closes #N`. One request with several changes = one issue per
  change, delivered in one PR.
- **CI** runs on every PR and must pass before merging: PHP 7.4–8.5 syntax, unit tests, integration tests in WordPress (lowest supported and latest), PHPCS, PHPStan, JS/CSS lint, block build and WordPress Plugin Check.
  Plugin Check fails when `Tested up to` in `readme.txt` is behind the latest WordPress,
  so bump it after testing each new WordPress version.
- **Releases are automatic.** On each merge to `main`, the
  [Release workflow](.github/workflows/release.yml):
  1. releases only if the plugin zip would change: it compares the package
     (everything [`.distignore`](.distignore) doesn't exclude) with the last
     release. Docs, CI and config changes don't release;
  2. bumps the version: patch by default, or minor/major when the PR has the
     `release:minor` / `release:major` label;
  3. writes the changelog (one line per closed issue) and the new version into
     `readme.txt` and the plugin header, and commits that to `main`;
  4. tags the commit and publishes a GitHub release with the plugin zip
     (`releases/latest/download/<slug>.zip`).
- **Deploys are off by default.** See below.

## Deploys

The [Deploy workflow](.github/workflows/deploy.yml) runs after each release while
the repo variable `DEPLOY_ENABLED` is `true`. Choose the target with `DEPLOY_METHOD`:

| `DEPLOY_METHOD` | Target | Settings (variables / secrets) |
|---|---|---|
| `ftps` | FTP with TLS (shared hosting) | `FTP_PORT`, `FTP_REMOTE_PATH`, `FTP_SSL_VERIFY` / `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD` |
| `ssh` | rsync over SSH | `SSH_PORT`, `SSH_REMOTE_PATH`, `SSH_DELETE` / `SSH_HOST`, `SSH_USER`, `SSH_PRIVATE_KEY`, `SSH_KNOWN_HOSTS` |
| `wporg` | WordPress.org plugin SVN | `WPORG_SLUG` / `SVN_USERNAME`, `SVN_PASSWORD` (banners and icons go in `.wordpress-org/`) |

To test the connection without uploading, run **Deploy** manually from the
Actions tab with *deploy* unticked.

## License

GPL-3.0-or-later. See [LICENSE](LICENSE).
