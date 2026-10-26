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
    $menu->add('Home', route('public.home', ['locale' => $context->locale]));
}
```

I provider costruiscono voci tramite `add(label, url, children, active)`; i figli
sono array con chiavi label, url, children opzionale e active opzionale.
Il template esegue escaping di etichette e attributi; gli URL devono essere
prodotti da route o resolver fidati. Non passare URL arbitrari forniti dagli utenti.
Il menu non usa cache, fetcher o endpoint gestionali. Il caricamento è idempotente
per lo stesso oggetto contesto, e viene ricostruito per un nuovo contesto.

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
l'autorizzazione. Il package non registra route pubbliche né modifica middleware.
Per condividere login usare sessione web e guard compatibili, cookie con path e
dominio appropriati; deployment distinti richiedono verifica di storage e chiavi.
