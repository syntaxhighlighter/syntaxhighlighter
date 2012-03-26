//
// Begin anonymous function. This is used to contain local scope variables without polutting global scope.
//
if (typeof(SyntaxHighlighter) == 'undefined') var SyntaxHighlighter = function(window) { 

// CommonJS
if (typeof(require) != 'undefined' && typeof(XRegExp) == 'undefined')
	XRegExp = require('XRegExp').XRegExp;

var document       = window.document,
	CLASS_NAME     = 'syntaxhighlighter',
	DOT_CLASS_NAME = '.' + CLASS_NAME,
	COLLAPSED      = 'collapsed'
	;

// Shortcut object which will be assigned to the SyntaxHighlighter variable.
// This is a shorthand for local reference in order to avoid long namespace 
// references to SyntaxHighlighter.whatever...
var sh = {
	/**
	 * SyntaxHighlight comes with a vast number of settings that could be changed to alter and configure
	 * code blocks to do different things. There are per-block settings and global settings. The messages
	 * that are displayed can also be altered in case you want to localize them
	 * 
	 * @author agorbatchev
	 * @id settings
	 */

	/**
	 * #### Defaults
	 *
	 * `SyntaxHighlighter.defaults` holds default values that are used for each highlighted element on
	 * the page. These options are local to each highlighter element and could be changed individually 
	 * via parameters. 
	 *
	 * To set defaults you can either directly change the values in JavaScript like so:
	 *
	 *     SyntaxHighlighter.defaults['gutter']     = false;
	 *     SyntaxHighlighter.defaults['smart-tabs'] = false;
	 *     ...
	 *     SyntaxHighlighter.all();
	 *
	 * #### Parameters
	 *
	 * Parameters allow you to customize each highlighted element individually to your liking. Key/value 
	 * pairs are specified in the format similar to CSS, but instead of the style node attribute, they go
	 * together with the brush declaration into the class attribute.
	 *
	 * In such fashion, you can change any default value from the `SyntaxHighlighter.defaults`.
	 *
	 * @author agorbatchev
	 * @id options
	 */
	defaults : {
		/**
		 * Allows you to change the first (starting) line number.
		 *
		 * @name first-line
		 * @default 1
		 * @author agorbatchev
		 * @id options.firstLine
		 */
		'first-line' : 1,
		
		/**
		 * Pads line numbers. Possible values are:
		 *
		 *  * `false` - don't pad line numbers.
		 *  * `true` - automaticaly pad numbers with minimum required number of leading zeroes.
		 *  * `Number` - length up to which pad line numbers.
		 *
		 * @name pad-line-numbers
		 * @default false
		 * @author agorbatchev
		 * @id options.padLineNumbers
		 */
		'pad-line-numbers' : false,
		
		/**
		 * Allows you to highlight one or more lines to focus user’s attention. When specifying as a 
		 * parameter, you have to pass an array value, like `[ 1, 2, 3 ]` or just an `Number` for a 
		 * single line. If you are changing `SyntaxHighlighter.defaults['highlight']`, you can pass a
		 * `Number` or an `Array` of numbers.
		 *
		 * @name highlight
		 * @default null
		 * @author agorbatchev
		 * @id options.highlight
		 */
		'highlight' : null,
		
		/**
		 * Title to be displayed above the code block.
		 *
		 * @name title
		 * @default null
		 * @author agorbatchev
		 * @id options.title
		 */
		'title' : null,
		
		/**
		 * Smart tabs is a formatting feature that will align tab separated content into vertical
		 * columns. Most modern IDEs and editors do this and it makes the code look neater.
		 *
		 * @name smart-tabs
		 * @default true
		 * @author agorbatchev
		 * @id options.smartTabs
		 */
		'smart-tabs' : true,
		
		/**
		 * Tab size works together with smart tabs feature and determines size of a tab column.
		 * Most common tab sizes are `4`, `8` and `2`.
		 *
		 * @name tab-size
		 * @default 4
		 * @author agorbatchev
		 * @id options.tabSize
		 */
		'tab-size' : 4,
		
		/**
		 * Toggles the gutter with line numbers on and off.
		 *
		 * @name gutter
		 * @default false
		 * @author agorbatchev
		 * @id options.gutter
		 */
		'gutter' : false,
		
		/**
		 * Allows you to turn detection of links in the highlighted element on and off. If the option
		 * is turned off, URLs won’t be clickable.
		 *
		 * @name auto-links
		 * @default true
		 * @author agorbatchev
		 * @id options.autoLinks
		 */
		'auto-links' : true,
		
		/**
		 * Will remove all the common leading spaces from formatted code. This allows you to have your
		 * HTML neatly indented and that extra indentation won't carry over into the highlighted source.
		 *
		 * @name unindent
		 * @default true
		 * @author agorbatchev
		 * @id options.unindent
		 */
		'unindent' : true,

		/**
		 * If user double clicks anywhere on the code, the whole code block will be selected. This allows
		 * users to quickly and easily select the whole source code block and copy it to the clipboard.
		 *
		 * @name quick-code
		 * @default true
		 * @author agorbatchev
		 * @id options.quickCode
		 */
		'quick-code' : true,
		
		/**
		 * Allows you to highlight a mixture of HTML/XML code and another code which is very common in
		 * web development (PHP is a good example of this). Setting this value to `true` requires that 
		 * you have `shBrushXml.js` loaded and that the brush you are using supports this feature.
		 *
		 * @name html-script
		 * @default false
		 * @author agorbatchev
		 * @id options.htmlScript
		 */
		'html-script' : false
	},
	
	/**
	 * `SyntaxHighlighter.config` has configuration values that are common to all highlighted elements 
	 * on the page and don’t make much sense within context of a single highlighted element. These could
	 * be considered as script configuration.
	 *
	 * @author agorbatchev
	 * @id config
	 */
	config : {
		/**
		 * Blogger integration. If you are hosting on blogger.com, you must set this to `true`. Turning
		 * this on will replace each `<br/>` with a new line character.
		 *
		 * @name bloggerMode
		 * @default false
		 * @author agorbatchev
		 * @id config.bloggerMode
		 */
		bloggerMode : false,
		
		/**
		 * If your software adds `<br/>` tags at the end of each line, this option allows you to strip
		 * those out.
		 *
		 * @name stripBrs
		 * @default false
		 * @author agorbatchev
		 * @id config.stripBrs
		 */
		stripBrs : false,
		
		/**
		 * Name of the tag that SyntaxHighlighter will automatically look for.
		 *
		 * @name tagName
		 * @default "pre"
		 * @author agorbatchev
		 * @id config.tagName
		 */
		tagName : 'pre',
	},

	/**
	 * This configuration option allows you to localize your highlighted elements or just change the
	 * displayed strings.
	 *
	 * @author agorbatchev
	 * @id strings
	 */
	strings : {
		/**
		 * Displayed when `collapse` option is set to `true` and no `title` option set.
		 *
		 * @name expandSource
		 * @default "Expand source"
		 * @author agorbatchev
		 * @id strings.expandSource
		 */
		expandSource : 'Expand source',

		/**
		 * Title of any alert window that SyntaxHighlighter might show. Alerts are only displayed in
		 * case of an error.
		 *
		 * @name alert
		 * @default "SyntaxHighlighter\n\n"
		 * @author agorbatchev
		 * @id strings.alert
		 */
		alert : 'SyntaxHighlighter\n\n',

		/**
		 * Message displayed when a required brush isn't found.
		 *
		 * @name noBrush
		 * @default "Can't find brush for: "
		 * @author agorbatchev
		 * @id strings.noBrush
		 */
		noBrush : 'Can\'t find brush for: ',

		/**
		 * Message displayed when requested brush wasn't red for `html-script`.
		 * 
		 * @name brushNotHtmlScript
		 * @author agorbatchev
		 * @id strings.brushNotHtmlScript
		 */
		brushNotHtmlScript : 'Brush wasn\'t red for html-script option: '
	},
	
	/** Internal 'global' variables. */
	vars : {
		space             : '&nbsp;',
		discoveredBrushes : null,
		highlighters      : {},
		css               : null
	},
	
	/** This object is populated by user included external brush files. */
	brushes : {},

	/** Common regular expressions. */
	regexLib : {
		multiLineCComments          : /\/\*[\s\S]*?\*\//gm,
		singleLineCComments         : /\/\/.*$/gm,
		singleLinePerlComments      : /#.*$/gm,
		doubleQuotedString          : /"([^\\"\n]|\\.)*"/g,
		singleQuotedString          : /'([^\\'\n]|\\.)*'/g,
		multiLineDoubleQuotedString : new XRegExp('"([^\\\\"]|\\\\.)*"', 'gs'),
		multiLineSingleQuotedString : new XRegExp("'([^\\\\']|\\\\.)*'", 'gs'),
		xmlComments                 : /(&lt;|<)!--[\s\S]*?--(&gt;|>)/gm,
		url                         : /\w+:\/\/[\w-.\/?%&=:@;#]*/g,

		/** <?= ?> tags. */
		phpScriptTags               : { left: /(&lt;|<)\?(?:=|php)?/g, right: /\?(&gt;|>)/g, 'eof' : true },

		/** <%= %> tags. */
		aspScriptTags               : { left: /(&lt;|<)%=?/g, right: /%(&gt;|>)/g },

		/** <script> tags. */
		scriptScriptTags            : { left: /(&lt;|<)\s*script.*?(&gt;|>)/gi, right: /(&lt;|<)\/\s*script\s*(&gt;|>)/gi }
	},

	/**
	 * Shorthand to highlight all elements on the page that are marked as 
	 * SyntaxHighlighter source code.
	 * 
	 * @param {Object} globalParams		Optional parameters which override element's 
	 * 									parameters. Only used if element is specified.
	 * 
	 * @param {Object} element	Optional element to highlight. If none is
	 * 							provided, all elements in the current document 
	 * 							are highlighted.
	 */ 
	highlight: function(globalParams, element)
	{
		sh.config = merge(sh.config, getDataAttributes(getElementById('syntaxhighlighter')));

		var elements     = findElementsToHighlight(globalParams, element),
			propertyName = 'innerHTML',
			highlighter  = null,
			vars         = sh.vars,
			conf         = sh.config,
			i
			;
			
		if (elements.length === 0) 
			return;

		if(!vars.css)
			vars.css = getSyntaxHighlighterCss();
		
		for (i = 0; i < elements.length; i++) 
		{
			var element   = elements[i],
				target    = element.target,
				params    = element.params,
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
			code = stripCData(code);
				
			// Inject title if the attribute is present
			if ((target.title || '') != '')
				params.title = target.title;
				
			params['brush'] = brushName;
			highlighter.init(params, target.id);
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
		bindReady(function()
		{
			sh.highlight(params);
		});
	}
}; // end of sh

