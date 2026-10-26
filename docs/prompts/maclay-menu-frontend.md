Nel progetto /Volumes/Lavoro/Progetti/ProfessionalShow/macLay realizza il menu
frontend usando le nuove API effettive di Menu e UikitTemplate. Leggi istruzioni,
modifiche locali e docs/frontend.md del package prima di intervenire.

Preserva integralmente App\Providers\Helpers\MenuItems, app('menu'), le chiavi
configurate e i provider gestionali. Usa app('menu.frontend') per il pubblico.
Non cambiare le route autenticate del package Menu. Nessun fetcher pubblico.

Crea App\Providers\Helpers\FrontendMenuItems implementando FrontendMenuProvider.
Nel solo config/menu.php aggiungi il ramo frontend senza riscrivere le altre
chiavi. Non registrare la stessa classe sia in providers sia in projectProvider.
Verifica prima la disponibilità delle nuove classi nella copia vendor di macLay.
Registra FrontendServiceProvider solo se non è già registrato.

Esempio di configurazione da adattare al file reale:

```php
'frontend' => [
    'label' => 'Navigazione principale', // Etichetta accessibile del nav.
    'providers' => [], // Altri provider pubblici che implementano il contratto.
    'projectProvider' => App\Providers\Helpers\FrontendMenuItems::class,
],
```

Esempio minimo basato sulla route home esistente:

```php
<?php
namespace App\Providers\Helpers;

use IlBronza\Menu\Frontend\FrontendMenu;
use IlBronza\Menu\Frontend\FrontendMenuProvider;

final class FrontendMenuItems implements FrontendMenuProvider
{
    public function build(FrontendMenu $menu, object $context): void
    {
        // Il contesto ricevuto dal layout è FrontendContext.
        // La route home attuale non richiede locale: non inventare parametri.
        // Se il routing diventa localizzato, passa qui i parametri reali.
        $menu->add(
            label: 'Home', // Localizzare secondo le convenzioni reali del sito.
            url: route('home'),
            active: request()->routeIs('home'),
        );

        // ESEMPIO OPZIONALE: attivare solo dopo aver creato/verificato le route.
        // Nessuna di queste route va considerata già presente in macLay.
        // $menu->add('Catalogo', route('public.catalog', [
        //     'locale' => $context->locale,
        // ]), children: [
        //     [
        //         'label' => 'Categoria pubblicata',
        //         'url' => $publicUrlResolver->category($category, $context->locale),
        //         'active' => false,
        //         'children' => [],
        //     ],
        // ]);
        // Risolvere categorie, pubblicazione e URL tramite servizi reali;
        // escludere le voci senza URL pubblico. Non derivare URL dai CRUD.
    }
}
```

Il backend link può essere fornito in FrontendContext::links per evitare di
mescolare la navigazione pubblica e le azioni tra aree. Esempio nel controller,
da adattare alle verifiche reali di account attivo e policy:

```php
// Gate manage-catalog è presente nel routing admin: verificare anche
// accountmanager.active prima di offrire una destinazione effettivamente accessibile.
$links = [];
// if ($canAccessAdmin) {
//     $links[] = ['url' => route('admin.dashboard'), 'label' => 'Vai al gestionale'];
// }
// $frontendContext = new FrontendContext(locale: $contentLocale, links: $links);
```

Non chiamare add() fuori dal metodo build del provider: l'API lo impedisce.
Le voci figlie sono array label/url/children/active; non sono Buttons gestionali.
La navbar pubblica e il suo stato sono indipendenti. L'attuale template ricorsivo
è server-side; per mobile/offcanvas crea eventuali override esclusivamente sotto
resources/views/vendor/uikit/frontend/menu/, con riferimenti uikit::frontend.*,
ID propri e asset UIkit espliciti. Non riutilizzare template menu gestionali.

Verifica: menu visibile ad anonimi e autenticati, nessun caricamento dei provider
legacy dal frontend, menu gestionale invariato, link reali, escaping, lingua,
voce attiva e navigazione mobile. Non presumere che i placeholder commentati
siano requisiti o funzionalità esistenti. Riporta modifiche e test.
