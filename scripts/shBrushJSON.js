;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
    var keywords =	'true false null';

    this.regexList = [
      { regex: SyntaxHighlighter.regexLib.doubleQuotedString,					css: 'string' },			// strings
      { regex: SyntaxHighlighter.regexLib.singleQuotedString,					css: 'string' },			// strings
      { regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' }
		];
	};
		
	Brush.prototype	= new SyntaxHighlighter.Highlighter();
  Brush.aliases	= ['json'];
  
  SyntaxHighlighter.brushes.JSON = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
