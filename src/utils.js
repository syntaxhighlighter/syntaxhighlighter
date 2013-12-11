/**
 * Splits block of text into lines.
 * @param {String} block Block of text.
 * @return {Array} Returns array of lines.
 */
function splitLines(block)
{
  return block.split(/\r?\n/);
}

/**
 * Executes a callback on each line and replaces each line with result from the callback.
 * @param {Object} str      Input string.
 * @param {Object} callback   Callback function taking one string argument and returning a string.
 */
function eachLine(str, callback)
{
  var lines = splitLines(str);

  for (var i = 0, l = lines.length; i < l; i++)
    lines[i] = callback(lines[i], i);

  // include \r to enable copy-paste on windows (ie8) without getting everything on one line
  return lines.join('\r\n');
};

module.exports = {
  splitLines: splitLines,
  eachLine: eachLine
}