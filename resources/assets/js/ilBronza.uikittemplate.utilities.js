jQuery(document).ready(function ($) {

    // Only iframe lightboxes (edit/forms). Plain image lightboxes must not
    // mark the parent fetcher for reload on close.
    $('body').on('click', '*[uk-lightbox] a[data-type="iframe"]', function () {

        window.fetcherToRefresh = $(this).parents('.ibfetchercontainer');
    });

    UIkit.util.on(document, 'hide', '.uk-lightbox', function (e) {
        if (window.fetcherToRefresh)
            $(window.fetcherToRefresh).find('a.refresh').click();

    });

});
