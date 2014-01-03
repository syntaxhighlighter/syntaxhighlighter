var
  trim        = require('./trim'),
  bloggerMode = require('./blogger_mode'),
  stripBrs    = require('./strip_brs'),
  unindent    = require('./unindent'),
  tabs        = require('./tabs')
  ;

module.exports = function(code, opts)
{
  code = trim(code, opts);
  code = bloggerMode(code, opts);
  code = stripBrs(code, opts);
  code = unindent(code, opts);
  code = tabs(code, opts);

  return code;
};
