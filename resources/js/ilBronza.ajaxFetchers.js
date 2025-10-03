jQuery(document).ready(function($)
{
	function ajaxFetcher(element, target, append = false)
	{
        fetch($(element).data('url'))
            .then(response => response.text())
            .then(html => {

                if(append)
                    $(target).append(html);

                else
                    $(target).html(html);
            });
	}

    $('.ajaxfetcher').each(function()
    {
        ajaxFetcher(this, this);
    });

    $('.clickajaxfetcher').click(function()
    {
        var target = $(this).data('target');
        var append = $(this).data('append');

        if(append !== true)
            append = false;

        ajaxFetcher(this, target, append);
    })

    $('body').on('change', '.selectfetcher', function()
    {
        let fetchUrl = $(this).data('fetchurl') + '/' + $(this).val();
        let target = $(this).data('target');

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                $(target).html(html);
            });
    });

    window.getTooltipFetchReplacingId = function(target, fetchtarget)
    {
        if(fetchtarget == 'row')
        {
            let row = window.__getTR(target);
            return $(row).attr('id');
        }

        if(fetchtarget == 'currentField')
        {
            let cell = window.__getCell(target);
            let cellIndex = $(target).data('cellindex');

            cellData = cell.data();

            return cellData[cellIndex][0];
        }

        if(fetchtarget == 'elementId')
        {
            let cell = window.__getCell(target);
            let cellIndex = $(target).data('cellindex');

            cellData = cell.data();

            return cellData[cellIndex][1];
        }

        alert('non so come prendere il replasincId');
    }

    window.addIframedToUrl = function(url)
    {
        if(url.indexOf('?') !== -1)
            return url + '&iframed=1';

        return url + '?iframed=1';
    }

    window.getTooltipFetchUrl = function(target)
    {
        let fetchUrl = $(target).data('fetchurl');

        if(fetchUrl)
            return fetchUrl;

        let th = window.__getTH(target);

        fetchUrl = $(th).data('fetch');

        let fetchtarget = $(th).data('fetchtarget');
        let replacingId = window.getTooltipFetchReplacingId(target, fetchtarget);

        return fetchUrl.replace(window.replace_model_id_string, replacingId);
    }

    $('body').on('click', '.clicktoclose', function (e)
    {
        $(this).remove();
    });

    UIkit.util.on(document, 'beforehide', '.uk-tooltip.uk-active', function(e)
    {
        if($(e.srcElement).hasClass('clicktoclose'))
          e.preventDefault();
    });

    $('body').on('dblclick', '.clickfetchertooltip', function (e)
    {
        var fetchUrl = window.getTooltipFetchUrl(this);
        var target = this;
        fetch(fetchUrl).then(function (response)
        {
            return response.text();
        }).then(function (html)
        {
            UIkit.tooltip(target,
            {
                title: html,
                cls: 'uk-active clicktoclose'
            }).show();
        });
    });

    $('body').on('dblclick', '.clickfetchermodal', function (e)
    {
        var fetchUrl = window.getTooltipFetchUrl(this);
        var target = this;

        fetch(fetchUrl).then(function (response)
        {
            return response.text();
        }).then(function (html)
        {
            let modal = UIkit.modal.dialog(html);//.show();

            let th = window.__getTH(target);

            $(modal.$el).addClass(th.attr('class'));
        });
    });

    $(document).on('beforehide', '.uk-lightbox', function ()
    {
        if (typeof($(this).data('reloadtableid')) !== 'undefined')
        {
            var table = $('#' + $(this).data('reloadtableid')).DataTable();
            window.reloadDatatable(table);
        }
    });

    $('body').on('click', '.clickfetcherlightbox, .clickfetcheriframe', function(e)
    {
        let fetchUrl = window.addIframedToUrl(
            window.getTooltipFetchUrl(this)
        );

        let target = this;
        let panel = UIkit.lightboxPanel({
            items: [
                {
                    source: fetchUrl,
                    type: 'iframe'
                }
            ],
        });

        let reloadTable = false;

        let th = window.__getTH(target);
        if($(th).data('reloadtable'))
            reloadTable = true;

        if($(target).data('reloadtable'))
            reloadTable = true;

        if(reloadTable)
        {
            let table = window.__getTable(target);

            let tableId = $(table).attr('id');

            $(panel.$el).data('reloadtableid', tableId);
        }

        panel.show();
    })

    $('body').on('mouseenter', '.hoverfetchermodal', function(e)
    {
        let fetchUrl = window.getTooltipFetchUrl(this);

        let target = this;

        fetch(fetchUrl).then(function (response)
        {
            return response.text();
        }).then(function (html)
        {
            UIkit.tooltip(target,
            {
                title: html
            }).show();
        });
    });

    $('body').on('mouseenter', '.hoverfetchertooltip', function(e)
    {
        let fetchUrl = window.getTooltipFetchUrl(this);

        let target = this;

        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                UIkit.tooltip(target, {
                    title : html
                }).show();
            });
    });

});