/**
 * Borrowed from jQuery code. This is equivalent to $(callback) or $(document).ready(callback);
 */
function bindReady(callback)
{
	var isReady = false;
	
	function ready()
	{
		if(isReady == false)
			callback();
		
		isReady = true;
	};
	
	function DOMContentLoaded() 
	{
		document.removeEventListener('DOMContentLoaded', DOMContentLoaded, false);
		ready();
	};
	
	// The DOM ready check for Internet Explorer
	function doScrollCheck() 
	{
		try 
		{
			// If IE is used, use the trick by Diego Perini
			// http://javascript.nwbox.com/IEContentLoaded/
			document.documentElement.doScroll('left');
		} 
		catch(e) 
		{
			setTimeout(doScrollCheck, 1);
			return;
		}
		
		// and execute any waiting functions
		ready();
	};
	
	// Mozilla, Opera and webkit nightlies currently support this event
	if(document.addEventListener) 
	{
		// Use the handy event callback
		document.addEventListener('DOMContentLoaded', DOMContentLoaded, false);
		
		// A fallback to window.onload, that will always work
		window.addEventListener('load', ready, false);
	} 
	// If IE event model is used
	else if(document.attachEvent) 
	{
		// ensure firing before onload, maybe late but safe also for iframes
		document.attachEvent('onreadystatechange', DOMContentLoaded);
		
		// A fallback to window.onload, that will always work
		window.attachEvent('onload', ready);

		// If IE and not a frame continually check to see if the document is ready
		var toplevel = false;

		try
		{
			toplevel = window.frameElement == null;
		} 
		catch(e) {}

		if(document.documentElement.doScroll && toplevel) 
			doScrollCheck();
	}
};

