SyntaxHighlighter.brushes.AS3 = function()
{
	// Created by Peter Atoria @ http://iAtoria.com
	
	var inits 	 =  'class interface function package';
	
	var keywords =	'-Infinity ...rest Array as AS3 Boolean break case catch const continue Date decodeURI ' + 
					'decodeURIComponent default delete do dynamic each else encodeURI encodeURIComponent escape ' + 
					'extends false final finally flash_proxy for get if implements import in include Infinity ' + 
					'instanceof int internal is isFinite isNaN isXMLName label namespace NaN native new null ' + 
					'Null Number Object object_proxy override parseFloat parseInt private protected public ' + 
					'return set static String super switch this throw true try typeof uint undefined unescape ' + 
					'use void while with'
					;
	
	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' },		// one line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' },		// multiline comments
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },		// double quoted strings
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },		// single quoted strings
		{ regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gi,				css: 'value' },			// numbers
		{ regex: new RegExp(this.getKeywords(inits), 'gm'),			css: 'color3' },		// initializations
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' },		// keywords
		{ regex: new RegExp('var', 'gm'),							css: 'variable' },		// variable
		{ regex: new RegExp('trace', 'gm'),							css: 'color1' }			// trace
		];
	
	this.forHtmlScript(SyntaxHighlighter.regexLib.scriptScriptTags);
};

SyntaxHighlighter.brushes.AS3.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.AS3.aliases	= ['actionscript3', 'as3'];
