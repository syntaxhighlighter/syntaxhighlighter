var
  trim        = require('./trim'),
  bloggerMode = require('./blogger_mode'),
  stripBrs    = require('./strip_brs'),
  unindenter  = require('unindenter'),
  retabber    = require('retabber')
  ;

module.exports = function(code, opts)
{
  code = trim(code, opts);
  code = bloggerMode(code, opts);
  code = stripBrs(code, opts);
  code = unindenter.unindent(code, opts);

  var tabSize = opts['tab-size'];
  code = opts['smart-tabs'] === true ? retabber.smart(code, tabSize) : retabber.regular(code, tabSize);

  return code;
};
