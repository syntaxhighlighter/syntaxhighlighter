(function() {

var sh = SyntaxHighlighter;

/**
 * Provides functionality to dynamically load only the brushes that a needed to render the current page.
 *
 * There are two syntaxes that autoload understands. For example:
 * 
 * SyntaxHighlighter.autoloader(
 *     [ 'applescript',          'Scripts/shBrushAppleScript.js' ],
 *     [ 'actionscript3', 'as3', 'Scripts/shBrushAS3.js' ]
 * );
 *
 * or a more easily comprehendable one:
 *
 * SyntaxHighlighter.autoloader(
 *     'applescript       Scripts/shBrushAppleScript.js',
 *     'actionscript3 as3 Scripts/shBrushAS3.js'
 * );
 */
sh.autoloader = function()
{
	var list = arguments,
		elements = sh.findElements(),
		brushes = {},
		i
		;
		
	function addBrush(aliases, url)
	{
		for (var i = 0; i < aliases.length; i++)
			brushes[aliases[i]] = url;
	};
	
	function getAliases(item)
	{
		return item.pop
			? item
			: item.split(/\s+/)
			;
	}
	
	// create table of aliases and script urls
	for (i = 0; i < list.length; i++)
	{
		var aliases = getAliases(list[i]),
			url = aliases.pop()
			;
			
		addBrush(aliases, url);
	}
	
	// dynamically add <script /> tags to the document body
	for (i = 0; i < elements.length; i++)
	{
		var url = brushes[elements[i].params.brush],
			script
			;
		
		if (!url)
			continue;
			
		with(script = document.createElement('script'))
		{
			src = url;
			type = 'text/javascript';
			language = 'javascript';
		};
		
		// sync way of adding script tags to the page
		document.body.appendChild(script);
	}
};

})();
