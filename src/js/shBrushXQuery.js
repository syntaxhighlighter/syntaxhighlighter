;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		// Contributed by Ben
		// https://github.com/benweaver
		
		var datatypes = 'xs:string xs:boolean xs:float xs:double xs:decimal xs:positiveInteger '+
						'xs:duration xs:dateTime xs:time xs:date xs:gYearMonth xs:gYear xs:gMonthDay xs:gDay xs:gMonth '+
						'xs:hexBinary xs:base64Binary xs:anyURI xs:QName xs:NOTATION xs:derived xs:token xs:language '+
						'xs:IDREFS xs:ENTITIES xs:NMTOKEN xs:NMTOKENS xs:Name xs:NCName xs:ID xs:IDREF xs:ENTITY '+
						'xs:integer xs:nonPositiveInteger xs:negativeInteger xs:long xs:int xs:short xs:byte '+
						'xs:nonNegativeInteger xs:unsignedLong xs:unsignedInt xs:unsignedShort xs:unsignedByte';
		
		var axes 	  = 'child:: descendant:: attribute:: self:: descendant-or-self:: following-sibling:: following:: ' + 
						'parent:: ancestor:: preceding-sibling:: preceding:: ancestor-or-self::';
		
		var keywords  =	'xquery version encoding ' +
						'declare module namespace boundary-space default function collation base-uri '+
						'option ordering ordered unordered empty greatest least ' +
						'copy-namespaces preserve no-preserve inherit no-inherit '+
						'import schema at construction strip preserve external '+
						'element document attribute comment text processing-instruction '+
						'for in as let where stable order by order by ascending descending return '+
						'some every satisfies typeswitch case '+
						'if then else function item of instance cast castable treat '+
						'validate lax strict empty-sequence ' + 
						'union intersect except to';

		var operators =	'eq ne gt lt le ge is div idiv mod and or';
		
		function getKeywordsSafe(str)
		{
			str = str.trim().replace(/\s+/g, '|');
			return '(?:^|\\s|\\)|{)(?:' + str + ')(?=$|\\s|\\(|})';
		};
		 
		function processXML(match, regexInfo)
		{
			var constructor = SyntaxHighlighter.Match,
				code = match[0],
				tag = new XRegExp('(&lt;|<)[\\s\\/\\?]*(?<name>[:\\w-\\.]+)', 'xg').exec(code),
				closetag = new XRegExp('(&gt;|>)$','g').exec(code),
				result = []
				;
		
			if (match.attributes != null) 
			{
				var attributes,
					regex = new XRegExp('(?<name> [\\w:\\-\\.]+)' +
										'\\s*=\\s*' +
										'(?<value> ".*?"|\'.*?\'|\\w+)',
										'xg');

				while ((attributes = regex.exec(code)) != null) 
				{
					result.push(new constructor(attributes.name, match.index + attributes.index, 'color2'));
					result.push(new constructor(attributes.value, match.index + attributes.index + attributes[0].indexOf(attributes.value), 'string'));
				}
			}

			if (tag != null)
			{
				result.push(
					new constructor(tag[0], match.index + tag[0].indexOf(tag[0]), 'constants')
				);
			}
			
			if (closetag != null)
			{
				result.push(
					new constructor(closetag[0], match.index + closetag.index, 'constants')
				);
			}
			
			return result;
		};
		
		function processText(match, regexInfo)
		{
			var constructor = SyntaxHighlighter.Match;
			var result = [];
			
			if (match.text != null) 
			{
				result.push(new constructor(match.text, match.index + 1, 'plain'));
			}
			return result;
		};
		
		function processNames(match, regexInfo)
		{
			var constructor = SyntaxHighlighter.Match;
			var result = [];
			
			if(match.name == null)
			{
				return result;
			}
			
			if(match.hasAt != null)
			{
				result.push(new constructor(match.hasAt + match.name, match.index, 'color2'));
				return result;
			}
			
			if(match.hasEqual != null)
			{
				return result;
			}
			
			if(match.hasParens != null)
			{
				if (["if","else","typeswitch","union","intersect"].indexOf(match.name) == -1
					&& operators.split(" ").indexOf(match.name) == -1 
					)
				{
					result.push(new constructor(match.name, match.index, 'functions'));
					return result;
				}
			}
			
			if (   keywords.split(" ").indexOf(match.name) == -1 
				&& operators.split(" ").indexOf(match.name) == -1
				&& axes.split(" ").indexOf(match.name + '::') == -1 
				)
			{
				result.push(new constructor(match.name, match.index, 'color1'));
			}
			
			return result;
		};
		
		var ncName = '[A-Z_][\\w\\.\\-]*'
		var xmlName = '('+ ncName +':)?' + ncName;
	
		this.regexList = [
			
			// XML
			{ regex: new XRegExp('(\\&lt;|<)[\\s\\/\\?]*(\\w+)'
								+'(?<attributes>[^<]*?)'
								+'[\\s\\/\\?]*(&gt;|>)', 'gs'), 		func: processXML 	},		// XML elements
			{ regex: new XRegExp('>(?<text>[^{}<]*)<\\s*(\\/|!)','gs'), func: processText 	},		// Text between XML tags
			{ regex: new XRegExp('(\\&lt;|<)\\!\\[[\\w\\s]*?'
								+'\\[(.|\\s)*?\\]\\](\\&gt;|>)', 'gs'),	css: 'constants' 	},		// <![ ... [ ... ]]>
			{ regex: SyntaxHighlighter.regexLib.xmlComments,			css: 'comments' 	},		// XML comments

			// XQuery
			{ regex: new XRegExp('\\(\\:(.*?)\\:\\)', 'gs'),			css: 'comments' 	},		// XQuery comments 
			{ regex: new RegExp(getKeywordsSafe(keywords), 'gi'),		css: 'keyword'		},		// Keywords
			{ regex: new RegExp(getKeywordsSafe(operators), 'gi'),		css: 'plain' 		},		// Operators
			{ regex: new RegExp(this.getKeywords(axes), 'gi'),			css: 'color3' 		},		// XPath axes
			{ regex: new RegExp('\\$' + xmlName , 'gi'),				css: 'variable' 	},		// Variables
			{ regex: new XRegExp('(?<hasAt>@)?' 
								+'(?<name>'+ xmlName +')'
								+'(?<hasEqual>=)?'
								+'(?<hasParens>\\s*\\()?'
								+'(?=[^<])', 'gi'),						func: processNames	},		// XPaths and functions

			// Strings
			{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,  css: 'string' },		// Double-quoted strings
			{ regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,  css: 'string' }		// Single-quoted strings

		];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['xq', 'xquery', 'xqy', 'xqm'];

	SyntaxHighlighter.brushes.XQuery = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();

