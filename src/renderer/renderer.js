var
  parser = require('syntaxhighlighter-parser'),
  dom = require('./dom'),
  highlighters = require('./highlighters'),
  regexLib = require('../regexlib')
  utils = require('../utils'),
  transformers = require('../transformers'),
  defaults = require('../defaults')
  ;

/**
 * Quick code mouse double click handler.
 */
function quickCodeHandler(e)
{
  var target = e.target,
    highlighterDiv = dom.findParentElement(target, '.syntaxhighlighter'),
    container = dom.findParentElement(target, '.container'),
    textarea = document.createElement('textarea'),
    highlighter
    ;

  if (!container || !highlighterDiv || dom.findElement(container, 'textarea'))
    return;

  highlighter = highlighters.get(highlighterDiv.id);

  // add source class name
  dom.addClass(highlighterDiv, 'source');

  // Have to go over each line and grab it's text, can't just do it on the
  // container because Firefox loses all \n where as Webkit doesn't.
  var lines = container.childNodes,
    code = []
    ;

  for (var i = 0, l = lines.length; i < l; i++)
    code.push(lines[i].innerText || lines[i].textContent);

  // using \r instead of \r or \r\n makes this work equally well on IE, FF and Webkit
  code = code.join('\r');

    // For Webkit browsers, replace nbsp with a breaking space
    code = code.replace(/\u00a0/g, " ");

  // inject <textarea/> tag
  textarea.appendChild(document.createTextNode(code));
  container.appendChild(textarea);

  // preselect all text
  textarea.focus();
  textarea.select();

  // set up handler for lost focus
  dom.attachEvent(textarea, 'blur', function(e)
  {
    textarea.parentNode.removeChild(textarea);
    dom.removeClass(highlighterDiv, 'source');
  });
};

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
      spaces += sh.config.space;

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

function Renderer()
{
  // not putting any code in here because of the prototype inheritance
};

