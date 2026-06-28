# Royal-Atelier

A custom extension framework for [Royal Panel](https://github.com/royaldevlopments/royalpanel) with a modern, PHP-first architecture.

## Features

- **`.royal` package format** — zip-based extensions with `royal.yml` manifest
- **PHP-based installer** — replaces the 1456-line bash `install.sh` with clean, maintainable PHP
- **Comprehensive API** — `ExtensionLibrary` for managing extension configs, settings, and lifecycle
- **Admin UI** — Neon Gaming themed extension manager with install/uninstall/toggle
- **Full lifecycle management** — install, remove, enable/disable extensions via CLI or web
- **React hook injection** — placeholder component replacement, route registration, webpack integration
- **Console commands** — `rx:install`, `rx:uninstall`, `rx:install-ext`, `rx:remove-ext`

## Installation

```bash
composer config repositories.royal-atelier path /path/to/royal-atelier
composer require royalpanel/royal-atelier:@dev
php artisan vendor:publish --provider="RoyalPanel\RoyalAtelier\AtelierServiceProvider" --tag=rx-assets
php artisan migrate
```

## Usage

### CLI

```bash
# Install an extension from a .royal package file
php artisan rx:install-ext extension.royal

# Remove an extension
php artisan rx:remove-ext extension-id

# List installed extensions
php artisan rx:install --list
```

### Admin Panel

Navigate to `/admin/extensions/rx` to manage extensions via the web UI.

## Commands

| Command | Description |
|---------|-------------|
| `rx:install-ext <package.royal>` | Full extension install with React/route/wrapper injection |
| `rx:remove-ext <id>` | Remove an extension and all its hooks |
| `rx:install` | Simple CLI extension installer |
| `rx:uninstall` | Simple CLI extension remover |

## How it works

Royal-Atelier uses `.royal` packages (zip format with `royal.yml` manifest). Extensions are extracted into `.rx/extensions/`, then:

1. Parses `royal.yml` (YAML manifest)
2. Replaces placeholders (`{identifier}`, `{name}`, etc.) in all files
3. Injects React component imports/exports via magic comments
4. Registers routes (web/client/application)
5. Installs Blade wrappers
6. Runs database migrations
7. Executes post-install commands
8. Triggers webpack build

## Requirements

- PHP 8.1+
- Laravel 11+
- Royal Panel 1.12+
- Node.js & npm (for webpack builds)

## License

MIT License — see [LICENSE](LICENSE)
