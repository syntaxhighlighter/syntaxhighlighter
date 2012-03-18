;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		// Contributed by s-han.lee
	
		var keywords =	'nil t defun list setq eval setf set let equal lambda funcall when' +
                        'cond loop case if declare method export import print equal concat cons';
        
		this.regexList = [
			{ regex: /;.*/gi,                                                   css: 'comments' },	// one line comments
			{ regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,	css: 'string' },	// multi-line strings
			{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,    css: 'string' },	// double-quoted string
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,				css: 'string' },	// strings
			{ regex: /0x[a-f0-9]+|-?\d+(\.\d+)?/gi,								css: 'variable' },		// numbers
			{ regex: /\(|\)/gi,                                                 css: 'variable bold' },		// lisp stmt
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'),				css: 'keyword' },	// keywords
			];
	}

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['lisp'];

	SyntaxHighlighter.brushes.lisp = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
