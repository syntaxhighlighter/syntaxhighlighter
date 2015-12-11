// Removes all plugins from the regular XRegExp.

module.exports = function(source, map) {
  var path = require('path');
  var fs = require('fs');
  var xregexpPath = path.dirname(require.resolve('xregexp'));
  var callback = this.async();

  this.cacheable();

  fs.readFile(xregexpPath + '/xregexp.js', function (err, content) {
    if (err) {
      console.error(err.stack);
      process.exit(-1);
    }

    callback(null, content, map);
  });
}