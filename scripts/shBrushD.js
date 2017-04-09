;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		// Contributed by Aleksey Vitebskiy

		var keywords = 	'abstract alias align asm assert auto body break case cast catch class const continue ' +
						'debug default delegate delete deprecated do else enum export extern false final ' +
						'finally for foreach foreach_reverse function goto if import in inout interface invariant ' +
						'is lazy mixin module new null out override package pragma private private: protected protected: ' +
						'public public: return scope static struct super switch synchronized template this throw true try ' +
						'typedef typeid typeof union unittest version void volatile wchar while with ~this';

		var datatypes =	'bool byte cdouble cent cfloat char creal dchar double float idouble ifloat int ireal ' +
						'long real short ubyte ucent uint ulong ushort size_t ptrdiff_t string';

		var properties = 'sizeof nan init mangleof stringof alignof min max infinity dig epsilon mant_dig max_10_exp max_exp min_10_exp min_exp max min_normal re im classinfo';

		var constants = '__DATE__ __EOF__ __TIME__ __TIMESTAMP__ __VENDOR__ __VERSION__'

		this.regexList = [
			{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	css: 'comments' },			// one line comments
			{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' },			// multiline comments
			{ regex: /\/\+[\s\S]*?\+\//gm,								css: 'comments' },			// nested comments
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },			// double-quoted strings
			{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },			// single-quoted strings
			{ regex: /r"([^\n]|\\.)*"/g,								css: 'string' },			// wysiwyg strings
			{ regex: /`([^\n]|\\.)*`/g,									css: 'string' },			// alternate wysiwyg strings
			{ regex: /\b([\d]+(\.[\d]+)?|0x[a-f0-9]+)\b/gi,				css: 'value' },				// numbers
			{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' },			// keywords
			{ regex: new RegExp(this.getKeywords(constants), 'gm'),		css: 'constants'},			// special tokens
			{ regex: new RegExp(this.getKeywords(constants), 'gm'),		css: 'value'},				// common properties
			{ regex: new RegExp(this.getKeywords(datatypes), 'gm'),		css: 'color1 bold'},		// data types
			{ regex: /\@property\b/g,									css: 'color2' },			// @property keyword
			{ regex: new RegExp(this.getKeywords(properties), 'gm'),	css: 'color2' },			// properties
			];

		this.forHtmlScript({
			left	: /(&lt;|<)%[@!=]?/g, 
			right	: /%(&gt;|>)/g 
		});
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['d'];

	SyntaxHighlighter.brushes.D = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
