# Scribe / Cache Bundle

[![License](https://img.shields.io/packagist/l/scribe/cache-bundle.svg?style=flat-square)](https://github.com/scribenet/symfony-cache-bundle/blob/master/LICENSE.md)
[![Travis](https://img.shields.io/travis/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://travis-ci.org/scribenet/symfony-cache-bundle) 
[![Scrutinizer](https://img.shields.io/scrutinizer/g/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/scribenet/symfony-cache-bundle/)
[![Coveralls](https://img.shields.io/coveralls/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://coveralls.io/r/scribenet/symfony-cache-bundle)
[![Gemnasium](https://img.shields.io/gemnasium/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://gemnasium.com/scribenet/symfony-cache-bundle)
[![Packagist](https://img.shields.io/packagist/v/scribe/cache-bundle.svg?style=flat-square)](https://packagist.org/packages/scribe/cache-bundle)

*Scribe / Cache Bundle* is a simple and extensible caching abstraction layer with built-in support for APUu and Memcached.

## Include Bundle

To include this bundle in your project, simply add it as a dependency to your `composer.json` file within the `require` block.

```json
    "require" : {
        "scribe/cache-bundle" : "dev-master"
    }
```

After adding Scribe's Cache Bundle as a dependency, simply run composer to update your vendor files and composer auto-loader includes.

```bash
composer.phar update
```