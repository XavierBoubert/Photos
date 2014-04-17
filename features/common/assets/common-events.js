(function(window) {
  'use strict';

  window.AbstractEvents = function() {

    var _this = this,
        _events = {},
        _anything = [];

    this.on = function(name, func) {
      _events[name] = _events[name] || [];
      _events[name].push(func);

      return _this;
    };

    this.onAnything = function(func) {
      _anything.push(func);

      return _this;
    };

    this.off = function(name) {
      name = name || false;
      if(!name) {
        _events = {};
        _anything = [];
      }
      else if(typeof _events[name] != 'undefined') {
        delete _events[name];
      }

      return _this;
    };

    this.fire = function(name, args) {
      args = args || null;

      if(typeof _events[name] != 'undefined') {
        for(var i = 0, len = _events[name].length; i < len; i++) {
          _events[name][i](args);
        }
      }
      for(var i = 0, len = _anything.length; i < len; i++) {
        _anything[i](args);
      }

      return _this;
    };

  };

})(window);