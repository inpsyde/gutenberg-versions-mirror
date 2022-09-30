# Gutenberg Versions Mirror

## What

This package contains a mirror of multiple versions of Gutenberg plugin, in different sub-folders.
This allows  to contemporarily deploy multiple versions of Gutenberg to WordPress, and let different
themes (for example) require different versions.
    
    
## How this work

Every day, the Gutenberg repository is pulled in a GitHub Action to find new versions.

If new versions are found, they are saved in separate folders.

A utility class shipped allows external code to require Gutenberg in the desired version.


## How to use it

Require via Composer. Ensure Composer autoload is loaded. **From a MU plugin** require the version
you need in one of the following ways:

### Specific version

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadVersion('14.1.0');
```

### First found

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadVersion('14.1.0', '13.2.0');
```

### Composer requirement

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadMatching('^14');

$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadMatching('>=14.2.0 || ~13.2');
```


## URL filtering

Gutenberg internally calls `plugins_url()` with the assumption it is installed under plugins' folder.

However, when using this library, Gutenberg files are deeper in the plugin folder tree.

To fix broken URLs resulting from that, when loading a Gutenberg version the package also filters
`plugins_url()`.


## Unique loading

The package ensures that any attempt of loading Gutenberg multiple times (same of different version)
will fail.


## License

Copyright (c) 2023 Inpsyde GmbH Released under GPL v2.0 or later.

See [LICENSE](LICENSE) file for more information.
