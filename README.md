# tongs

Static site generator using Laravel Zero. Heavily based on [metalsmith](https:/metalsmith.io). WIP.

## example site

Source code for the example site is at [datashaman/tongs-example](https://github.com/datashaman/tongs-example).

The built files are deployed at [tongs-example.datashaman.com](http://tongs-example.datashaman.com).

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

Will create two collections in metadata at `$collections['posts']` and `$collections['other']`. If you also add `collection: featured` to posts' frontmatter, you can access the collection of those posts at `$collection['featured']`.

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

### sass

Render SASS files into CSS.

For example:

    {
        "plugins": {
            "sass": {
                "outputStyle": "compressed"
            }
        }
    }

will invoke the following command:

    node-sass --output-style=compressed $sourcePath

for any file ending in `.sass` and `.scss`. The source will be removed and replaced by a file with `.css` containing the rendered stylesheet.

Bear in mind that your `node_modules` folder will not exist in the cloud if you use a cloud-based _source_ disk. If your _source_ is local, no problem - use `npm` packages in the build.

### views

Render views and layouts to HTML.

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

More plugin packages:

* `feed` in [datashaman/tongs-feed](http://github.com/datashaman/tongs-feed)
* `metadata` in [datashaman/tongs-metadata](http://github.com/datashaman/tongs-metadata)
* `more` in [datashaman/tongs-more](http://github.com/datashaman/tongs-more)
* `permalinks` in [datashaman/tongs-permalinks](http://github.com/datashaman/tongs-permalinks)

To create your own plugins, look at the [plugin template](https://github.com/datashaman/tongs-plugin).

TEST
