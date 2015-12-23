# [scr-be/teavee-] object-cache-bundle

| CI Test Results | Code Review     | Test Coverage   |
|:---------------:|:---------------:|:---------------:|
| [![Travis](https://scr.be/teavee-object-cache-bundle/travis_shield)](https://scr.be/teavee-object-cache-bundle/travis) | [![Codacy](https://scr.be/teavee-object-cache-bundle/codacy_shield)](https://scr.be/teavee-object-cache-bundle/codacy) | [![Coveralls](https://scr.be/teavee-object-cache-bundle/coveralls_shield)](https://scr.be/teavee-object-cache-bundle/coveralls) |

## Overview

The `scr-be/teavee-object-cache-bundle` project provides a simple, robust, and extensible
caching abstraction layer with support for custom backends through Symfony compiler tag
registration and a central manager implementation. Implementations are provided for the
following handlers.

- Memcached (requires the *memcached* extension)
- Mock (provides an always-true, fake handler)

##### More Open Source!

This project is one of a [collection](https://src.run) of open-source, PHP
libraries and Symfony bundles maintained by [Rob Frawley 2nd](https://scr.be/rmf)
and [collaborators](https://github.com/scr-be/cache-bundle/graphs/contributors),
often for [Scribe Inc](https://scr.be/).

## Installation

Include within your project using [Composer](https://getcomposer.com).

```bash
composer require scr-be/teavee-object-cache-bundle
```

Enable the bundle by registering it with your application kernel.

```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Scribe\Teavee\ObjectCachingBundle\ScribeTeaveeObjectCacheBundle(),
        );
        // ...
    }
}
```

## Configuration

Available configuration can be referenced by using the Symfony console command
*app/console* after registering the bundle within the full-stack framework.
Additionally, this bundle provides a bare console that can be invoked as such.

```bash
bin/teavee-object-cache config:dump-reference scribe_teavee_object_cache
```

## Reference

##### API Docs

This package's API-documentation is available at [scr.be/teavee-object-cache-bundle/api](https://scr.be/teavee-object-cache-bundle/api),
(as well as linked below via the *Reference* badge found under the *Additional Links*
header). All API-reference is build against the *master* Git branch and updated
automatically on each Git push---api-reference for *specific releases* will
be provided once this package has matured.

> The entire API-reference website is auto-generated using a quick,
> reliable, and well-developed CLI tool called [Sami](https://github.com/FriendsOfPHP/Sami).
> It is rigerously and regularly tested through its use in large, complex projects,
> such as the [Symfony Full-Stack Framework](https://symfony.com/) 
> <see: [scr.be/go/api-ref-symfony](https://scr.be/go/api-ref-symfony)>, as well
> as its use in smaller projects such
> [Twig](http://twig.sensiolabs.org/)
> <see: [scr.be/go/api-ref-twig](https://scr.be/go/api-ref-twig)>.
> Reference Sami's [GitHub page](https://scr.be/go/sami-git) to learn how to use
> it with your own projects!

##### Examples/Tutorials

Currently, there is no *"human-written"* documentation---outside of this README.
Pending package stability and available resources, a
[RTD (Read the Docs)](http://readthedocs.org/) page will be published with
additional information and tutorials, including real use-cases within the Symfony
Framework.

## License

This project is licensed under the
[MIT License](https://github.com/scr-be/teavee-object-cache-bundle/blob/master/LICENSE.md), an
[FSF](https://en.wikipedia.org/wiki/Free_Software_Foundation)- and 
[OSI](https://en.wikipedia.org/wiki/Open_Source_Initiative)-approved and
[GPL-compatible](https://en.wikipedia.org/wiki/GNU_General_Public_License#Compatibility_and_multi-licensing)
permissive free software license. Review the
[LICENSE](https://github.com/scr-be/teavee-object-cache-bundle/blob/master/LICENSE.md)
file distributed with this source code for additional information.

## Additional Links

| Purpose       | Status        |
|--------------:|:--------------|
| *License*    | [![License](https://scr.be/teavee-object-cache-bundle/license_shield)](https://scr.be/teavee-object-cache-bundle/license) |
| *Reference*  | [![License](https://scr.be/teavee-object-cache-bundle/api_shield)](https://scr.be/teavee-object-cache-bundle/api) |
| *Release*    | [![Packagist](https://scr.be/teavee-object-cache-bundle/packagist_shield)](https://scr.be/teavee-object-cache-bundle/packagist) |
