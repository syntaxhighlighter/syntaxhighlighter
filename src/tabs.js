/**
 * Replaces tabs with spaces.
 *
 * @param {String} code   Source code.
 * @param {Number} tabSize  Size of the tab.
 * @return {String}     Returns code with all tabs replaces by spaces.
 */
function regular(code, tabSize)
{
  var tab = '';

  for (var i = 0; i < tabSize; i++)
    tab += ' ';

  return code.replace(/\t/g, tab);
};

/**
 * Replaces tabs with smart spaces.
 *
 * @param {String} code    Code to fix the tabs in.
 * @param {Number} tabSize Number of spaces in a column.
 * @return {String}        Returns code with all tabs replaces with roper amount of spaces.
 */
function smart(code, tabSize)
{
  var lines = splitLines(code),
    tab = '\t',
    spaces = ''
    ;

  // Create a string with 1000 spaces to copy spaces from...
  // It's assumed that there would be no indentation longer than that.
  for (var i = 0; i < 50; i++)
    spaces += '                    '; // 20 spaces * 50

  // This function inserts specified amount of spaces in the string
  // where a tab is while removing that given tab.
  function insertSpaces(line, pos, count)
  {
    return line.substr(0, pos)
      + spaces.substr(0, count)
      + line.substr(pos + 1, line.length) // pos + 1 will get rid of the tab
      ;
  };

  // Go through all the lines and do the 'smart tabs' magic.
  code = eachLine(code, function(line)
  {
    if (line.indexOf(tab) == -1)
      return line;

    var pos = 0;

    while ((pos = line.indexOf(tab)) != -1)
    {
      // This is pretty much all there is to the 'smart tabs' logic.
      // Based on the position within the line and size of a tab,
      // calculate the amount of spaces we need to insert.
      var spaces = tabSize - pos % tabSize;
      line = insertSpaces(line, pos, spaces);
    }

    return line;
  });

  return code;
};

module.exports = function(code, tabSize, smart)
{
  return smart ? smart(code, tabSize) : regular(code, tabSize);
}
