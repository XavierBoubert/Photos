(function(window) {
  'use strict';

  window.Templates = function(selector) {

    var _templates = this,
        _selector = selector,
        _tpls = {};

    this.selector = function() {
      return _selector;
    };

    $(_selector).find('[data-template]').each(function() {
      var $this = $(this),
          name = $this.data('template');

      _tpls[name] = $this.html();
      $this.remove();

      _templates[name] = function(data) {
        data = data || {};
        if(!$.isArray(data)) {
          data = [data];
        }

        var html = _tpls[name];

        html = html.replace(/template-/g, '');

        var values = Array.apply(null, new Array(data.length)).map(String.prototype.valueOf, html),
            item;

        for(var i = 0; i < values.length; i++) {
          values[i] = values[i].replace(new RegExp('{{$index}}', 'g'), i);
          values[i] = values[i].replace(new RegExp('{{$total}}', 'g'), values.length);
          for(item in data[i]) {
            values[i] = values[i].replace(new RegExp('{{' + item + '}}', 'g'), data[i][item]);
          }
        }

        return values.join('');
      }

    });

  };

})(window);