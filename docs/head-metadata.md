# Metadati del documento

I layout gestionale e frontend usano lo stesso partial
`uikittemplate-head::metadata` per favicon, touch icon, theme color, canonical,
alternate di lingua, Open Graph e JSON-LD. Gli override Blade di
`uikit::frontend.layout` / `uikit::frontend.head` devono continuare a includere
quel partial: è l'unico punto che importa automaticamente `getMetaTags()` da
`FrontendContext::page`.

## Configurazione

```php
// config/uikittemplate.php
'availableLanguages' => ['it', 'en'],
'head' => [
    'favicon' => [
        ['path' => '/favicon.svg', 'type' => 'image/svg+xml'],
        ['path' => '/favicon-32x32.png', 'type' => 'image/png', 'sizes' => '32x32'],
        ['path' => '/favicon.ico', 'type' => 'image/x-icon', 'sizes' => 'any'],
    ],
    // oppure una sola icona: 'favicon' => ['path' => '/favicon.svg', 'type' => 'image/svg+xml'],
    'touchIcon' => ['path' => '/apple-touch-icon.png', 'sizes' => '180x180'],
    'themeColor' => '#ffffff',
    'canonical' => null,
    'hrefLinks' => null,
    'xDefaultLocale' => null, // null → app.base_locale → app.locale
],
```

Le icone accettano un percorso, un oggetto `{path, type?, sizes?}` oppure una lista
di oggetti per SVG + fallback PNG/ICO. Il tipo viene ricavato dall'estensione se
non specificato. `head.favicon = null` mantiene la configurazione precedente
`uikittemplate.favicon.path`, con fallback su `/favicon.png`. Touch icon e theme
color vengono omessi se non configurati.

Senza canonical esplicito si usa l'URL assoluto della richiesta corrente,
senza query string. Identificativi nel percorso, protocollo, porta e slash finale
restano invariati. Per esempio:

```text
Richiesta: https://example.com/en/catalogo/42?pagina=2&utm_source=mail
Canonical: https://example.com/en/catalogo/42
```

Senza `hrefLinks` espliciti, con almeno due lingue distinte configurate si
genera un URL per ciascuna lingua, prefissando il percorso del canonical:

```text
it: https://example.com/it/catalogo/42
en: https://example.com/en/catalogo/42
x-default: https://example.com/it/catalogo/42
```

Il prefisso lingua già presente viene sostituito quando coincide con un'intera
prima componente del percorso. Non si generano quindi `/it/en/catalogo/42` né
`/en/en/catalogo/42`. Anche la lingua base riceve il prefisso. L'eventuale percorso
di installazione dell'applicazione rimane prima della lingua.

`x-default` viene aggiunto automaticamente quando gli alternate includono la
lingua di `uikittemplate.head.xDefaultLocale`, oppure in assenza di quella
chiave `app.base_locale` e poi `app.locale`. Se quella lingua non è tra gli
alternate, si usa la prima lingua di `availableLanguages` presente nella mappa.
Un `x-default` già fornito (config, `languageUrls`, `setHrefLinks` o
`setHrefLink`) non viene sovrascritto; `setHrefLink('x-default', null)` lo
omite del tutto.

Per slug differenti tra lingue, pagine prive di alcune traduzioni o una lingua
base senza prefisso, fornire gli URL espliciti. `hrefLinks = []` disattiva i link
alternativi; `null` attiva il fallback automatico. Un canonical esplicito conserva
anche l'eventuale query string e questa viene conservata negli alternate generati.

## Controller

```php
$head = app('uikittemplate')->head();
// Disponibile anche con il solo FrontendServiceProvider:
// $head = app(\IlBronza\UikitTemplate\Helpers\HeadMetadata::class);

$head->setFavicon('/favicon.svg')
    ->setTouchIcon(['path' => '/touch.png', 'sizes' => '180x180'])
    ->setThemeColor('#ffffff')
    ->setCanonical($canonicalUrl)
    ->setHrefLinks([
        'it' => 'https://example.com/articolo',
        'en' => 'https://example.com/en/article',
        'x-default' => 'https://example.com/articolo',
    ]);
```

I valori risolti sono consultabili tramite i getter dell'helper. `setFavicon(null)`,
`setTouchIcon(null)` e `setThemeColor(null)` omettono il rispettivo tag.
`setCanonical(null)` e `setHrefLinks(null)` usano i fallback automatici.
Gli URL relativi per canonical e alternate espliciti vengono resi assoluti.

### Override indipendenti

Tutti i setter restituiscono l'helper e possono essere concatenati:

```php
$head->setTitle('Titolo della pagina')
    ->setDescription('Descrizione specifica')
    ->setRobots('noindex, follow')
    ->setOpenGraphTitle('Titolo per la condivisione')
    ->setOpenGraphImage('https://example.com/share.jpg')
    ->setTwitterCard('summary_large_image')
    ->setTwitterTitle('Titolo Twitter')
    ->setHrefLink('en', 'https://example.com/en/custom-slug');
```

| Dati | Setter dedicati |
| --- | --- |
| Documento | `setTitle`, `setDescription`, `setRobots` |
| Icone e colore | `setFavicon`, `setTouchIcon`, `setThemeColor` |
| URL | `setCanonical`, `setHrefLinks`, `setHrefLink($locale, $url)` |
| Open Graph | `setOpenGraphTitle`, `setOpenGraphDescription`, `setOpenGraphUrl`, `setOpenGraphType`, `setOpenGraphImage`, `setOpenGraphImageAlt`, `setOpenGraphLocale`, `setOpenGraphSiteName` |
| Twitter | `setTwitterCard`, `setTwitterTitle`, `setTwitterDescription`, `setTwitterImage`, `setTwitterImageAlt`, `setTwitterSite`, `setTwitterCreator` |
| JSON-LD | `setJsonLd`, `addJsonLd`, `setJsonLdProperty($property, $value, $objectIndex = 0)` |

