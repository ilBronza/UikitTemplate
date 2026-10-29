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

### Manutenzione con accesso amministratori

In `config/uikittemplate.php` impostare `'onlyAdmin' => true`, oppure aggiungere
`APP_UIKIT_ONLY_ADMIN=true` al `.env` quando la configurazione usa il valore del package.
Il valore predefinito è `false`. Dopo una modifica rigenerare l'eventuale cache
della configurazione dell'applicazione.

Il provider registra automaticamente il middleware `OnlyAdmin` nel gruppo `web`.
Durante la manutenzione solo gli utenti con ruolo `administrator` o `superadmin`
possono proseguire. Ospiti e altri utenti ricevono direttamente la view
`uikittemplate::maintenance` con stato HTTP **503**, `Retry-After: 60` e cache
disabilitata. Il controllo avviene dopo l'apertura della sessione e prima di
`auth`, del binding dei modelli e dei controller, anche per le richieste di scrittura.
Le richieste che accettano JSON ricevono un messaggio JSON con lo stesso stato.
Le route fuori dal gruppo `web` non sono interessate.

`onlyAdminExcept` contiene i percorsi o nomi di route di login, logout,
autenticazione a due fattori, LDAP e GitHub che devono restare raggiungibili
per consentire l'accesso agli amministratori. Per URL di autenticazione
personalizzati aggiungere i relativi percorsi, inclusi POST e callback.
Le eccezioni accettano `*`: mantenere l'elenco limitato alle route necessarie.

La pagina è autonoma, senza menu o asset del gestionale. Per personalizzarla
creare `resources/views/vendor/uikittemplate/maintenance.blade.php`
nell'applicazione.
Mostra il logo configurato in `uikittemplate.logo` (`APP_LOGO`), con fallback
su `menu.logo.path` e `mail.logo.path`. Se il logo non è configurato, viene omesso.

### Catalogo dei widget

Il [catalogo illustrato YOOtheme](docs/yootheme-catalogo/README.md) raccoglie esempi, varianti strutturali e riferimenti ai temi per progettare gli helper del package. Include un [PDF consultabile offline](output/pdf/catalogo-widget-yootheme.pdf) e un [confronto fra i caroselli di Design Escapes e Vision](docs/yootheme-catalogo/confronto-caroselli.md).

### Elementi collassabili

`CollapsibleElementTrait` fornisce stato, identificatore HTML e selettori per rendere collassabile qualsiasi oggetto. Il trait non impone la struttura HTML dell'elemento che lo usa.

```php
use IlBronza\UikitTemplate\Traits\CollapsibleElementTrait;

class Panel
{
    use CollapsibleElementTrait;
}

$panel->setCollapsible()
    ->setCollapsedInitially();
```

Il pulsante UIkit standard è un componente Blade anonimo:

```blade
<x-uikittemplate::collapse-toggle
    :element="$panel"
    label="Dettagli"
    aria-label="Apri o chiudi i dettagli"
/>

<div
    id="{{ $panel->getCollapseTargetId() }}"
    class="{{ $panel->getCollapseTargetHtmlClassesString() }}"
>
    ...
</div>
```

Gli attributi Blade aggiuntivi vengono applicati al pulsante. Sono inoltre disponibili le prop `expanded-icon` e `collapsed-icon`. Per usare un id deterministico chiamare `setCollapseTargetId()`; sono accettati id HTML composti da lettere, numeri, `_` e `-`, con una lettera iniziale.

Il componente mantiene automaticamente lo stato aperto/chiuso nella `sessionStorage` del browser usando `getCollapseStateKey()`. Gli oggetti che usano il trait possono impostare manualmente una chiave con `setCollapseStateKey()`; in assenza di override il trait usa il suo id di target.

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
