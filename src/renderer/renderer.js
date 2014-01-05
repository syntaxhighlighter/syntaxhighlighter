var
  regexLib = require('../regexlib')
  utils = require('../utils'),
  config = require('../config')
  ;

/**
 * Pads number with zeros until it's length is the same as given length.
 *
 * @param {Number} number Number to pad.
 * @param {Number} length Max string length with.
 * @return {String}     Returns a string padded with proper amount of '0'.
 */
function padNumber(number, length)
{
  var result = number.toString();

  while (result.length < length)
    result = '0' + result;

  return result;
};

/**
 * Wraps each line of the string into <code/> tag with given style applied to it.
 *
 * @param {String} str   Input string.
 * @param {String} css   Style name to apply to the string.
 * @return {String}      Returns input string with each line surrounded by <span/> tag.
 */
function wrapLinesWithCode(str, css)
{
  if (str == null || str.length == 0 || str == '\n')
    return str;

  str = str.replace(/</g, '&lt;');

  // Replace two or more sequential spaces with &nbsp; leaving last space untouched.
  str = str.replace(/ {2,}/g, function(m)
  {
    var spaces = '';

    for (var i = 0, l = m.length; i < l - 1; i++)
      spaces += config.space;

    return spaces + ' ';
  });

  // Split each line and apply <span class="...">...</span> to them so that
  // leading spaces aren't included.
  if (css != null)
    str = utils.eachLine(str, function(line)
    {
      if (line.length == 0)
        return '';

      var spaces = '';

      line = line.replace(/^(&nbsp;| )+/, function(s)
      {
        spaces = s;
        return '';
      });

      if (line.length == 0)
        return spaces;

      return spaces + '<code class="' + css + '">' + line + '</code>';
    });

  return str;
}

/**
 * Turns all URLs in the code into <a/> tags.
 * @param {String} code Input code.
 * @return {String} Returns code with </a> tags.
 */
function processUrls(code)
{
  var gt = /(.*)((&gt;|&lt;).*)/;

  return code.replace(regexLib.url, function(m)
  {
    var suffix = '',
      match = null
      ;

    // We include &lt; and &gt; in the URL for the common cases like <http://google.com>
    // The problem is that they get transformed into &lt;http://google.com&gt;
    // Where as &gt; easily looks like part of the URL string.

    if (match = gt.exec(m))
    {
      m = match[1];
      suffix = match[2];
    }

    return '<a href="' + m + '">' + m + '</a>' + suffix;
  });
}

function Renderer(code, matches, opts)
{
  var _this = this;

  _this.opts = opts;
  _this.code = code;
  _this.matches = matches;
  _this.lines = code.split(/\r?\n/);
};

