var
  XRegExp = require('xregexp'),
  params = require('opts-parser'),
  parser = require('syntaxhighlighter-parser'),
  transformers = require('./transformers'),
  utils = require('./utils'),
  dom = require('./renderer/dom')
  ;

var sh = module.exports = {
  defaults : require('./defaults'),
  config : require('./config'),

  /** Internal 'global' variables. */
  vars : {
    discoveredBrushes : null,
    highlighters : {}
  },

  /** This object is populated by user included external brush files. */
  brushes : {},

  /** Common regular expressions. */
  regexLib : require('./regexlib'),

  /**
   * Finds all elements on the page which should be processes by SyntaxHighlighter.
   *
   * @param {Object} globalParams   Optional parameters which override element's
   *                  parameters. Only used if element is specified.
   *
   * @param {Object} element  Optional element to highlight. If none is
   *              provided, all elements in the current document
   *              are returned which qualify.
   *
   * @return {Array}  Returns list of <code>{ target: DOMElement, params: Object }</code> objects.
   */
  findElements: function(globalParams, element)
  {
    var elements = element ? [element] : utils.toArray(document.getElementsByTagName(sh.config.tagName)),
      conf = sh.config,
      result = []
      ;

    // support for <SCRIPT TYPE="syntaxhighlighter" /> feature
    // if (conf.useScriptTags)
      // elements = elements.concat(getSyntaxHighlighterScriptTags());

    if (elements.length === 0)
      return result;

    for (var i = 0, l = elements.length; i < l; i++)
    {
      var item = {
        target: elements[i],
        // local params take precedence over globals
        params: utils.merge(globalParams, params.parse(elements[i].className))
      };

      if (item.params['brush'] == null)
        continue;

      result.push(item);
    }

    return result;
  },

  /**
   * Shorthand to highlight all elements on the page that are marked as
   * SyntaxHighlighter source code.
   *
   * @param {Object} globalParams   Optional parameters which override element's
   *                  parameters. Only used if element is specified.
   *
   * @param {Object} element  Optional element to highlight. If none is
   *              provided, all elements in the current document
   *              are highlighted.
   */
  highlight: function(globalParams, element)
  {
    var elements = this.findElements(globalParams, element),
      propertyName = 'innerHTML',
      highlighter = null,
      conf = sh.config
      ;

    if (elements.length === 0)
      return;

    for (var i = 0, l = elements.length; i < l; i++)
    {
      var element = elements[i],
        target = element.target,
        params = element.params,
        brushName = params.brush,
        code
        ;

      if (brushName == null)
        continue;

      // Instantiate a brush
      if (params['html-script'] == 'true' || sh.defaults['html-script'] == true)
      {
        highlighter = new sh.HtmlScript(brushName);
        brushName = 'htmlscript';
      }
      else
      {
        var brush = findBrush(brushName);

        if (brush)
          highlighter = new brush();
        else
          continue;
      }

      code = target[propertyName];

      // remove CDATA from <SCRIPT/> tags if it's present
      if (conf.useScriptTags)
        code = stripCData(code);

      // Inject title if the attribute is present
      if ((target.title || '') != '')
        params.title = target.title;

      params['brush'] = brushName;
      highlighter.init(params);
      element = highlighter.getDiv(code);

      // carry over ID
      if ((target.id || '') != '')
        element.id = target.id;

      target.parentNode.replaceChild(element, target);
    }
  },

  /**
   * Main entry point for the SyntaxHighlighter.
   * @param {Object} params Optional params to apply to all highlighted elements.
   */
  all: function(params)
  {
    dom.attachEvent(
      window,
      'load',
      function() { sh.highlight(params); }
    );
  }
}; // end of sh

/**
 * Generates HTML ID for the highlighter.
 * @param {String} highlighterId Highlighter ID.
 * @return {String} Returns HTML ID.
 */
function getHighlighterId(id)
{
  var prefix = 'highlighter_';
  return id.indexOf(prefix) == 0 ? id : prefix + id;
};

/**
 * Finds Highlighter instance by ID.
 * @param {String} highlighterId Highlighter ID.
 * @return {Highlighter} Returns instance of the highlighter.
 */
function getHighlighterById(id)
{
  return sh.vars.highlighters[getHighlighterId(id)];
};

/**
 * Finds highlighter's DIV container.
 * @param {String} highlighterId Highlighter ID.
 * @return {Element} Returns highlighter's DIV element.
 */
function getHighlighterDivById(id)
{
  return document.getElementById(getHighlighterId(id));
};

/**
 * Stores highlighter so that getHighlighterById() can do its thing. Each
 * highlighter must call this method to preserve itself.
 * @param {Highilghter} highlighter Highlighter instance.
 */
function storeHighlighter(highlighter)
{
  sh.vars.highlighters[getHighlighterId(highlighter.id)] = highlighter;
};


/**
 * Displays an alert.
 * @param {String} str String to display.
 */
function alert(str)
{
  window.alert(sh.config.strings.alert + str);
};

/**
 * Finds a brush by its alias.
 *
 * @param {String} alias    Brush alias.
 * @param {Boolean} showAlert Suppresses the alert if false.
 * @return {Brush}        Returns bursh constructor if found, null otherwise.
 */
