###
By Rod Vagg <rod@vagg.org> / @rvagg / http://rod.vagg.org
Released under the do-what-you-like-,-attribution-would-be-nice-,-but-please-don't-pass-off-as-your-own-work licence.
Compiled from CoffeeScript, see http://rod.vagg.org/2011/02/coffeescript-brush-for-syntaxhighlighter/
###

# Compile with: coffee -c shBrushCoffeeScript.coffee 

if typeof(require) isnt 'undefined' then @SyntaxHighlighter = require('shCore').SyntaxHighlighter
if not @SyntaxHighlighter then return

Brush = ->
	jsKeywords = 'if else new return try catch finally throw break continue for in while delete instanceof typeof switch super extends class case default do function var void with const let debugger enum export import native __extends __hasProp'
	csKeywords = 'then unless and or is isnt not of by where when until'
	keywords = jsKeywords + ' ' + csKeywords

	@regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLinePerlComments, css: 'comments' },
		# pass-through comment block
		{ regex: /\#\#\#[\s\S]*?\#\#\#/gm, css: 'comments' },

		{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString, css: 'string' },
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string' },
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString, css: 'string' },
		# heredocs
		{ regex: /\'\'\'[\s\S]*?\'\'\'/gm, css: 'string' },
		# extended regular expressions
		{ regex: /\/\/\/[\s\S]*?\/\/\//gm, css: 'string' },

		{ regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gmi, css: 'value' },

		# I'm not sure whether these next two should be variables or 'color1' or something else
		# @ & this variables
		{ regex: /(@[\w._]*|this[\w._]*)/g, css: 'variable bold' },
		# prototype references
		{ regex: /(([\w._]+)::([\w._]*))/g, css: 'variable bold' },

		# variables when used in assignment
		{ regex: /([\w._]+)\s*(?=\=)/g, css: 'variable bold' },

		# operators and other punctuational syntax
		{ regex: /(-&gt;|->|=&gt;|=>|===|==|=|>|&gt;|<|&lt;|\.\.\.|&&|&amp;&amp;|\|\||\!\!|\!|\+\+|\+|--|-|\[|\]|\(|\)|\{|\})|\?|\/|\*|\%/g, css: 'keyword' },
		{ regex: new RegExp(@getKeywords(keywords), 'gm'), css: 'keyword' },
	]
	undefined

Brush:: = new @SyntaxHighlighter.Highlighter()
Brush.aliases = [ 'coffeescript', 'CoffeeScript', 'coffee' ];
@SyntaxHighlighter.brushes.CoffeeScript = Brush;
if typeof(exports) isnt 'undefined' then exports.Brush = Brush
