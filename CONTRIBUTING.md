# Contributing

Thanks for taking an interest. Issues and pull requests are welcome.

## Getting set up

Follow the **Local setup** steps in the [README](README.md). You need PHP 8.2+ with `pdo_pgsql`, and a PostgreSQL 15+ database.

## Before opening a pull request

1. Keep changes focused. One fix or feature per pull request.
2. Run `php -l` on any PHP files you touched.
3. Run the security checks against a local server: `php tests/security.php http://localhost:8091`.
4. All SQL must go through PDO prepared statements with bound parameters. No string-built queries from user input.
5. Escape all output in templates with `e()`.
6. Check new screens at 375px wide for horizontal overflow.

## Reporting bugs

Use the bug report issue template. For security issues, follow [SECURITY.md](SECURITY.md) instead.
