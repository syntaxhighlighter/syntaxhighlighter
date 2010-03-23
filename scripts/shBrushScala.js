SyntaxHighlighter.brushes.Scala = function()
{
	// Contributed by Yegor Jbanov and David Bernard.
	
	var keywords =	'val sealed case def true trait implicit forSome import match object null finally super ' +
	                'override try lazy for var catch throw type extends class while with new final yield abstract ' +
	                'else do if return protected private this package false';

	var keyops =	'[_:=><%#@]+';

	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,			css: 'comments' },	// one line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,				css: 'comments' },	// multiline comments
		{ regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,	css: 'string' },	// multi-line strings
		{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,    css: 'string' },	// double-quoted string
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString,				css: 'string' },	// strings
		{ regex: /0x[a-f0-9]+|\d+(\.\d+)?/gi,								css: 'value' },		// numbers
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'),				css: 'keyword' },	// keywords
		{ regex: new RegExp(keyops, 'gm'),									css: 'keyword' }	// scala keyword
		];
}

SyntaxHighlighter.brushes.Scala.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Scala.aliases		= ['scala'];
