// This loader adds compatibility header to v3 brushes that are being bundled in.

var fs = require('fs');
var header = null;

function getHeader(callback) {
  if (header) {
    return setImmediate(function () { callback(null, header) });
  }

  fs.readFile(__dirname + '/templates/brush-v3-compatibility-header.js', 'utf8', function (err, content) {
    callback(err, header = content);
  });
}

module.exports = function (source, map) {
  var callback = this.async();

  this.cacheable();

  getHeader(function (err, header) {
    if (source.indexOf("require('shCore').SyntaxHighlighter") !== -1) {
      source = header + source;
    }

    return callback(err, source, map);
  });
}