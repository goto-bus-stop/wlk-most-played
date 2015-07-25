;(function ($) {

  _sekshi_history.page = parseInt(_sekshi_history.page, 10);
  _sekshi_history.lastPage = parseInt(_sekshi_history.lastPage, 10);

  var history = $('.history');
  var list = history.find('.history-list');
  var prev = history.find('.paginate button.previous');
  var next = history.find('.paginate button.next');
  prev.on('click', function () {
    show(_sekshi_history.page - 1);
  });
  next.on('click', function () {
    show(_sekshi_history.page + 1);
  });

  update();

  function show(page) {
    list.load(_sekshi_history.ajax_url + ' .history-list', {
      action: 'sekshi_history',
      filter: _sekshi_history.filter,
      page: page
    }, function () {
      _sekshi_history.page = page;
      update();
    });
  }

  function update() {
    var page = parseInt(_sekshi_history.page, 10)
    prev.toggleClass('hide', page < 1);
    next.toggleClass('hide', page + 1 >= _sekshi_history.lastPage);
  }

}(jQuery));