Ogni setter singolo modifica soltanto il proprio valore: `setDescription()` non
modifica `og:description` o `twitter:description`; le altre proprietà continuano
a provenire dal modello, dal contesto o dagli override già impostati. Non viene
salvato nulla sul modello. Per omettere titolo, description, robots o una
proprietà social, passarle `null`: il valore ereditato non riappare.

`setHrefLink()` modifica una sola lingua, conservando le altre. Il fallback
viene risolto al rendering, quindi considera anche un canonical o un contesto
impostato successivamente. `setHrefLink('en', null)` rimuove solo l'alternate
inglese. Una chiamata a `setHrefLinks()` sostituisce la mappa e azzera gli
override per lingua impostati in precedenza.

Per proprietà social aggiuntive usare
`setOpenGraphProperty('article:author', $url)` o
`setTwitterProperty('creator', '@autore')`. Le proprietà Open Graph senza `:`
ricevono il prefisso `og:`; per attributi come `og:image:width` usare la chiave
completa. I getter `getOpenGraphProperty()` e `getTwitterProperty()` leggono i
valori risolti; sono disponibili anche `getDescription()`, `getRobots()` e
`getHrefLink($locale)`.

I setter di gruppo `setMeta()`, `setOpenGraph()` e `setJsonLd()` sostituiscono i
rispettivi override di gruppo: per correzioni puntuali chiamare dopo i setter
singoli. La registrazione del modello con `setSeoModel()` non azzera gli override.

La precedenza è controller, poi `FrontendContext::meta` e
`FrontendContext::languageUrls`, poi i metadati del modello, poi configurazione
e fallback automatici. `setTitle()` imposta il titolo del documento senza
modificare l'H1; in assenza di titolo SEO si usa il titolo del layout.
Il contesto viene applicato su una copia dell'helper, senza mutare lo stato dei
controller. Lo stato dei metadati è scoped alla richiesta e non viene conservato
nel singleton di UikitTemplate.

I fallback automatici riguardano soltanto i metadati del documento. Il selettore
di lingua del menu continua a usare gli URL espliciti di `languageUrls`.

## Open Graph e JSON-LD

Questi dati vengono forniti soltanto dai controller, senza default in config:

```php
$head->setOpenGraph([
    'og:title' => $page->title,
    'og:type' => 'article',
    'og:url' => $canonicalUrl,
    'article:tag' => ['Design', 'Architecture'],
    'og:image' => [
        ['url' => $imageUrl, 'width' => 1200, 'height' => 630, 'alt' => 'Copertina'],
    ],
]);

$head->addJsonLd([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $page->title,
]);
```

`setOpenGraph()` accetta anche chiavi brevi come `title`, convertite in `og:title`.
Le proprietà ripetute accettano liste di valori. Per immagini multiple, ciascun
URL viene emesso insieme ai propri attributi. `setJsonLd([$article, $breadcrumbs])`
sostituisce tutti gli oggetti, mentre `addJsonLd()` ne aggiunge uno.

`setJsonLdProperty('headline', 'Titolo aggiornato')` aggiorna una sola proprietà
del primo oggetto, preservando le altre proprietà e gli altri oggetti. Il terzo
argomento seleziona un oggetto esistente tramite indice da zero. Se la lista è
vuota, l'indice zero permette di costruire il primo oggetto un campo alla volta;
gli altri indici devono già esistere. `null` rimuove la proprietà. Le chiavi sono
letterali e di primo livello; per un oggetto annidato passare l'array completo
come valore. `getJsonLdProperty()` legge la proprietà senza modificarla.

Il JSON viene serializzato come JSON valido con escaping adatto al tag script;
i valori HTML vengono escapati da Blade. I microdata restano negli elementi HTML
dei componenti che descrivono, mentre JSON-LD viene inserito nel head.

Per importare i metadati esistenti di CRUD:

```php
$head->setSeoModel($page, $locale);
// Oppure un array esplicito, con precedenza sui default del modello:
$head->setMeta($page->getMetaTags($locale));
```

`setMeta()` e `FrontendContext::meta` continuano a supportare description, robots,
title, canonical, Open Graph e Twitter card. Se `FrontendContext::page` espone
`getMetaTags()`, i suoi dati vengono importati automaticamente nella lingua del
contesto. Non è necessario passarli anche in `meta`. La lingua del traduttore
applicativo non viene modificata.

`setSeoModel()` può essere chiamato prima o dopo i setter del controller: questi
ultimi mantengono la precedenza. `setSeoModel(null)` disattiva la lettura automatica
di `FrontendContext::page`. Un modello che non espone `getMetaTags()` non è una
sorgente SEO automatica.

Il modello viene letto una volta per rendering del head. `HasSeo` riusa la
relazione `seo` se già caricata; per liste è consigliato `with('seo')`.
Canonical e alternate automatici restano responsabilità di UikitTemplate:
`SeoUrlResolver` in CRUD non dipende dall'URL della richiesta, così lo stesso
modello resta utilizzabile nella sitemap.

Quando `HasSeo` ricava `og:url` dal canonical, il renderer li mantiene coerenti
anche dopo un override del canonical. Un `og:url` diverso e dichiarato
esplicitamente viene mantenuto. JSON-LD continua a essere fornito dagli helper.

Titolo e metadati vengono emessi una sola volta nel documento, anche se il
partial compatibile `crud::seo.meta` viene incluso insieme al renderer comune.
Con i layout UikitTemplate registrare il modello prima del rendering del head;
non occorre aggiungere altri include SEO.
