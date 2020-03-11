# Optimize WP SDK

The WP optimize package for [Karolína Vyskočilová's](https://kybernaut.cz) WordPress projects. Feel free to use it, if it helps.

## Install

Via Composer

``` bash
$ composer require vyskoczilova/optimize-wp-sdk
```

## Usage

``` php
// Load Optimize WP
$composer_autoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
	new Vyskoczilova\OptimizeWP();
}
```

## Contributing

Please see [CONTRIBUTING](https://github.com/vyskoczilova/optimize-wp-sdk/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Karolína Vyskočilová](https://github.com/vyskoczilova)
* [All Contributors](https://github.com/vyskoczilova/optimize-wp-sdk/contributors)
* [SVG-sanitizer library](https://github.com/darylldoyle/svg-sanitizer)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