/**
 * Finds all elements on the page which should be processes by SyntaxHighlighter.
 *
 * @param {Object} globalParams		Optional parameters which override element's 
 * 									parameters. Only used if element is specified.
 * 
 * @param {Object} element	Optional element to highlight. If none is
 * 							provided, all elements in the current document 
 * 							are returned which qualify.
 *
 * @return {Array}	Returns list of <code>{ target: DOMElement, params: Object }</code> objects.
 */
function findElementsToHighlight(globalParams, element)
{
	var elements = element ? [element] : toArray(document.getElementsByTagName(sh.config.tagName)),
		conf     = sh.config,
		result   = []
		;

	function getAttribute(element, name)
	{
		var result = element.getAttribute(name);
		return result && result.length > 0 ? result : null;
	};

	// support for <SCRIPT TYPE="syntaxhighlighter" /> feature
	elements = elements.concat(getSyntaxHighlighterScriptTags());

	if (elements.length === 0) 
		return result;

	for (var i = 0; i < elements.length; i++) 
	{
		var element = elements[i],
			item = {
				target: element, 
				// local params take precedence over globals
				params: merge(globalParams, parseParams(getAttribute(element, 'data-sh') || getAttribute(element, 'class')))
			}
			;
			
		if (item.params['brush'] == null)
			continue;
			
		result.push(item);
	}
	
	return result;
};

// this function is used by autoloader
sh.findElementsToHighlight = findElementsToHighlight;

/**
 * Finds all syntaxhighlighter css that is currently loaded.
 * @author agorbatchev
 * @date 2012/03/18
 */
function getSyntaxHighlighterCss()
{
	var styles      = document.styleSheets,
		completeCss = '',
		rules,
		cssText,
		i,
		j
		;

	for(i = 0; i < styles.length; i++)
	{
		rules = styles[i].cssRules || [];

		for(j = 0; j < rules.length; j++)
		{
			cssText = rules[j].cssText;

			if(cssText.indexOf('.syntaxhighlighter') >= 0)
				completeCss += cssText + '\n';
		}
	}

	return completeCss;
};

/**
 * Checks if target DOM elements has specified CSS class.
 * @param {DOMElement} target Target DOM element to check.
 * @param {String} className Name of the CSS class to check for.
 * @return {Boolean} Returns true if class name is present, false otherwise.
 */
function hasClass(target, className)
{
	return target.className.indexOf(className) != -1;
};

/**
 * Adds CSS class name to the target DOM element.
 * @param {DOMElement} target Target DOM element.
 * @param {String} className New CSS class to add.
 */
function addClass(target, className)
{
	if (!hasClass(target, className))
		target.className += ' ' + className;
};

/**
 * Removes CSS class name from the target DOM element.
 * @param {DOMElement} target Target DOM element.
 * @param {String} className CSS class to remove.
 */
function removeClass(target, className)
{
	target.className = target.className.replace(className, '');
};

/**
 * Converts the source to array object. Mostly used for function arguments and 
 * lists returned by getElementsByTagName() which aren't Array objects.
 * @param {List} source Source list.
 * @return {Array} Returns array.
 */
function toArray(source)
{
	var result = [];
	
	for (var i = 0; i < source.length; i++) 
		result.push(source[i]);
		
	return result;
};

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
 * Finds Highlighter instance by ID.
 * @param {String} highlighterId Highlighter ID.
 * @return {Highlighter} Returns instance of the highlighter.
 */
function getHighlighterById(id)
{
	return sh.vars.highlighters[id];
};

/**
 * Shortcut for document.createElement()
 * @date 2010/12/17
 */
function createElement(name)
{
	return document.createElement(name);
};

