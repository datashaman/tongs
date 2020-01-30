# tongs

Static site generator using Laravel Zero. Heavily based on [metalsmith](https:/metalsmith.io). WIP.

## example site

Source code for the example site is at [datashaman/tongs-example](https://github.com/datashaman/tongs-example).

The built files are deployed at [tongs-example.datashaman.com](http://tongs-example.datashaman.com).

## source and destination

The `source` and `destination` configs can be a string or a config array for a Laravel filesystem.

If it's a string, a _local_ filesystem is created with the root set to `directory`/`source` values, where `directory` will be the current working directory if you use the command-line app.

For example:

    {
        "source": "src",
        "destination": {
            "driver": "s3",
            "region": "eu-west-1",
            "bucket": "example.com"
        }
    }

Will build from `src` directory to the root of an S3 bucket named `example.com` using the default AWS credentials.


## plugins

The following plugins are provided by this package:

### collections

Add posts to `collections` metadata by adding a `collection` value in front matter or matching files with a `pattern` (it uses [fnmatch](https://php.net/functions/fnmatch).

For example:

    {
        "plugins": {
            "collections": {
                "posts": "posts/*.html",
                "other": {
                    "pattern": "other/*.html"
                }
            }
        }
    }

Will create two collections in metadata at `$collections['posts']` and `$collections['other']`. If you also add `collection: featured` to posts' frontmatter, you can access the collection of those posts at `$collections['featured']`.

### drafts

Mark posts as being a draft so they are not built.

For example:

    {
        "plugins": {
            "drafts": truu
        }
    }

will remove a post with `draft: true` in frontmatter.

### markdown

Render Markdown files into HTML.

For example:

    {
        "plugins": {
            "markdown": {
                "breaksEnabled": true,
                "strictMode": true
            }
        }
    }

will convert the content from Markdown to HTML (and rename files) using a [Parsedown](https://github.com/erusev/parsedown) parser. The configuration object is mangled to create config calls to the parser.

For example, the above will configure the parser with `setBreaksEnabled(true)` and `setStrictMode(true)`. Consult the [source code](https://github.com/erusev/parsedown/blob/master/Parsedown.php) for the options.

### views

Render views and layouts to HTML using Blade views.

For example:

    {
        "plugins": {
            "views": {
                "paths": [
                    "views"
                ],
                "compiled": ".cache"
            }
        }
    }

Put `view: post` frontmatter in a post and it will be rendered from `views/post.blade.php` with Blade. Local view variables are made up the post frontmatter and the global metadata values.

More plugin packages:

* `feed` in [datashaman/tongs-feed](http://github.com/datashaman/tongs-feed)
* `metadata` in [datashaman/tongs-metadata](http://github.com/datashaman/tongs-metadata)
* `more` in [datashaman/tongs-more](http://github.com/datashaman/tongs-more)
* `permalinks` in [datashaman/tongs-permalinks](http://github.com/datashaman/tongs-permalinks)
* `sass` in [datashaman/tongs-sass](http://github.com/datashaman/tongs-sass)

To create your own plugins, look at the [plugin template](https://github.com/datashaman/tongs-plugin).
