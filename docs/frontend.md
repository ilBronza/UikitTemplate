# Frontend opt-in

Registrare esplicitamente `IlBronza\UikitTemplate\Frontend\FrontendServiceProvider`
nei provider dell'applicazione. Non è auto-discovered: senza registrazione il
comportamento esistente non cambia. Richiede la versione di Menu con `FrontendMenu`.

Le pagine pubbliche estendono `uikit::frontend.layout` e ricevono obbligatoriamente
`$frontendContext`, istanza di `FrontendContext(locale: 'en', title: 'Page')`.
Tutte le view pubbliche, inclusi override e pagine applicative, devono stare sotto
`frontend/` (override: `resources/views/vendor/uikit/frontend/`). Non viene aggiunto
un alias `uikittemplate::frontend`: l'entry point rispetta il prefisso `uikit::frontend`.
Verificare prima che l'applicazione non registri già un namespace `uikit`.

Asset espliciti tramite gli stack `frontend.styles`, `frontend.head` e
`frontend.scripts`. Nessun asset gestionale viene incluso automaticamente.
Gli stack possono caricare UIkit secondo il build dell'applicazione.

Nel solo config/menu.php dell'applicazione si può aggiungere:

```php
'frontend' => [
    'label' => 'Navigazione pubblica',
    'providers' => [],
    'projectProvider' => App\Providers\Helpers\FrontendMenuItems::class,
],
```

Il ramo è opzionale e non altera le configurazioni gestionali. La classe
FrontendMenuItems, se presente, deve implementare
`IlBronza\Menu\Frontend\FrontendMenuProvider`:

```php
public function build(FrontendMenu $menu, object $context): void
{
    // In questo esempio il controller passa il modello della pagina nel contesto.
    if ($url = $context->page->getDefaultSeoUrl($menu->locale())) {
        $menu->addTranslated('navigation.page', $url);
    }
}
```

I provider costruiscono voci tramite `add(label, url, children, active)`; i figli
sono array con chiavi label, url, children opzionale e active opzionale.
Il template esegue escaping di etichette e attributi; gli URL devono essere
prodotti da route o resolver fidati. Non passare URL arbitrari forniti dagli utenti.
Il menu non usa cache, fetcher o endpoint gestionali. Il caricamento è idempotente
per lo stesso oggetto contesto, e viene ricostruito per un nuovo contesto o
quando si cambia la configurazione della lingua tramite `withLanguages()`.

## Lingua del menu

In `config/uikittemplate.php` ogni progetto configura:

```php
'availableLanguages' => ['it', 'en'],
```

La lingua iniziale è `app.locale`; il fallback delle traduzioni è
`app.fallback_locale`. La lingua predefinita deve essere compresa nell'elenco.
Una scelta salvata che non compare più nell'elenco viene ignorata.

UikitTemplate fornisce a `FrontendMenu` l'elenco, la lingua scelta e l'URL
dell'azione. Gli URL localizzati provengono dai modelli o dagli oggetti del
progetto: il menu li riceve già completi, senza aggiungere, rimuovere o dedurre
prefissi. Per una voce, il provider chiama il getter pubblico dell'oggetto
passandogli `$menu->locale()`, poi passa il risultato a `add()` o `addTranslated()`.

Per il selettore della pagina corrente, il controller raccoglie le destinazioni
del modello e le passa in `FrontendContext::languageUrls`:

```php
$languageUrls = [];
foreach (config('uikittemplate.availableLanguages') as $locale) {
    if ($url = $page->getDefaultSeoUrl($locale)) {
        $languageUrls[$locale] = $url;
    }
}

$frontendContext = new FrontendContext(
    locale: $contentLocale, // Ricavato dal routing del progetto.
    page: $page,
    languageUrls: $languageUrls,
);
```

`getDefaultSeoUrl($locale)` è un esempio basato sul punto di estensione di
`HasSeo`: il modello deve implementarlo, perché la versione di base restituisce
`null`. Si può usare il getter pubblico di qualsiasi altro oggetto. Il getter
è responsabile delle route effettivamente registrate, dei prefissi e degli URL
disponibili; per esempio `/news/123`, `/en/news/123`, `/es/news/123`. Per la lingua
predefinita sarà il getter a restituire l'URL senza prefisso. Non è previsto
un helper che trasformi automaticamente gli URL.