Renderer.prototype = {
  /**
   * Creates an array containing integer line numbers starting from the 'first-line' param.
   * @return {Array} Returns array of integers.
   */
  figureOutLineNumbers: function(code)
  {
    var lineNumbers = [],
        lines = this.lines,
        firstLine = parseInt(this.opts.firstLine || 0),
        i,
        l
        ;

    for (i = 0, l = lines.length; i < l; i++)
      lineNumbers.push(i + firstLine);

    return lineNumbers;
  },

  /**
   * Determines if specified line number is in the highlighted list.
   */
  isLineHighlighted: function(lineNumber)
  {
    var linesToHighlight = this.opts.highlight || [];

    if (typeof(linesToHighlight.push) !== 'function')
      linesToHighlight = [linesToHighlight];

    return linesToHighlight.indexOf(lineNumber.toString()) !== -1;
  },

  /**
   * Generates HTML markup for a single line of code while determining alternating line style.
   * @param {Integer} lineNumber  Line number.
   * @param {String} code Line  HTML markup.
   * @return {String}       Returns HTML markup.
   */
  wrapLine: function(lineIndex, lineNumber, lineHtml)
  {
    var classes = [
      'line',
      'number' + lineNumber,
      'index' + lineIndex,
      'alt' + (lineNumber % 2 == 0 ? 1 : 2).toString()
    ];

    if (this.isLineHighlighted(lineNumber))
      classes.push('highlighted');

    if (lineNumber == 0)
      classes.push('break');

    return '<div class="' + classes.join(' ') + '">' + lineHtml + '</div>';
  },

  /**
   * Generates HTML markup for line number column.
   * @param {String} code     Complete code HTML markup.
   * @param {Array} lineNumbers Calculated line numbers.
   * @return {String}       Returns HTML markup.
   */
  renderLineNumbers: function(code, lineNumbers)
  {
    var html = '',
        count = utils.splitLines(code).length,
        firstLine = parseInt(this.opts.firstLine),
        pad = this.opts.padLineNumbers,
        lineNumber,
        i
        ;

    if (pad == true)
      pad = (firstLine + count - 1).toString().length;
    else if (isNaN(pad) == true)
      pad = 0;

    for (i = 0; i < count; i++)
    {
      lineNumber = lineNumbers ? lineNumbers[i] : firstLine + i;
      code = lineNumber == 0 ? config.space : padNumber(lineNumber, pad);
      html += this.wrapLine(i, lineNumber, code);
    }

    return html;
  },

  /**
   * Splits block of text into individual DIV lines.
   * @param {String} code     Code to highlight.
   * @param {Array} lineNumbers Calculated line numbers.
   * @return {String}       Returns highlighted code in HTML form.
   */
  getCodeLinesHtml: function(html, lineNumbers)
  {
    // html = utils.trim(html);

    var _this = this,
        lines = utils.splitLines(html),
        padLength = _this.opts.padLineNumbers,
        firstLine = parseInt(_this.opts.firstLine),
        brushName = _this.opts.brush,
        html = ''
        ;

    for (var i = 0, l = lines.length; i < l; i++)
    {
      var line = lines[i],
          indent = /^(&nbsp;|\s)+/.exec(line),
          spaces = null,
          lineNumber = lineNumbers ? lineNumbers[i] : firstLine + i;
          ;

      if (indent != null)
      {
        spaces = indent[0].toString();
        line = line.substr(spaces.length);
        spaces = spaces.replace(' ', config.space);
      }

      line = utils.trim(line);

      if (line.length == 0)
        line = config.space;

      html += _this.wrapLine(
        i,
        lineNumber,
        (spaces != null ? '<code class="' + brushName + ' spaces">' + spaces + '</code>' : '') + line
      );
    }

    return html;
  },

  /**
   * Returns HTML for the table title or empty string if title is null.
   */
  getTitleHtml: function(title)
  {
    return title ? '<caption>' + title + '</caption>' : '';
  },

  /**
   * Finds all matches in the source code.
   * @param {String} code   Source code to process matches in.
   * @param {Array} matches Discovered regex matches.
   * @return {String} Returns formatted HTML with processed mathes.
   */
  getMatchesHtml: function(code, matches)
  {
    function getBrushNameCss(match)
    {
      var result = match ? (match.brushName || brushName) : brushName;
      return result ? result + ' ' : '';
    };

    var pos = 0,
        result = '',
        brushName = this.opts.brush || '',
        match,
        matchBrushName,
        i,
        l
        ;

    // Finally, go through the final list of matches and pull the all
    // together adding everything in between that isn't a match.
    for (i = 0, l = matches.length; i < l; i++)
    {
      match = matches[i];

      if (match === null || match.length === 0)
        continue;

      matchBrushName = getBrushNameCss(match);

      result += wrapLinesWithCode(code.substr(pos, match.index - pos), matchBrushName + 'plain')
          + wrapLinesWithCode(match.value, matchBrushName + match.css)
          ;

      pos = match.index + match.length + (match.offset || 0);
    }

    // don't forget to add whatever's remaining in the string
    result += wrapLinesWithCode(code.substr(pos), getBrushNameCss() + 'plain');

    return result;
  },

  /**
   * Generates HTML markup for the whole syntax highlighter.
   * @param {String} code Source code.
   * @return {String} Returns HTML markup.
   */
  getHtml: function()
  {
    var _this = this,
        opts = _this.opts,
        code = _this.code,
        matches = _this.matches,
        classes = ['syntaxhighlighter'],
        lineNumbers,
        html
        ;

    if (opts.collapse === true)
      classes.push('collapsed');

    if ((gutter = opts.gutter) === false)
      classes.push('nogutter');

    // add custom user style name
    classes.push(opts.className);

    // add brush alias to the class name for custom CSS
    classes.push(opts.brush);

    if (gutter)
      lineNumbers = _this.figureOutLineNumbers(code);

    // processes found matches into the html
    html = _this.getMatchesHtml(code, matches);

    // finally, split all lines so that they wrap well
    html = _this.getCodeLinesHtml(html, lineNumbers);

    // finally, process the links
    if (opts.autoLinks)
      html = processUrls(html);

    if (typeof(navigator) != 'undefined' && navigator.userAgent && navigator.userAgent.match(/MSIE/))
      classes.push('ie');

    html =
      '<div class="' + classes.join(' ') + '">'
        // + (opts.toolbar ? sh.toolbar.getHtml(_this) : '')
        + '<table border="0" cellpadding="0" cellspacing="0">'
          + _this.getTitleHtml(opts.title)
          + '<tbody>'
            + '<tr>'
              + (gutter ? '<td class="gutter">' + _this.renderLineNumbers(code) + '</td>' : '')
              + '<td class="code">'
                + '<div class="container">'
                  + html
                + '</div>'
              + '</td>'
            + '</tr>'
          + '</tbody>'
        + '</table>'
      + '</div>'
      ;

    return html;
  },

  render: function()
  {
    return this.getHtml();
  }
};

module.exports = {
  Renderer: Renderer
};
