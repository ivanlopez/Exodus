Exodus
====================

Exodus is a content migration library that allows you to dynamically create content migrations.

## Installation
This module can be easily installed by adding `10up/exodus` to your `composer.json` file. Then, either autoload your Composer dependencies or manually `include()` the `Exodus.php` bootstrap file.

Exodus requires WP-CLI. Install it by following [these instructions](http://wp-cli.org).

> Currently the library only support json migations but im planning on
> on creating parsers for sql and xml.

## Creating a Migration Schema Files
```bash
wp exodus schema my_first_migration --type=json
```

Other available configurations:

*  `<name>` The name of your migration
* `--type` The type of migration. You can use json, sql or xml
* `[--post_types]` Comma delimited list of the post types that will be imported. By default it will only do post
* `[--site]` The id of the site you are migrating content to.
* `[--iterator]` The nesting of where the post are in your import file
* `[--report]` Name of the url parameter in your import file in order to export a csv of old to new urls

## Run a Migration
```bash
wp exodus schema my-first-migration --file=test-migration.json
```
Available configurations:

*  `<migration>` Name of the migration ( file name without .php )
* `--file` Path to a valid file. Supported file formats are xml, sql, and json
* `[--force]` Skip the check to see if post already exist
