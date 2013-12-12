;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{       
                var datatypes =	'boolean char integer string';
		var keywords =	"and array begin case const div do downto else end file for forward " +
                       "function goto if in label mod nil not of or packed procedure " +
                       "program record repeat set then to type until var while with";
		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' },		// one line comments
			{ regex: /\(\*[\s\S]*?\*\)/gm,                                  css: 'comments' },	 	// multiline comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },		// strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },		// strings
			{ regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gi,			css: 'value' },			// numbers
			{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'color1 bold' },
                        { regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' }		
			];
			
		this.forHtmlScript(SyntaxHighlighter.regexLib.aspScriptTags);
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['pascal', 'pas'];

	SyntaxHighlighter.brushes.Pascal = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
