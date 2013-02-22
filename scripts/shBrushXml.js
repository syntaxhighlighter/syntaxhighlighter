;(function()
{
	// CommonJS
	SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

	function Brush()
	{
		function process(match, regexInfo)
		{
			var constructor = SyntaxHighlighter.Match,
				code = match[0],
				tag = XRegExp.exec(code, XRegExp('(&lt;|<)[\\s/?]*(?<name>[\\w:.-]+)', 'g')),
				result = []
				;
		
			if (match.attributes != null) 
			{
				var attributes,
					regex = XRegExp('(?<name> [\\w:.-]+)' +
									'\\s*=\\s*' +
									'(?<value> ".*?"|\'.*?\'|\\w+)',
									'xg');

				while ((attributes = XRegExp.exec(code, regex)) != null) 
				{
					result.push(new constructor(attributes.name, match.index + attributes.index, 'color1'));
					result.push(new constructor(attributes.value, match.index + attributes.index + attributes[0].indexOf(attributes.value), 'string'));
				}
			}

			if (tag != null)
				result.push(
					new constructor(tag.name, match.index + tag[0].indexOf(tag.name), 'keyword')
				);

			return result;
		}
	
		this.regexList = [
			{ regex: XRegExp('(&lt;|<)!\\[[\\w\\s]*?\\[(.)*?\\]\\](&gt;|>)', 'sg'),				css: 'color2' },	// <![ ... [ ... ]]>
			{ regex: SyntaxHighlighter.regexLib.xmlComments,									css: 'comments' },	// <!-- ... -->
			{ regex: XRegExp('(&lt;|<)[\\s/?]*(\\w+)(?<attributes>.*?)[\\s/?]*(&gt;|>)', 'sg'),	func: process }
		];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['xml', 'xhtml', 'xslt', 'html'];

	SyntaxHighlighter.brushes.Xml = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
