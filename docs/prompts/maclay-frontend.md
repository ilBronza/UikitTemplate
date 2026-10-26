Lavora nel progetto reale /Volumes/Lavoro/Progetti/ProfessionalShow/macLay.
Integra la nuova parte frontend opt-in di UikitTemplate e Menu, preservando il
backend e tutte le modifiche locali. Leggi prima le istruzioni applicabili,
git status e il codice effettivo: le informazioni seguenti vanno riverificate.

Contesto verificato alla preparazione di questo prompt:
- Laravel 12; routes/web.php espone home pubblica con Route::view('/', 'home').
- /home rimanda a /admin; non cambiare implicitamente questa convenzione.
- /admin usa auth, accountmanager.active e can:manage-catalog.
- Route gestionali: admin.dashboard, ibProducts.products.*, ibCategory.categories.*.
- App\Providers\Helpers\MenuItems costruisce il menu gestionale: preservalo.
- Esiste un override resources/views/vendor/uikittemplate/app.blade.php: preservalo.
- Composer usa repository path per Menu e UikitTemplate con symlink=false:
  non presumere che vendor contenga già le nuove classi. Verifica le copie e
  sincronizza solo le dipendenze necessarie con il workflow del progetto,
  evitando aggiornamenti generali e modifiche manuali a vendor.

Leggi /Volumes/Lavoro/pacchetti/UikitTemplate/docs/frontend.md e le classi reali
src/Frontend nei due package. Registra esplicitamente
IlBronza\UikitTemplate\Frontend\FrontendServiceProvider nel punto previsto dal
progetto. Non cambiare provider o binding del menu originale.

Tutte le view pubbliche devono stare in resources/views/vendor/uikit/frontend/,
con riferimenti uikit::frontend.*; estendere uikit::frontend.layout. Prima verifica
collisioni del namespace uikit. Non esiste l'alias uikittemplate::frontend.
Migra la home conservando contenuti e design utili già presenti. Non inventare
un catalogo pubblico né rendere pubbliche route CRUD o entità prive di pubblicazione.

Passa sempre $frontendContext (FrontendContext) alla vista: locale del contenuto,
titolo, meta SEO, links ed eventuale page. Valida la lingua con le convenzioni
reali del sito. Non introdurre persistenza delle traduzioni e non confondere
lingua dell'interfaccia admin e lingua del contenuto.

Usa gli stack frontend.styles, frontend.head, frontend.scripts per asset pubblici
espliciti. Verifica il build esistente e mantieni gli asset gestionali invariati.
Il layout fornito non carica automaticamente UIkit, CSS o JS. Cura navigazione
responsive e accessibilità; qualsiasi override Blade pubblico resta nella cartella
frontend. Non includere head, fetcher, heartbeat o script CRUD gestionali.

Implementa il menu seguendo il prompt maclay-menu-frontend.md in questa directory.
Per i link tra aree usa route reali e policy. Vai al gestionale è visibile solo
agli autorizzati (verifica Gate manage-catalog e account attivo). Visita il sito
può usare la route home. Per Visualizza/Modifica questa pagina crea un resolver
esplicito solo dove esiste una corrispondenza reale; riusa getDefaultSeoUrl(locale)
se disponibile, verifica pubblicazione e lingua, fallback etichettato alla home.
Non dedurre URL dal CRUD. Anteprima bozze solo con route separata e policy.
Trasporta la lingua nei link, senza modalità globale o ultimo URL in sessione.
Verifica middleware web, guard e cookie per condividere login nelle due aree.

Verifica anonimo e autenticato sulla home, accesso admin invariato, rendering
senza dipendenze gestionali, due schede contemporanee e lingua dei link.
Aggiungi test mirati secondo gli strumenti del progetto. Non distribuire in
produzione. Concludi con file modificati, test ed eventuali limiti concreti.
