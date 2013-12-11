var
  utils = require('./utils'),
  tabs = require('./tabs')
  ;

/**
 * Performs various string fixes based on configuration.
 */
function fixInputString(str, opts)
{
  var br = /<br\s*\/?>|&lt;br\s*\/?&gt;/gi;

  // FIXME join global opts with local opts

  if (opts['bloggerMode'] == true)
    str = str.replace(br, '\n');

  if (opts['stripBrs'] == true)
    str = str.replace(br, '');

  return str;
}

/**
 * Unindents a block of text by the lowest common indent amount.
 * @param {String} str   Text to unindent.
 * @return {String}      Returns unindented text block.
 */
function unindent(str, opts)
{
  var lines = utils.splitLines(fixInputString(str, opts)),
    indents = new Array(),
    regex = /^\s*/,
    min = 1000
    ;

  // go through every line and check for common number of indents
  for (var i = 0, l = lines.length; i < l && min > 0; i++)
  {
    var line = lines[i];

    if (utils.trim(line).length == 0)
      continue;

    var matches = regex.exec(line);

    // In the event that just one line doesn't have leading white space
    // we can't unindent anything, so bail completely.
    if (matches == null)
      return str;

    min = Math.min(matches[0].length, min);
  }

  // trim minimum common number of white space from the begining of every line
  if (min > 0)
    for (var i = 0, l = lines.length; i < l; i++)
      lines[i] = lines[i].substr(min);

  return lines.join('\n');
};

module.exports = function(code, opts)
{
  code = utils.trimFirstAndLastLines(code)
    .replace(/\r/g, ' ') // IE lets these buggers through
    ;

  // replace tabs with spaces
  code = tabs(code, opts['tab-size'], opts['smart-tabs'] == true);

  // unindent code by the common indentation
  if (opts['unindent'])
    code = unindent(code, opts);

  return code;
};