Renderer.prototype = {
  /**
   * Returns value of the parameter passed to the highlighter.
   * @param {String} name       Name of the parameter.
   * @param {Object} defaultValue   Default value.
   * @return {Object}         Returns found value or default value otherwise.
   */
  getParam: function(name, defaultValue)
  {
    var result = this.params[name];
    return utils.toBoolean(result == null ? defaultValue : result);
  },

  /**
   * Shortcut to document.createElement().
   * @param {String} name   Name of the element to create (DIV, A, etc).
   * @return {HTMLElement}  Returns new HTML element.
   */
  create: function(name)
  {
    return document.createElement(name);
  },

  /**
   * Creates an array containing integer line numbers starting from the 'first-line' param.
   * @return {Array} Returns array of integers.
   */
  figureOutLineNumbers: function(code)
  {
    var lines = [],
      firstLine = parseInt(this.getParam('first-line'))
      ;

    utils.eachLine(code, function(line, index)
    {
      lines.push(index + firstLine);
    });

    return lines;
  },

  /**
   * Determines if specified line number is in the highlighted list.
   */
  isLineHighlighted: function(lineNumber)
  {
    var list = this.getParam('highlight', []);

    if (typeof(list) != 'object' && list.push == null)
      list = [ list ];

    return list.indexOf(lineNumber.toString()) != -1;
  },

  /**
   * Generates HTML markup for a single line of code while determining alternating line style.
   * @param {Integer} lineNumber  Line number.
   * @param {String} code Line  HTML markup.
   * @return {String}       Returns HTML markup.
   */
  getLineHtml: function(lineIndex, lineNumber, code)
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

    return '<div class="' + classes.join(' ') + '">' + code + '</div>';
  },

  /**
   * Generates HTML markup for line number column.
   * @param {String} code     Complete code HTML markup.
   * @param {Array} lineNumbers Calculated line numbers.
   * @return {String}       Returns HTML markup.
   */
  getLineNumbersHtml: function(code, lineNumbers)
  {
    var html = '',
      count = utils.splitLines(code).length,
      firstLine = parseInt(this.getParam('first-line')),
      pad = this.getParam('pad-line-numbers')
      ;

    if (pad == true)
      pad = (firstLine + count - 1).toString().length;
    else if (isNaN(pad) == true)
      pad = 0;

    for (var i = 0; i < count; i++)
    {
      var lineNumber = lineNumbers ? lineNumbers[i] : firstLine + i,
        code = lineNumber == 0 ? sh.config.space : padNumber(lineNumber, pad)
        ;

      html += this.getLineHtml(i, lineNumber, code);
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
    html = utils.trim(html);

    var lines = utils.splitLines(html),
      padLength = this.getParam('pad-line-numbers'),
      firstLine = parseInt(this.getParam('first-line')),
      html = '',
      brushName = this.getParam('brush')
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
        spaces = spaces.replace(' ', sh.config.space);
      }

      line = utils.trim(line);

      if (line.length == 0)
        line = sh.config.space;

      html += this.getLineHtml(
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
    var pos = 0,
      result = '',
      brushName = this.getParam('brush', '')
      ;

    function getBrushNameCss(match)
    {
      var result = match ? (match.brushName || brushName) : brushName;
      return result ? result + ' ' : '';
    };

    // Finally, go through the final list of matches and pull the all
    // together adding everything in between that isn't a match.
    for (var i = 0, l = matches.length; i < l; i++)
    {
      var match = matches[i],
        matchBrushName
        ;

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
  getHtml: function(code)
  {
    var html = '',
        classes = ['syntaxhighlighter'],
        matches,
        lineNumbers
        ;

    // process light mode
    if (this.getParam('light') == true)
      this.params.toolbar = this.params.gutter = false;

    className = 'syntaxhighlighter';

    if (this.getParam('collapse') == true)
      classes.push('collapsed');

    if ((gutter = this.getParam('gutter')) == false)
      classes.push('nogutter');

    // add custom user style name
    classes.push(this.getParam('class-name'));

    // add brush alias to the class name for custom CSS
    classes.push(this.getParam('brush'));

    code = transformers(code, this.params);

    if (gutter)
      lineNumbers = this.figureOutLineNumbers(code);

    matches = parser.parse(code, this.regexList, this.params);

    // processes found matches into the html
    html = this.getMatchesHtml(code, matches);

    // finally, split all lines so that they wrap well
    html = this.getCodeLinesHtml(html, lineNumbers);

    // finally, process the links
    if (this.getParam('auto-links'))
      html = processUrls(html);

    if (typeof(navigator) != 'undefined' && navigator.userAgent && navigator.userAgent.match(/MSIE/))
      classes.push('ie');

    html =
      '<div id="' + highlighters.id(this.id) + '" class="' + classes.join(' ') + '">'
        // + (this.getParam('toolbar') ? sh.toolbar.getHtml(this) : '')
        + '<table border="0" cellpadding="0" cellspacing="0">'
          + this.getTitleHtml(this.getParam('title'))
          + '<tbody>'
            + '<tr>'
              + (gutter ? '<td class="gutter">' + this.getLineNumbersHtml(code) + '</td>' : '')
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

  /**
   * Highlights the code and returns complete HTML.
   * @param {String} code     Code to highlight.
   * @return {Element}        Returns container DIV element with all markup.
   */
  getDiv: function(code)
  {
    if (code === null)
      code = '';

    this.code = code;

    var div = this.create('div');

    // create main HTML
    div.innerHTML = this.getHtml(code);

    // set up click handlers
    // if (this.getParam('toolbar'))
    //   dom.attachEvent(dom.findElement(div, '.toolbar'), 'click', sh.toolbar.handler);

    if (this.getParam('quick-code'))
      dom.attachEvent(dom.findElement(div, '.code'), 'dblclick', quickCodeHandler);

    return div;
  },

  /**
   * Initializes the highlighter/brush.
   *
   * Constructor isn't used for initialization so that nothing executes during necessary
   * `new SyntaxHighlighter.Highlighter()` call when setting up brush inheritence.
   *
   * @param {Hash} params Highlighter parameters.
   */
  init: function(regexList, params)
  {
    this.regexList = regexList;
    this.id = utils.guid();

    // register this instance in the highlighters list
    highlighters.set(this.id, this);

    // local params take precedence over defaults
    this.params = utils.merge(defaults, params || {})

    // process light mode
    if (this.getParam('light') == true)
      this.params.toolbar = this.params.gutter = false;
  }
}; // end of Highlighter

module.exports = {
  Renderer: Renderer
};
