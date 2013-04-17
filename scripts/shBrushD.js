// Copyright 2013 Stefan Rohe
;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	// This is just for the very basic grammar
	function Brush()
	{
		var datatypes =	'bool byte char creal dchar double float idouble ifloat int ireal ' +
                        'long real short ubyte ucent uint ulong ushort wchar wstring void ' +
                        'size_t sizediff_t';

		var keywords =	'abstract alias align asm assert auto break case cast cdouble cent ' +
                        'cfloat const continue debug default delegate delete deprecated ' +
                        'export extern final finally function goto immutable import inout ' +
                        'invariant is lazy macro module new nothrow override package pragma ' +
                        'private protected public pure ref return shared short static super ' +
                        'synchronized template this throw typedef typeid typeof volatile ' +
                        '__FILE__ __LINE__ __gshared __traits __vector __parameters body ' +
                        'catch class do else enum for foreach foreach_reverse if in interface ' +
                        'mixin out scope struct switch try union unittest version while with';

		var functions =	'assert';

		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' },			// one line comments
			{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' },			// multiline comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },			// strings
			{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },			// strings
			{ regex: /^ *#.*/gm,										css: 'preprocessor' },
			{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'color1 bold' },
			{ regex: new RegExp(this.getKeywords(functions), 'gm'),		css: 'functions bold' },
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword bold' }
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['d', 'di'];

	SyntaxHighlighter.brushes.D = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
