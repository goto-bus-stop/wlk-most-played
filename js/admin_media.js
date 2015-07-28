;(function ($) {

    function save(cid, prop, value, td) {
        $.ajax({
            url: _mp_admmedia.ajax_url,
            data: {
                action: 'wlk_admin_media',
                cid: cid,
                prop: prop,
                value: value
            },
            type: 'post',
            dataType: 'json',
            success: function (res) {
                if (res.error) {
                    alert(res.error)
                }
                else {
                    td.text(res.ok).css('opacity', 1)
                }
            }
        })
    }

    var table = $('#sekshibot-media')
    table.on('click td', function (e) {
        var td = $(e.target).closest('td')
        if (e.ctrlKey) {
            var input = $('<input />')
                .attr('type', 'text')
                .val(td.text().trim())
            td.empty().append(input)

            input.on('keydown', function (e) {
                if (e.keyCode === 13) {
                    var val = input.val()
                    td.text(val).css('opacity', 0.7)
                    save(td.parent('tr').data('cid'), td.data('name'), val, td)
                }
            })
        }
    })

}(jQuery));
