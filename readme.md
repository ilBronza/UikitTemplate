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

### Footer event log

Every page extending `uikittemplate::app` includes a small, non-persistent event console in the bottom-right corner. It is independent from the existing UIkit notifications (`addSuccessNotification`, `addDangerNotification`, and so on), which continue to work unchanged.

Set `APP_UIKIT_SHOW_BROADCAST_UPDATES=false` to hide the console and prevent its browser hooks and listeners from being registered. The setting defaults to `true`.

Send a short, safe-to-display event through the global API:

```js
window.footerEventLog.push({
    message: 'Order 42 updated',
    level: 'success', // info | success | warning | error
    source: 'CRUD',   // optional
    data: {}          // optional; never displayed or retained by the console
});
```

Or emit the equivalent browser event, useful for decoupled integrations:

```js
window.dispatchEvent(new CustomEvent('uikittemplate:footer-log', {
    detail: {
        message: 'Data table reloaded',
        level: 'success',
        source: 'Datatables'
    }
}));
```

Each entry has a timestamp, level, message and optional source. The short live list disappears after 5.5 seconds by default; the current page's in-memory history can be shown with `footerEventLog.expand()`. No event is persisted across page loads. `footerEventLog.clearVisible()` and `footerEventLog.collapse()` are also available when needed.

Set options before the footer component is rendered (for example in a script included by the page `<head>`):

```js
window.footerEventLogOptions = {
    timeout: 7000,
    maxVisible: 3,
    maxHistory: 30
};
```

The component uses no application-specific dependency. Its styles are scoped under `#uikittemplate-footer-event-log` and expose CSS custom properties such as `--uikittemplate-footer-log-background`, `--uikittemplate-footer-log-right` and `--uikittemplate-footer-log-bottom`, so host applications can reposition or restyle it without replacing the component. Messages and sources are inserted exclusively with `textContent`; never send secrets in `message`, `source`, or `data`.

For compatibility with the optional Datatables CRUD-broadcast integration, the component also defines `window.addFooterLog(message, context)` only when the host application has not already defined it. The adapter forwards the message, `context.level`, and `context.source` to `footerEventLog.push()`; the rest of `context` is treated as non-rendered data.

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
