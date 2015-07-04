jQuery('#most-played').DataTable({
  serverSide: true,
  ajax: function (data, cb) {
    data.action = 'most_played';
    jQuery.ajax({
      url: _mp_ajax.ajax_url,
      type: 'post',
      dataType: 'json',
      data: data,
      success: cb
    });
  },
  searching: true,
  pageLength: 50,
  order: [ [ 2, 'desc' ] ],
  columns: [
    { data: function (row, type, set, cell) {
      return '#' + (cell.settings.oAjaxData.start + cell.row + 1)
    } },
    { orderable: false, searchable: true, data: 0 },
    { orderable: false, searchable: true, data: 1 },
    { data: 2 }
  ]
})
