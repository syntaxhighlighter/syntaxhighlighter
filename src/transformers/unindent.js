var utils = require('../utils');

/**
 * Unindents a block of text by the lowest common indent amount.
 */
module.exports = function(code, opts)
{
  if (opts['unindent'] !== true)
    return code;

  var lines = utils.splitLines(code),
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
      return code;

    min = Math.min(matches[0].length, min);
  }

  // trim minimum common number of white space from the begining of every line
  if (min > 0)
    for (var i = 0, l = lines.length; i < l; i++)
      lines[i] = lines[i].substr(min);

  return lines.join('\n');
};
