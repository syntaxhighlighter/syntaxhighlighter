;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		this.regexList = [
			{ regex: /^\+\+\+ .*$/gm,	css: 'color2' },	// new file
			{ regex: /^\-\-\- .*$/gm,	css: 'color2' },	// old file
			{ regex: /^\s.*$/gm,			css: 'color1' },
			{ regex: /^@@.*@@.*$/gm,	css: 'variable' },	// location
			{ regex: /^\+[^\+]{1}.*$/gm,	css: 'string' },
			{ regex: /^\-[^\-]{1}.*$/gm,	css: 'comments' }
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['diff', 'patch'];

	SyntaxHighlighter.brushes.Diff = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