/**
 * Shortcut for document.getElementById()
 * @date 2010/12/17
 */
function getElementById(id)
{
	return document.getElementById(id);
};

/**
 * Returns IFRAME's window.document.
 * @date 2010/12/17
 */
function getIframeDocument(iframe)
{
	return iframe.contentDocument;
};

/**
 * Finds highlighter's DIV container.
 * @param {String} highlighterId Highlighter ID.
 * @return {Element} Returns highlighter's DIV element.
 */
function getHighlighterDivById(id)
{
	var div = getElementById(id);
	
	if (div.tagName == 'IFRAME')
		div = getIframeDocument(div).getElementById(id);
	
	return div;
};

/**
 * Stores highlighter so that getHighlighterById() can do its thing. Each
 * highlighter must call this method to preserve itself.
 * @param {Highilghter} highlighter Highlighter instance.
 */
function storeHighlighter(highlighter)
{
	sh.vars.highlighters[highlighter.id] = highlighter;
};

/**
 * Looks for a child or parent node which has specified classname.
 * Equivalent to jQuery's $(container).find(".className")
 * @param {Element} target Target element.
 * @param {String} search Class name or node name to look for.
 * @param {Boolean} reverse If set to true, will go up the node tree instead of down.
 * @return {Element} Returns found child or parent element on null.
 */
function findElement(target, search, reverse /* optional */)
{
	if (target == null)
		return null;
		
	var nodes			= reverse != true ? target.childNodes : [ target.parentNode ],
		propertyToFind	= { '#' : 'id', '.' : 'className' }[search.substr(0, 1)] || 'nodeName',
		expectedValue,
		found
		;

	expectedValue = propertyToFind != 'nodeName'
		? search.substr(1)
		: search.toUpperCase()
		;
		
	// main return of the found node
	if ((target[propertyToFind] || '').indexOf(expectedValue) != -1)
		return target;
	
	for (var i = 0; nodes && i < nodes.length && found == null; i++)
		found = findElement(nodes[i], search, reverse);
	
	return found;
};

/**
 * Looks for a parent node which has specified classname.
 * This is an alias to <code>findElement(container, className, true)</code>.
 * @param {Element} target Target element.
 * @param {String} className Class name to look for.
 * @return {Element} Returns found parent element on null.
 */
function findParentElement(target, className)
{
	return findElement(target, className, true);
};

/**
 * Finds an index of element in the array.
 * @ignore
 * @param {Object} searchElement
 * @param {Number} fromIndex
 * @return {Number} Returns index of element if found; -1 otherwise.
 */
function indexOf(array, searchElement, fromIndex)
{
	fromIndex = Math.max(fromIndex || 0, 0);

	for (var i = fromIndex; i < array.length; i++)
		if(array[i] == searchElement)
			return i;
	
	return -1;
};

/**
 * Generates a unique element ID.
 */
function guid(prefix)
{
	return (prefix || '') + Math.round(Math.random() * 1000000).toString();
};

/**
 * Merges two objects. Values from obj2 override values in obj1.
 * Function is NOT recursive and works only for one dimensional objects.
 * @param {Object} obj1 First object.
 * @param {Object} obj2 Second object.
 * @return {Object} Returns combination of both objects.
 */
function merge(obj1, obj2)
{
	var result = {}, name;

	for (name in obj1) 
		result[name] = obj1[name];
	
	for (name in obj2) 
		result[name] = obj2[name];
		
	return result;
};

/**
 * Attempts to convert string to boolean.
 * @param {String} value Input string.
 * @return {Boolean} Returns true if input was "true", false if input was "false" and value otherwise.
 */
function toBoolean(value)
{
	var result = { "true" : true, "false" : false }[value];
	return result == null ? value : result;
};

/**
 * Adds event handler to the target object.
 * @param {Object} obj		Target object.
 * @param {String} type		Name of the event.
 * @param {Function} func	Handling function.
 */
function attachEvent(obj, type, func, scope)
{
	function handler(e)
	{
		e = e || window.event;
		
		if (!e.target)
		{
			e.target = e.srcElement;
			e.preventDefault = function()
			{
				this.returnValue = false;
			};
		}
			
		func.call(scope || window, e);
	};
	
	if (obj.attachEvent) 
	{
		obj.attachEvent('on' + type, handler);
	}
	else 
	{
		obj.addEventListener(type, handler, false);
	}
};

/**
 * Displays an alert.
 * @param {String} str String to display.
 */
function alert(str)
{
	window.alert(sh.strings.alert + str);
};

/**
 * Finds a brush by its alias.
 *
 * @param {String} alias		Brush alias.
 * @param {Boolean} showAlert	Suppresses the alert if false.
 * @return {Brush}				Returns bursh constructor if found, null otherwise.
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
			
			for (var i = 0; i < aliases.length; i++) 
				brushes[aliases[i]] = brush;
		}
		
		sh.vars.discoveredBrushes = brushes;
	}
	
	result = sh.brushes[brushes[alias]];

	if (result == null && showAlert)
		alert(sh.strings.noBrush + alias);
	
	return result;
};

/**
 * Executes a callback on each line and replaces each line with result from the callback.
 * @param {Object} str			Input string.
 * @param {Object} callback		Callback function taking one string argument and returning a string.
 */
function eachLine(str, callback)
{
	var lines = splitLines(str),
		i
		;
	
	for (i = 0; i < lines.length; i++)
		lines[i] = callback(lines[i], i);
		
	// include \r to enable copy-paste on windows (ie8) without getting everything on one line
	return lines.join('\r\n');
};

