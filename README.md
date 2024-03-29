# Gutenberg Versions Mirror

## What is this?

This package contains a mirror of multiple versions of Gutenberg plugin, in different sub-folders.
This allows  to contemporarily deploy multiple versions of Gutenberg to WordPress, and let different
themes (for example) require different versions.
    
    
## How does it work?

The wp.org API is called regularly in a GitHub Action to find new Gutenberg versions.

If new versions are found, they are saved, each in a separate folder.

A utility class shipped with the package allows external code to require Gutenberg in the desired version.


## How to use this package

- Require via Composer.
- Ensure Composer autoload is loaded.
-  **From a MU plugin** require the version you need in one of the following ways

### Require a specific version

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadVersion('14.1.0');
```

### Require the first found among a list of possible (accepted) versions

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadVersion('14.1.0', '13.2.0');
```

### Require via Semver requirement

```php
/** @var string|null $loadedVersion The exact version loaded or null on failure */
$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadMatching('^14');

$loadedVersion = Inpsyde\GutenbergVersions\Loader::loadMatching('>=14.2.0 || ~13.2');
```


## URL filtering

Gutenberg internally calls `plugins_url()` with the assumption it is installed under the plugins' folder.

However, when using this library, Gutenberg files are deeper in the plugin folder tree.

To fix broken URLs resulting from that, when loading a Gutenberg version, the package also filters
`plugins_url()`.


## Unique loading

The package ensures that any attempt of loading Gutenberg multiple times (same of different version)
will fail.


## License

This repository is a free software, and is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See [LICENSE](./LICENSE) for complete license.
