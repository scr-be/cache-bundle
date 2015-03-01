# Scribe / Cache Bundle

[![License](https://img.shields.io/packagist/l/scribe/cache-bundle.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/license)
[![RTD](https://readthedocs.org/projects/symfony-cache-bundle/badge/?version=latest&style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/docs)
[![API](https://img.shields.io/badge/api-latest-green.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/api)
[![Travis](https://img.shields.io/travis/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/ci) 
[![Scrutinizer](https://img.shields.io/scrutinizer/g/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/quality)
[![Coveralls](https://img.shields.io/coveralls/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/coverage)
[![Gemnasium](https://img.shields.io/gemnasium/scribenet/symfony-cache-bundle.svg?style=flat-square)](https://symfony-cache-bundle.docs.scribe.tools/deps)

*Scribe / Cache Bundle* is a simple and extensible caching abstraction layer with built-in support for APUu and Memcached.

## Our Standards

- *Auto-loading*: Conformance with the [PS4-4](http://www.php-fig.org/psr/psr-4/) 
  standard, allowing for seamless inclusion in any [composer](https://getcomposer.org/)
  project or any PSR-4 aware auto-loader implementation.
- *Continuous Integration*: Utilization of [Travis CI](https://symfony-cache-bundle.docs.scribe.tools/ci)
  to provide per-commit reports on the success or failure status of our builds.
- *Tests and Coverage*: Automated testing against our comprehensive 
  [PHPUnit](https://phpunit.de/) test suite, resulting code-coverage metrics
  dispatched to [Coveralls](https://symfony-cache-bundle.docs.scribe.tools/coverage).
- *Reports and Metrics*: Automated metrics pertaining to the defined code-styling
  guidelines, general code quality reports, and other statistics using 
  [Scrutinizer-CI](https://symfony-cache-bundle.docs.scribe.tools/quality).
- *API and Documentation*: Comprehensive [API reference](https://symfony-cache-bundle.docs.scribe.tools/api) 
  generated automatically using [Sami](https://github.com/fabpot/sami), as well 
  as [documentation and examples](https://symfony-cache-bundle.docs.scribe.tools/docs)
  compiled using [Read the Docs](https://readthedocs.org/).

## Installation

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