/**
 * This is a special trim which only removes first and last empty lines
 * and doesn't affect valid leading space on the first line.
 * 
 * @param {String} str   Input string
 * @return {String}      Returns string without empty first and last lines.
 */
function trimFirstAndLastLines(str)
{
	return str.replace(/^[ ]*[\n]+|[\n]*[ ]*$/g, '');
};

function getDataAttributes(node)
{
	if(!node)
		return {};

	var result     = {},
		attributes = node.attributes,
		item,
		name,
		value,
		i
		;

	for(i = 0; i < attributes.length; i++)
	{
		item = attributes[i];
		name = item.name;

		if(name.indexOf('data-') === 0)
		{
			name = name.substr(5).replace(/-(\w)/g, function(match, letter)
			{
				return letter.toUpperCase();
			});

			value = item.value || '';
			
			// auto convert true/false to boolean
			if(/^(true|false)$/.test(value))
				value = value === 'true';

			result[name] = value;
		}
	}

	return result;
};

/**
 * Parses key/value pairs into hash object.
 * 
 * Understands the following formats:
 * - name: word;
 * - name: [word, word];
 * - name: "string";
 * - name: 'string';
 * 
 * For example:
 *   name1: value; name2: [value, value]; name3: 'value'
 *   
 * @param {String} str    Input string.
 * @return {Object}       Returns deserialized object.
 */
