app.factory('$blob', function() {
  return {
    csvToURL: function(content) {
      var blob;
      blob = new Blob([content], {type: 'text/csv'});
      return (window.URL || window.webkitURL).createObjectURL(blob);
    },
    sanitizeCSVName: function(name) {
      if (/^[A-Za-z0-9]+\.csv$/.test(name)) {
        return name;
      }
      if (/^[A-Za-z0-9]+/.test(name)) {
        return name + ".csv";
      }
      throw new Error("Invalid title fo CSV file : " + name);
    },
    revoke: function(url) {
      return (window.URL || window.webkitURL).revokeObjectURL(url);
    }
  };
});
 
app.factory('$click', function() {
  return {
    on: function(element) {
      var e = document.createEvent("MouseEvent");
      e.initMouseEvent("click", false, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
      element.dispatchEvent(e);
    }
  };
});
 
app.directive('downloadCsv', function($parse, $click, $blob, $log, $timeout) {
  return {
    compile: function($element, attr) {
      var fn = $parse(attr.downloadCsv);
       
      return function(scope, element, attr) {
         
        element.on('click', function(event) {
          var a_href, content, title, url, _ref;
          _ref = fn(scope), content = _ref.content, title = _ref.title;
           
          if (!(content != null) && !(title != null)) {
            $log.warn("Invalid content or title in download-csv : ", content, title);
            return;
          }
           
          title = $blob.sanitizeCSVName(title);
          url = $blob.csvToURL(content);
           
          element.append("<a download=\"" + title + "\" href=\"" + url + "\"></a>");
          a_href = element.find('a')[0];
           
          $click.on(a_href);
          $timeout(function() {$blob.revoke(url);});
           
          element[0].removeChild(a_href);
        });
      };
    }
  };
});