# UikitTemplate

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require ilbronza/uikittemplate
```


add this lines to package.json dependencies list

``` bash
"uikit": "^3.6.21",
"uikit-icons": "^0.5.0",
"@fortawesome/fontawesome-free": "^5.15.3",
```

run npm install from terminal

``` bash
npm install
```

add these line to resources/js/app.js to include the required modules
``` bash
require('./ilBronza.ajaxFetchers.js');
```


publish the package assets

``` bash
php artisan vendor:publish --force --tag "uikittemplate.assets"
```


compile the file with laravel-mix

``` bash
npm run development
```



## Usage

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [author name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ilbronza/uikittemplate.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ilbronza/uikittemplate.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ilbronza/uikittemplate/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/ilbronza/uikittemplate
[link-downloads]: https://packagist.org/packages/ilbronza/uikittemplate
[link-travis]: https://travis-ci.org/ilbronza/uikittemplate
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/ilbronza
[link-contributors]: ../../contributors