Menu prepara il selettore con le sigle IT/EN, nell'ordine configurato, ed
evidenzia la lingua corrente. Omette le lingue senza URL e quelle non abilitate.
Con meno di due destinazioni disponibili il selettore viene omesso. L'header
standard lo mostra dopo le voci di navigazione; un contesto senza `languageUrls`
continua a rendere il menu, ma non mostra il selettore.

Il cambio invia un form POST alla route `uikittemplate.frontend.menu-locale`
(`/uikittemplate/frontend/menu-locale`), protetta dal middleware `web` e dal
token CSRF. È disponibile anche agli ospiti. Il controller accetta solo le
lingue configurate, salva la scelta in `uikittemplate.menu_locale` nella
sessione e risponde con un redirect 303 all'URL della lingua selezionata fornito
dal modello. Accetta percorsi locali o URL assoluti della stessa origine,
conservando query string e frammenti forniti dall'oggetto; destinazioni esterne,
con credenziali o origine diversa riportano a `/`.

Il package non modifica `app()->getLocale()`, il locale del traduttore globale
o `$frontendContext->locale`. Dopo il redirect, il routing del progetto determina
la lingua della nuova pagina e il suo contesto. Non vengono aggiunti middleware
per applicare la lingua ad altre richieste, comprese quelle Ajax. Il menu indica
la propria lingua nell'attributo `lang` della navigazione.

Per tradurre una voce, definire le chiavi nei normali file lingua Laravel
(per esempio `lang/it/navigation.php` e `lang/en/navigation.php`) e usare
`addTranslated()`. Per i figli o per interpolare valori è disponibile
`translate()`, che passa esplicitamente la lingua del menu al traduttore:

```php
$menu->addTranslated('navigation.product', $product->getDefaultSeoUrl($menu->locale()));
$menu->addTranslated('navigation.results', $results->getPublicUrl($menu->locale()), replace: ['count' => 3]);
$menu->add('Acme', $home->getPublicUrl($menu->locale())); // Etichetta letterale, URL già localizzato.
```

Le traduzioni seguono il fallback nativo Laravel e vengono escapate nelle view.
Le etichette già prodotte con `__()` prima della chiamata a `add()` continuano
a usare la lingua applicativa: convertirle ai metodi sopra per usare la scelta
del menu. `$menu->locale()` espone la lingua del menu, mentre il contesto passato
ai provider conserva la lingua della pagina.

Un progetto che sovrascrive `uikit::frontend.header` può inserire il selettore
nella posizione desiderata:

```blade
@php($frontendMenu = app('menu.frontend')->build($frontendContext))
@include('uikit::frontend.menu.language-switcher', ['frontendMenu' => $frontendMenu])
```

Il partial usa anche `$frontendContext->languageUrls`. È personalizzabile in
`resources/views/vendor/uikit/frontend/menu/language-switcher.blade.php`.
Non richiede JavaScript. Gli header già personalizzati devono includerlo
esplicitamente per mostrare il selettore.

## Contesto della pagina

Il contesto separa la lingua del contenuto dalla locale dell'interfaccia gestionale.
`meta` accetta description, robots, canonical, og:* e twitter:*.
`links` accetta array ['url' => ..., 'label' => ...] per i collegamenti tra aree.
Il controller deve produrli con un resolver applicativo esplicito e policy:
URL pubblico solo per contenuti pubblicati, modifica solo con autorizzazione,
fallback alla home dell'area se manca una corrispondenza. Il resolver può riusare
SeoUrlResolver::publicUrl per modelli con getDefaultSeoUrl(locale); non dedurre
URL pubblici dai CRUD e non usare canonical come destinazione implicita.

Trasportare la lingua del contenuto nei link di modifica e pubblici, senza usare
un ultimo URL o una modalità in sessione. Le anteprime delle bozze richiedono route
e policy dedicate, URL separati e meta robots noindex. Una firma non sostituisce
l'autorizzazione. Il provider frontend registra solo la route di cambio lingua
del menu; non modifica i gruppi middleware e non crea route per i contenuti.
Il provider principale applica invece la modalità di manutenzione `onlyAdmin`
alle route del gruppo `web`, incluse quelle pubbliche (vedi il README).
Per condividere login usare sessione web e guard compatibili, cookie con path e
dominio appropriati; deployment distinti richiedono verifica di storage e chiavi.
