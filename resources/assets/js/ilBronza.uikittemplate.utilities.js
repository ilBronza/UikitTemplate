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

    window.__ibBindEscCloseParentOverlays = window.__ibBindEscCloseParentOverlays || function ()
    {
        if (window.__ibEscCloseParentOverlaysBound)
            return;

        window.__ibEscCloseParentOverlaysBound = true;

        // Escape inside an iframe never reaches the parent, so UIkit cannot close
        // the overlay. Forward it when we are framed.
        jQuery(document).on('keydown', function (e) {
            if (e.key !== 'Escape')
                return;

            if (window.self === window.top)
                return;

            e.preventDefault();

            const parent = window.parent;

            if (typeof parent.closeLightbox === 'function')
                parent.closeLightbox();
            else {
                const openLightbox = parent.document.querySelector('.uk-lightbox.uk-open');

                if (openLightbox) {
                    if (! parent.UIkit)
                        throw new Error('Cannot close parent lightbox: UIkit is not available on parent window');

                    parent.UIkit.lightbox(openLightbox).hide();
                }
            }

            if (typeof parent.closeModal === 'function')
                parent.closeModal();
            else {
                const openModal = parent.document.querySelector('.uk-modal.uk-open');

                if (openModal) {
                    if (! parent.UIkit)
                        throw new Error('Cannot close parent modal: UIkit is not available on parent window');

                    parent.UIkit.modal(openModal).hide();
                }
            }

            if (typeof parent.__ibForceUnlockBodyScroll === 'function')
                parent.__ibForceUnlockBodyScroll();
        });
    };

    window.__ibBindEscCloseParentOverlays();

});
