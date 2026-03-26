(function($){
  if (!$.fn.serializeJSON) {
    $.fn.serializeJSON = function() {
      var obj = {};
      var arr = this.serializeArray();
      $.each(arr, function(){
        var name = this.name;
        var value = this.value || '';
        if (obj[name] !== undefined) {
          if (!Array.isArray(obj[name])) obj[name] = [obj[name]];
          obj[name].push(value);
        } else {
          obj[name] = value;
        }
      });
      return obj;
    };
  }
})(jQuery);