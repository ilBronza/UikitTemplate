jQuery(document).ready(function ($) {

    $('body').on('click', '*[uk-lightbox] a', function () {

        window.fetcherToRefresh = $(this).parents('.ibfetchercontainer');
    });

    UIkit.util.on(document, 'hide', '.uk-lightbox', function (e) {
        if (window.fetcherToRefresh)
            $(window.fetcherToRefresh).find('a.refresh').click();

    });

});
