var
  optsParser = require('opts-parser'),
  match = require('syntaxhighlighter-match'),
  Renderer = require('syntaxhighlighter-html-renderer').default,
  utils = require('./utils'),
  transformers = require('./transformers'),
  dom = require('./dom'),
  config = require('./config'),
  defaults = require('./defaults'),
  HtmlScript = require('./html_script')
  ;

const sh = {
  Match: match.Match,
  Highlighter: require('brush-base'),

  config: require('./config'),
  regexLib: require('syntaxhighlighter-regex').commonRegExp,

  /** Internal 'global' variables. */
  vars : {
    discoveredBrushes : null,
    highlighters : {}
  },

  /** This object is populated by user included external brush files. */
  brushes : {},

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
    elements = elements.concat(dom.getSyntaxHighlighterScriptTags());

    if (elements.length === 0)
      return result;

    for (var i = 0, l = elements.length; i < l; i++)
    {
      var item = {
        target: elements[i],
        // local params take precedence over globals
        params: optsParser.defaults(optsParser.parse(elements[i].className), globalParams)
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
    var elements = sh.findElements(globalParams, element),
        propertyName = 'innerHTML',
        brush = null,
        renderer,
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
          brush,
          matches,
          code
          ;

      if (brushName == null)
        continue;

      brush = findBrush(brushName);

      if (!brush)
        continue;

      // local params take precedence over defaults
      params = optsParser.defaults(params || {}, defaults);
      params = optsParser.defaults(params, config);

      // Instantiate a brush
      if (params['html-script'] == true || defaults['html-script'] == true)
      {
        brush = new HtmlScript(findBrush('xml'), brush);
        brushName = 'htmlscript';
      }
      else
      {
        brush = new brush();
      }

      code = target[propertyName];

      // remove CDATA from <SCRIPT/> tags if it's present
      if (conf.useScriptTags)
        code = stripCData(code);

      // Inject title if the attribute is present
      if ((target.title || '') != '')
        params.title = target.title;

      params['brush'] = brushName;

      code = transformers(code, params);
      matches = match.applyRegexList(code, brush.regexList, params);
      renderer = new Renderer(code, matches, params);

      element = dom.create('div');
      element.innerHTML = renderer.getHtml();

      // id = utils.guid();
      // element.id = highlighters.id(id);
      // highlighters.set(id, element);

      if (params.quickCode)
        dom.attachEvent(dom.findElement(element, '.code'), 'dblclick', dom.quickCodeHandler);

      // carry over ID
      if ((target.id || '') != '')
        element.id = target.id;

      target.parentNode.replaceChild(element, target);
    }
  }
}; // end of sh

/**
 * Displays an alert.
 * @param {String} str String to display.
 */
function alert(str)
{
  window.alert('SyntaxHighlighter\n\n' + str);
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
    for (var brushName in sh.brushes)
    {
      var brush = sh.brushes[brushName],
        aliases = brush.aliases
        ;

      if (aliases == null) {
        continue;
      }

      brush.className = brush.className || brush.aliases[0];
      brush.brushName = brush.className || brushName.toLowerCase();

      for (var i = 0, l = aliases.length; i < l; i++) {
        brushes[aliases[i]] = brushName;
      }
    }

    sh.vars.discoveredBrushes = brushes;
  }

  result = sh.brushes[brushes[alias]];

  if (result == null && showAlert)
    alert(sh.config.strings.noBrush + alias);

  return result;
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

let brushCounter = 0;

export default sh;
export const registerBrush = brush => sh.brushes['brush' + brushCounter++] = brush.default || brush;
export const clearRegisteredBrushes = () => {
  sh.brushes = {};
  brushCounter = 0;
}

/* an EJS hook for `gulp build --brushes` command
 * <%- registerBrushes %>
 */
