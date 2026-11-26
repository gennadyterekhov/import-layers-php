# import-layers-php

Checks that each layer imports only from itself and from layers listed earlier in the configuration.

## Configuration

Create `import_layers.json` in the project root.

```json
{
  "layers": ["Domain", "Application"],
  "debug": false,
  "ignoreTests": false
}
```

The analyzer searches upward from the working directory for the nearest configuration. It looks for `import_layers.json`, `import_layers.yaml`, and `import_layers.yml`, in that order within each directory. Use `-c` or `--config` to select a different JSON or YAML file. Its path is relative to the working directory. The project root remains the directory containing the nearest default configuration, or the working directory when there is no default configuration.

A layer can be a PHP namespace sequence such as `App\\Domain` or a complete directory segment such as `Domain`. Slash-separated directories such as `src/Domain` are matched from the project root and take precedence. Within each kind, the first matching entry wins.

## Running the analyzer

Run `php vendor/bin/import-layers.php` in a project that has installed the package via Composer. From the analyzer's own checkout, run `composer install` and use `php src/import-layers.php`. Release executables accept the same options.

```sh
php vendor/bin/import-layers.php
php vendor/bin/import-layers.php src/Domain src/Application.php
php vendor/bin/import-layers.php -c config/layers.yml --graph
```

By default the analyzer checks the whole project. Positional files and directories are relative to the working directory. A non-empty `rootDir` config value selects a project-relative directory and takes precedence over positional paths. Common dependency and build directories such as `vendor`, `node_modules`, and `dist` are skipped. Set `ignoreTests` (or the legacy `ignore_tests` spelling) to also skip `test`, `tests`, and `__tests__` directories and filenames ending in `Test.php` or `_test.php`.

The analyzer checks regular and grouped PHP `use` statements for classes, functions, and constants. It also checks `include`, `require`, and their `_once` variants when their paths are string literals or concatenations with `__DIR__` or `__FILE__`. It looks up relative include paths from the source file's directory, then the project root.

The command prints `OK` on success. Diagnostics go to stderr and include the source path and line number. Exit codes are `0` for success, `1` for a layer violation, and `2` for invalid arguments, configuration, or source files. Set `debug: true` to print each checked file.

With `--graph`, stdout contains JSON with `nodes` (project-relative PHP file paths) and `edges` (objects with `from` and `to` paths). It includes direct edges from selected files to resolved PHP declarations or includes elsewhere in the project, even when the target files fall outside the selected paths. Debug messages go to stderr in this mode. PHP permits `use` declarations that alias a namespace, so an import without a matching source declaration is compared by namespace and has no graph edge. The analyzer does not execute PHP code.

## Development

```sh
composer install
vendor/bin/phpunit
```