function parseParams(str)
{
	var match, 
		result = {},
		arrayRegex = new XRegExp("^\\[(?<values>(.*?))\\]$"),
		regex = new XRegExp(
			"(?<name>[\\w-]+)" +
			"\\s*:\\s*" +
			"(?<value>" +
				"[\\w-%#]+|" +		// word
				"\\[.*?\\]|" +		// [] array
				'".*?"|' +			// "" string
				"'.*?'" +			// '' string
			")\\s*;?",
			"g"
		)
		;

	while ((match = regex.exec(str)) != null) 
	{
		var value = match.value.replace(/^['"]|['"]$/g, ''); // strip quotes from end of strings 
		
		// try to parse array value
		if (value != null && arrayRegex.test(value))
		{
			var m = arrayRegex.exec(value);
			value = m.values.length > 0 ? m.values.split(/\s*,\s*/) : [];
		}
		
		result[match.name] = value;
	}
	
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
		
		for (var i = 0; i < m.length - 1; i++)
			spaces += sh.vars.space;
		
		return spaces + ' ';
	});

	// Split each line and apply <span class="...">...</span> to them so that
	// leading spaces aren't included.
	if (css != null) 
		str = eachLine(str, function(line)
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
 * @param {Number} number	Number to pad.
 * @param {Number} length	Max string length with.
 * @return {String}			Returns a string padded with proper amount of '0'.
 */
function padNumber(number, length)
{
	var result = number.toString();
	
	while (result.length < length)
		result = '0' + result;
	
	return result;
};

/**
 * Replaces tabs with spaces.
 * 
 * @param {String} code		Source code.
 * @param {Number} tabSize	Size of the tab.
 * @return {String}			Returns code with all tabs replaces by spaces.
 */
function processTabs(code, tabSize)
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
function processSmartTabs(code, tabSize)
{
	var lines  = splitLines(code),
		tab    = '\t',
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

/**
 * Performs various string fixes based on configuration.
 */
function fixInputString(str)
{
	var br = /<br\s*\/?>|&lt;br\s*\/?&gt;/gi;
	
	if (sh.config.bloggerMode == true)
		str = str.replace(br, '\n');

	if (sh.config.stripBrs == true)
		str = str.replace(br, '');
		
	return str;
};

/**
 * Removes all white space at the begining and end of a string.
 * 
 * @param {String} str   String to trim.
 * @return {String}      Returns string without leading and following white space characters.
 */
function trim(str)
{
	return str.replace(/^\s+|\s+$/g, '');
};

/**
 * Unindents a block of text by the lowest common indent amount.
 * @param {String} str   Text to unindent.
 * @return {String}      Returns unindented text block.
 */
function unindent(str)
{
	var lines   = splitLines(fixInputString(str)),
		indents = new Array(),
		regex   = /^\s*/,
		min     = 1000
		;
	
	// go through every line and check for common number of indents
	for (var i = 0; i < lines.length && min > 0; i++) 
	{
		var line = lines[i];
		
		if (trim(line).length == 0) 
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
		for (var i = 0; i < lines.length; i++) 
			lines[i] = lines[i].substr(min);
	
	return lines.join('\n');
};

/**
 * Callback method for Array.sort() which sorts matches by
 * index position and then by length.
 * 
 * @param {Match} m1	Left object.
 * @param {Match} m2    Right object.
 * @return {Number}     Returns -1, 0 or -1 as a comparison result.
 */
function matchesSortCallback(m1, m2)
{
	// sort matches by index first
	if(m1.index < m2.index)
		return -1;
	else if(m1.index > m2.index)
		return 1;
	else
	{
		// if index is the same, sort by length
		if(m1.length < m2.length)
			return -1;
		else if(m1.length > m2.length)
			return 1;
	}
	
	return 0;
};

/**
 * Executes given regular expression on provided code and returns all
 * matches that are found.
 * 
 * @param {String} code    Code to execute regular expression on.
 * @param {Object} regex   Regular expression item info from <code>regexList</code> collection.
 * @return {Array}         Returns a list of Match objects.
 */ 
function getMatches(code, regexInfo)
{
	function defaultAdd(match, regexInfo)
	{
		return match[0];
	};
	
	var index = 0,
		match = null,
		matches = [],
		func = regexInfo.func ? regexInfo.func : defaultAdd
		;
	
	while((match = regexInfo.regex.exec(code)) != null)
	{
		var resultMatch = func(match, regexInfo);
		
		if (typeof(resultMatch) == 'string')
			resultMatch = [new sh.Match(resultMatch, match.index, regexInfo.css)];

		matches = matches.concat(resultMatch);
	}
	
	return matches;
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
 * Finds all <SCRIPT TYPE="syntaxhighlighter" /> elementss.
 * @return {Array} Returns array of all found SyntaxHighlighter tags.
 */
function getSyntaxHighlighterScriptTags()
{
	var tags   = document.getElementsByTagName('script'),
		result = [],
		i
		;
	
	for (i = 0; i < tags.length; i++)
		if (tags[i].type == CLASS_NAME)
			result.push(tags[i]);
			
	return result;
};

/**
 * Strips <![CDATA[]]> from <SCRIPT /> content because it should be used
 * there in most cases for XHTML compliance.
 * @param {String} original	Input code.
 * @return {String} Returns code without leading <![CDATA[]]> tags.
 */
function stripCData(original)
{
	var left        = '<![CDATA[',
		right       = ']]>',
		copy        = trim(original), // for some reason IE inserts some leading blanks here
		changed     = false,
		leftLength  = left.length,
		rightLength = right.length,
		copyLength
		;
	
	if (copy.indexOf(left) == 0)
	{
		copy = copy.substring(leftLength);
		changed = true;
	}
	
	copyLength = copy.length;
	
	if (copy.indexOf(right) == copyLength - rightLength)
	{
		copy = copy.substring(0, copyLength - rightLength);
		changed = true;
	}
	
	return changed ? copy : original;
};

// functionality for the mouse hover over the whole highlighter block

var mouseTimeoutId = 0;

function mouseTimeoutAction(e, func)
{
	var highlighterDiv = findParentElement(e.target, DOT_CLASS_NAME);
	clearTimeout(mouseTimeoutId);
	mouseTimeoutId = setTimeout(function() { func(highlighterDiv, 'hover') }, 200);
};

function mouseOverHandler(e)
{
	mouseTimeoutAction(e, addClass);
};

function mouseOutHandler(e)
{
	mouseTimeoutAction(e, removeClass);
};

/**
 * Match object.
 */
sh.Match = function(value, index, css)
{
	var self = this;

	self.value     = value;
	self.index     = index;
	self.length    = value.length;
	self.css       = css;
	self.brushName = null;
};

sh.Match.prototype.toString = function()
{
	return this.value;
};

/**
 * Simulates HTML code with a scripting language embedded.
 * 
 * @param {String} scriptBrushName Brush name of the scripting language.
 */
sh.HtmlScript = function(scriptBrushName)
{
	var brushClass      = findBrush(scriptBrushName),
		xmlBrush        = new sh.brushes.Xml(),
		bracketsRegex   = null,
		ref             = this,
		methodsToExpose = 'getDiv getHtml init'.split(' '),
		scriptBrush,
		i
		;

	if (brushClass == null)
		return;
	
	scriptBrush = new brushClass();
	
	for(i = 0; i < methodsToExpose.length; i++)
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
		alert(sh.strings.brushNotHtmlScript + scriptBrushName);
		return;
	}
	
	xmlBrush.regexList.push(
		{ regex: scriptBrush.htmlScript.code, func: process }
	);
	
	function offsetMatches(matches, offset)
	{
		for (var j = 0; j < matches.length; j++) 
			matches[j].index += offset;
	}
	
	function process(match, info)
	{
		var code       = match.code,
			matches    = [],
			regexList  = scriptBrush.regexList,
			offset     = match.index + match.left.length,
			htmlScript = scriptBrush.htmlScript,
			result
			;

		// add all matches from the code
		for (var i = 0; i < regexList.length; i++)
		{
			result = getMatches(code, regexList[i]);
			offsetMatches(result, offset);
			matches = matches.concat(result);
		}
		
		// add left script bracket
		if (htmlScript.left != null && match.left != null)
		{
			result = getMatches(match.left, htmlScript.left);
			offsetMatches(result, match.index);
			matches = matches.concat(result);
		}
		
		// add right script bracket
		if (htmlScript.right != null && match.right != null)
		{
			result = getMatches(match.right, htmlScript.right);
			offsetMatches(result, match.index + match[0].lastIndexOf(match.right));
			matches = matches.concat(result);
		}
		
		for (var j = 0; j < matches.length; j++)
			matches[j].brushName = brushClass.brushName;
			
		return matches;
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
	 * @param {String} name				Name of the parameter.
	 * @param {Object} defaultValue		Default value.
	 * @return {Object}					Returns found value or default value otherwise.
	 */
	getParam: function(name, defaultValue)
	{
		var result = this.params[name];
		return toBoolean(result == null ? defaultValue : result);
	},
	
	/**
	 * Applies all regular expression to the code and stores all found
	 * matches in the `this.matches` array.
	 * @param {Array} regexList		List of regular expressions.
	 * @param {String} code			Source code.
	 * @return {Array}				Returns list of matches.
	 */
	findMatches: function(regexList, code)
	{
		var result = [];
		
		if (regexList != null)
			for (var i = 0; i < regexList.length; i++) 
				// BUG: length returns len+1 for array if methods added to prototype chain (oising@gmail.com)
				if (typeof (regexList[i]) == "object")
					result = result.concat(getMatches(code, regexList[i]));
		
		// sort and remove nested the matches
		return this.removeNestedMatches(result.sort(matchesSortCallback));
	},
	
	/**
	 * Checks to see if any of the matches are inside of other matches. 
	 * This process would get rid of highligted strings inside comments, 
	 * keywords inside strings and so on.
	 */
	removeNestedMatches: function(matches)
	{
		// Optimized by Jose Prado (http://joseprado.com)
		for (var i = 0; i < matches.length; i++) 
		{ 
			if (matches[i] === null)
				continue;
			
			var itemI = matches[i],
				itemIEndPos = itemI.index + itemI.length
				;
			
			for (var j = i + 1; j < matches.length && matches[i] !== null; j++) 
			{
				var itemJ = matches[j];
				
				if (itemJ === null) 
					continue;
				else if (itemJ.index > itemIEndPos) 
					break;
				else if (itemJ.index == itemI.index && itemJ.length > itemI.length)
					matches[i] = null;
				else if (itemJ.index >= itemI.index && itemJ.index < itemIEndPos) 
					matches[j] = null;
			}
		}
		
		return matches;
	},
	
	/**
	 * Creates an array containing integer line numbers starting from the 'first-line' param.
	 * @return {Array} Returns array of integers.
	 */
	figureOutLineNumbers: function(code)
	{
		var lines     = [],
			firstLine = parseInt(this.getParam('first-line'))
			;
		
		eachLine(code, function(line, index)
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
		
		return indexOf(list, lineNumber.toString()) != -1;
	},
	
	/**
	 * Generates HTML markup for a single line of code while determining alternating line style.
	 * @param {Integer} lineNumber	Line number.
	 * @param {String} code Line	HTML markup.
	 * @return {String}				Returns HTML markup.
	 */
	getLineHtml: function(lineIndex, lineNumber, code)
	{
		var classes = [
			'line',
			'number' + lineNumber,
			'index' + lineIndex,
			'alt' + (lineNumber % 2 == 0 ? 1 : 2)
		];
		
		if (this.isLineHighlighted(lineNumber))
		 	classes.push('highlighted');
		
		return '<div class="' + classes.join(' ') + '">' + code + '\n</div>';
	},
	
	/**
	 * Generates HTML markup for line number column.
	 * @param {String} code			Complete code HTML markup.
	 * @return {String}				Returns HTML markup.
	 */
	getLineNumbersHtml: function(code)
	{
		var self      = this,
			html      = '',
			count     = splitLines(code).length,
			firstLine = parseInt(self.getParam('first-line')),
			pad       = self.getParam('pad-line-numbers'),
			lineNumber,
			i
			;
		
		if (pad == true)
			pad = (firstLine + count - 1).toString().length;
		else if (isNaN(pad) == true)
			pad = 0;
			
		for (i = 0; i < count; i++)
		{
			lineNumber = firstLine + i;
			html += self.getLineHtml(i, lineNumber, padNumber(lineNumber, pad) + '&nbsp;');
		}
		
		return html;
	},
	
	/**
	 * Splits block of text into individual DIV lines.
	 * @param {String} code			Code to highlight.
	 * @param {Array} lineNumbers	Calculated line numbers.
	 * @return {String}				Returns highlighted code in HTML form.
	 */
	getCodeLinesHtml: function(html, lineNumbers)
	{
		html = trim(html);
		
		var self      = this,
			lines     = splitLines(html),
			padLength = self.getParam('pad-line-numbers'),
			firstLine = parseInt(self.getParam('first-line')),
			html      = '',
			brushName = self.getParam('brush'),
			space     = sh.vars.space,
			i
			;

		for (i = 0; i < lines.length; i++)
		{
			var line       = lines[i],
				indent     = /^(&nbsp;|\s)+/.exec(line),
				spaces     = null,
				lineNumber = lineNumbers ? lineNumbers[i] : firstLine + i
				;

			if (indent != null)
			{
				spaces = indent[0].toString();
				line   = line.substr(spaces.length);
				spaces = spaces.replace(' ', space);
			}

			line = trim(line);
			
			if (line.length == 0)
				line = space;
			
			html += self.getLineHtml(
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
	 * @param {String} code		Source code to process matches in.
	 * @param {Array} matches	Discovered regex matches.
	 * @return {String} Returns formatted HTML with processed mathes.
	 */
	getMatchesHtml: function(code, matches)
	{
		var pos       = 0,
			result    = '',
			brushName = this.getParam('brush', '')
			;
		
		function getBrushNameCss(match)
		{
			var result = match ? (match.brushName || brushName) : brushName;
			return result ? result + ' ' : '';
		};
		
		// Finally, go through the final list of matches and pull the all
		// together adding everything in between that isn't a match.
		for (var i = 0; i < matches.length; i++) 
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
		var html    = '',
			classes = [ CLASS_NAME ],
			self    = this,
			title   = self.getParam('title'),
			gutter  = self.getParam('gutter'),
			tabSize = self.getParam('tab-size'),
			matches,
			lineNumbers
			;
		
		if (self.getParam('collapse'))
		{
			classes.push(COLLAPSED);

			if (!title)
			{
				title = sh.strings.expandSource;
				classes.push('notitle');
			}
		}
		
		if (!gutter)
			classes.push('nogutter');

		// add brush alias to the class name for custom CSS
		classes.push(self.getParam('brush'));

		code = trimFirstAndLastLines(code)
			.replace(/\r/g, ' ') // IE lets these buggers through
			;

		// replace tabs with spaces
		code = self.getParam('smart-tabs')
			? processSmartTabs(code, tabSize)
			: processTabs(code, tabSize)
			;

		// unindent code by the common indentation
		if (self.getParam('unindent'))
			code = unindent(code);

		if (gutter)
			lineNumbers = self.figureOutLineNumbers(code);
		
		// find matches in the code using brushes regex list
		matches = self.findMatches(self.regexList, code);
		// processes found matches into the html
		html = self.getMatchesHtml(code, matches);
		// finally, split all lines so that they wrap well
		html = self.getCodeLinesHtml(html, lineNumbers);

		// finally, process the links
		if (self.getParam('auto-links'))
			html = processUrls(html);
		
		if (typeof(navigator) != 'undefined' && navigator.userAgent && navigator.userAgent.match(/MSIE/))
			classes.push('ie');
		
		html = (
			'<div id="' + self.id + '" class="' + classes.join(' ') + '">'
				+ '<table border="0" cellpadding="0" cellspacing="0">'
					+ self.getTitleHtml(title)
					+ '<tbody>'
						+ '<tr>'
							+ (gutter ? '<td class="gutter" align="right"><code>' + self.getLineNumbersHtml(code) + '</code></td>' : '')
							+ '<td class="code">'
								+ '<div class="container"><code>'
									+ html
								+ '</code></div>'
							+ '</td>'
						+ '</tr>'
					+ '</tbody>'
				+ '</table>'
				+ '<a href="http://syntaxhighlighterjs.com?rel=' + window.location.toString() + '" class="about">awesome by syntaxhighlighter.js</a>'
			+ '</div>'
		);
			
		return html;
	},
	
	/**
	 * Highlights the code and returns complete HTML.
	 * @param {String} code     Code to highlight.
	 * @return {Element}        Returns container DIV element with all markup.
	 */
	getDiv: function(code)
	{
		code = code || '';

		var self   = this,
			vars   = sh.vars,
			html   = self.getHtml(code),
			iframe = createElement('iframe')
			;

		self.code = code;
			
		iframe.className = CLASS_NAME + '_iframe';
		iframe.id        = self.id;
		
		iframe.setAttribute('frameBorder', '0');
		iframe.setAttribute('allowTransparency', 'true');
		
		function loop()
		{
			var doc = getIframeDocument(iframe);
			
			// loop until the iframe document is available
			if (!doc)
				return setTimeout(loop, 10);
			
			var style = doc.createElement('style'),
				body  = doc.body,
				div,
				container
				;

			function updateHeight()
			{
				iframe.setAttribute('style', 'height:' + parseInt(body.offsetHeight) + 'px !important');
			};

			body.innerHTML = html;
			style.appendChild(doc.createTextNode(vars.css));
			body.appendChild(style);
			body.setAttribute('style', 'margin:0;padding:0;overflow:hidden');
			updateHeight();

			div       = getHighlighterDivById(self.id);
			container = findElement(div, '.container');

			if (self.getParam('quick-code'))
				attachEvent(findElement(div, '.code'), 'dblclick', function(e)
				{
					if(!window.getSelection)
						return;

					e.preventDefault();

					var selection = iframe.contentWindow.getSelection(),
						range     = iframe.contentDocument.createRange()
						;

					range.selectNode(container);
					selection.removeAllRanges();
					selection.addRange(range);
				});

			if (self.getParam('collapse'))
				attachEvent(div, 'click', function(e)
				{
					if (hasClass(div, COLLAPSED))
					{
						removeClass(div, COLLAPSED);
						updateHeight();
					}
				});
			
			attachEvent(div, 'mouseover', mouseOverHandler);
			attachEvent(div, 'mouseout', mouseOutHandler);

			attachEvent(iframe.contentWindow, 'resize', function(e)
			{
				var box = container.getBoundingClientRect();
				container.style.width = (div.offsetWidth - box.left) + 'px';
			});
		};
	
		loop();
		
		return iframe;
	},
	
	/**
	 * Initializes the highlighter/brush.
	 *
	 * Constructor isn't used for initialization so that nothing executes during necessary
	 * `new SyntaxHighlighter.Highlighter()` call when setting up brush inheritence.
	 *
	 * @param {Hash} params Highlighter parameters.
	 * @param {String} elementId Optional element ID that would be used in place of autogenerated one.
	 */
	init: function(params, elementId)
	{
		this.id = elementId || ('highlighter_' + guid());
		
		// register this instance in the highlighters list
		storeHighlighter(this);
		
		// local params take precedence over defaults
		this.params = merge(sh.defaults, params || {})
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
			code : new XRegExp(
				"(?<left>" + regexGroup.left.source + ")" +
				"(?<code>.*?)" +
				"(?<right>" + regex.end + ")",
				"sgi"
				)
		};
	}
}; // end of Highlighter

return sh;
}(window); // end of anonymous function

// CommonJS
typeof(exports) != 'undefined' ? exports.SyntaxHighlighter = SyntaxHighlighter : null;
