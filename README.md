Exodus
====================

Content migration library that allows you to dynamically create content migrations independent of the data structure.

## Installation
This module can be easily installed by adding `10up/exodus` to your `composer.json` file. Then, either autoload your Composer dependencies or manually `include()` the `Exodus.php` bootstrap file.

Exodus requires WP-CLI. Install it by following [these instructions](http://wp-cli.org).

> Currently the library only support json migrations but im planning on
> on creating parsers for sql and xml.

## Creating a Migration Schema Files
```bash
wp exodus schema my_first_migration --type=json
```

**Other available Configurations:**

*  `<name>` The name of your migration
* `--type` The type of migration. You can use json, sql or xml
* `[--post_types]` Comma delimited list of the post types that will be imported. By default it will only do post
* `[--site]` The id of the site you are migrating content to.
* `[--iterator]` The nesting of where the post are in your import file
* `[--report]` Name of the url parameter in your import file in order to export a csv of old to new urls
* `[--verify]` Percent ( 1-100 ) of random post to verify that were imported successfully

### Adding a Schema Map

Sample JSON import file
```bash
[{
"timestamp": 1408464060,
"title": "Test title",
"body": "Body content",
"post_url": "http://test.com/test-title/",
"post_id": 1234,
"tags" : [ "Tag 1", "Tag 2"]
},{
"timestamp": 1408677660,
"title": "Test title 2",
"body": "Body content 2",
"post_url": "http://test.com/test-title-2/",
"post_id": 1234,
"tags" : []
}]
```

Sample Schema Map
```bash
Map::factory()->create( 'post', function ( $data ) {
	$data->post_title( 'title' );
	$data->post_content( 'body' );
	$data->post_date( 'timestamp' );
	$data->meta_data( 'post_url', 'post_url' );
	$data->meta_data( 'post_id', 'id' );
	$data->taxonomy( 'post_tag', 'tags' );
} );
```

**Available Schema Commands:**

`$key` stands for the key that the WordPress attribute represents in your content. For example in the JSON above the key for the post_content is "body".

`$meta_key` stands for the key the meta data will have in WordPress.

`$taxonomy` stands for the taxonomy label inside of WordPress.


| Commands                             | Description                               |
| ------------------------------------ | ----------------------------------------- |
| $data->post_type( $key );            | Map the post type attribute.              |
| $data->post_title( $key );           | Map the post title attribute.             |
| $data->post_content( $key );         | Map the post content attribute.           |
| $data->post_author( $key );          | Map the post author attribute.            |
| $data->post_excerpt( $key );         | Map the post excerpt.                     |
| $data->post_date( $key );            | Map the post date.                        |
| $data->post_date_gmt( $key );        | Map the post date GMT.                    |
| $data->meta_data( $meta_key, $key ); | Map the meta data. One for each data set. |
| $data->taxonomy( $taxonomy, $key );  | Map the taxonomy. One for each taxonomy.  |

## Run a Migration
```bash
wp exodus migrate my-first-migration --file=test-migration.json
```
**Available Configurations:**

*  `<migration>` Name of the migration ( file name without .php )
* `--file` Path to a valid file. Supported file formats are xml, sql, and json
* `[--force]` Skip the check to see if post already exist
