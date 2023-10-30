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

        alert('non so come prendere il replasincId');
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
            UIkit.modal.dialog(html);
        });
    });

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