function findBrush(alias, showAlert)
{
  var brushes = sh.vars.discoveredBrushes,
    result = null
    ;

  if (brushes == null)
  {
    brushes = {};

    // Find all brushes
    for (var brush in sh.brushes)
    {
      var info = sh.brushes[brush],
        aliases = info.aliases
        ;

      if (aliases == null)
        continue;

      // keep the brush name
      info.brushName = brush.toLowerCase();

      for (var i = 0, l = aliases.length; i < l; i++)
        brushes[aliases[i]] = brush;
    }

    sh.vars.discoveredBrushes = brushes;
  }

  result = sh.brushes[brushes[alias]];

  if (result == null && showAlert)
    alert(sh.config.strings.noBrush + alias);

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
 * Turns all URLs in the code into <a/> tags.
 * @param {String} code Input code.
 * @return {String} Returns code with </a> tags.
 */
function processUrls(code)
{
  var gt = /(.*)((&gt;|&lt;).*)/;

  return code.replace(sh.regexLib.url, function(m)
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
};

/**
 * Strips <![CDATA[]]> from <SCRIPT /> content because it should be used
 * there in most cases for XHTML compliance.
 * @param {String} original Input code.
 * @return {String} Returns code without leading <![CDATA[]]> tags.
 */
function stripCData(original)
{
  var left = '<![CDATA[',
    right = ']]>',
    // for some reason IE inserts some leading blanks here
    copy = utils.trim(original),
    changed = false,
    leftLength = left.length,
    rightLength = right.length
    ;

  if (copy.indexOf(left) == 0)
  {
    copy = copy.substring(leftLength);
    changed = true;
  }

  var copyLength = copy.length;

  if (copy.indexOf(right) == copyLength - rightLength)
  {
    copy = copy.substring(0, copyLength - rightLength);
    changed = true;
  }

  return changed ? copy : original;
};

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

  highlighter = getHighlighterById(highlighterDiv.id);

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
 * Simulates HTML code with a scripting language embedded.
 *
 * @param {String} scriptBrushName Brush name of the scripting language.
 */
sh.HtmlScript = function(scriptBrushName)
{
  var brushClass = findBrush(scriptBrushName),
    scriptBrush,
    xmlBrush = new sh.brushes.Xml(),
    bracketsRegex = null,
    ref = this,
    methodsToExpose = 'getDiv getHtml init'.split(' ')
    ;

  if (brushClass == null)
    return;

  scriptBrush = new brushClass();

  for(var i = 0, l = methodsToExpose.length; i < l; i++)
    // make a closure so we don't lose the name after i changes
    (function() {
      var name = methodsToExpose[i];

      ref[name] = function()
      {
        return xmlBrush[name].apply(xmlBrush, arguments);
      };
    })();

  if (scriptBrush.htmlScript == null)
  {
    alert(sh.config.strings.brushNotHtmlScript + scriptBrushName);
    return;
  }

  xmlBrush.regexList.push(
    { regex: scriptBrush.htmlScript.code, func: process }
  );

  function offsetMatches(matches, offset)
  {
    for (var j = 0, l = matches.length; j < l; j++)
      matches[j].index += offset;
  }

  function process(match, info)
  {
    var code = match.code,
        results = [],
        regexList = scriptBrush.regexList,
        offset = match.index + match.left.length,
        htmlScript = scriptBrush.htmlScript,
        matches
        ;

    function add(matches)
    {
      results = results.concat(matches);
    }

    matches = parser.parse(code, regexList);
    offsetMatches(matches, offset);
    add(matches);

    // add left script bracket
    if (htmlScript.left != null && match.left != null)
    {
      matches = parser.parse(match.left, [htmlScript.left]);
      offsetMatches(matches, match.index);
      add(matches);
    }

    // add right script bracket
    if (htmlScript.right != null && match.right != null)
    {
      matches = parser.parse(match.right, [htmlScript.right]);
      offsetMatches(matches, match.index + match[0].lastIndexOf(match.right));
      add(matches);
    }

    for (var j = 0, l = results.length; j < l; j++)
      results[j].brushName = brushClass.brushName;

    return results;
  }
};

/**
 * Main Highlither class.
 * @constructor
 */
sh.Highlighter = function()
{
  // not putting any code in here because of the prototype inheritance
};

sh.Highlighter.prototype = {
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
      '<div id="' + getHighlighterId(this.id) + '" class="' + classes.join(' ') + '">'
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
  init: function(params)
  {
    this.id = utils.guid();

    // register this instance in the highlighters list
    storeHighlighter(this);

    // local params take precedence over defaults
    this.params = utils.merge(sh.defaults, params || {})

    // process light mode
    if (this.getParam('light') == true)
      this.params.toolbar = this.params.gutter = false;
  },

  /**
   * Converts space separated list of keywords into a regular expression string.
   * @param {String} str    Space separated keywords.
   * @return {String}       Returns regular expression string.
   */
  getKeywords: function(str)
  {
    str = str
      .replace(/^\s+|\s+$/g, '')
      .replace(/\s+/g, '|')
      ;

    return '\\b(?:' + str + ')\\b';
  },

  /**
   * Makes a brush compatible with the `html-script` functionality.
   * @param {Object} regexGroup Object containing `left` and `right` regular expressions.
   */
  forHtmlScript: function(regexGroup)
  {
    var regex = { 'end' : regexGroup.right.source };

    if(regexGroup.eof)
      regex.end = "(?:(?:" + regex.end + ")|$)";

    this.htmlScript = {
      left : { regex: regexGroup.left, css: 'script' },
      right : { regex: regexGroup.right, css: 'script' },
      code : XRegExp(
        "(?<left>" + regexGroup.left.source + ")" +
        "(?<code>.*?)" +
        "(?<right>" + regex.end + ")",
        "sgi"
        )
    };
  }
}; // end of Highlighter

