;(function()
{
  // CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		var keywords =	"and array begin case const div do downto else end file for forward integer " +
                       "boolean char function goto if in label mod nil not of or packed procedure " +
                       "program record repeat set string then to type until var while with";
		this.regexList = [
				{ regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' }
				];
			
		this.forHtmlScript(SyntaxHighlighter.regexLib.aspScriptTags);
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['pascal', 'pas'];

	SyntaxHighlighter.brushes.Pascal = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
