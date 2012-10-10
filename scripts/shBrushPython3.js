;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		// Contributed by Gheorghe Milas and Ahmad Sherif

		var keywords = 'and as assert break class continue def del elif else ' +
					   'except finally for from global if import in is ' +
					   'lambda nonlocal not or pass raise return try while ' +
					   'with yield';

		var funcs = 'abs all any ascii bin bool bytearray bytes callable ' +
					'chr classmethod compile complex delattr dict dir divmod ' +
					'enumerate eval exec filter float format frozenset ' +
					'getattr globals hasattr hash help hex id input int ' +
					'isinstance issubclass iter len list locals map max ' +
					'memoryview min next object oct open ord pow print ' +
					'property range repr reversed round set setattr slice ' +
					'sorted staticmethod str sum super tuple type vars zip ' +
					'__import__';

		var special = 'False True None NotImplemented Ellipsis self cls ' +
					  '__\\w+__';

		this.regexList = [
				{ regex: SyntaxHighlighter.regexLib.singleLinePerlComments, css: 'comments' },
				{ regex: /^\s*@\w[\.\w]*/gm,								css: 'decorator' },
				{ regex: /(['\"]{3})([^\1])*?\1/gm, 						css: 'comments' },
				{ regex: /[bru]{0,2}"(?!")(?:\.|\\\"|[^\""\n])*"/gmi, 		css: 'string' },
				{ regex: /[bru]{0,2}'(?!')(?:\.|(\\\')|[^\''\n])*'/gmi, 	css: 'string' },
				{ regex: /\b\d+\.?[-\w]*/g, 								css: 'value' },
				{ regex: new RegExp(this.getKeywords(funcs), 'gm'),			css: 'functions' },
				{ regex: new RegExp(this.getKeywords(keywords), 'gm'), 		css: 'keyword' },
				{ regex: new RegExp(this.getKeywords(special), 'gm'), 		css: 'color1' }
				];

		this.forHtmlScript(SyntaxHighlighter.regexLib.aspScriptTags);
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['py3', 'py3k', 'python3'];

	SyntaxHighlighter.brushes.Python3 = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
