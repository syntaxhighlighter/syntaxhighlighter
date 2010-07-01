require.paths.unshift(__dirname + '/../scripts');

var sys = require('sys'),
	shSyntaxHighlighter = require('shCore').SyntaxHighlighter,
	shJScript = require('shBrushJScript').Brush,
	
	code = '\
		function helloWorld()\
		{\
			// this is great!\
			for(var i = 0; i <= 1; i++)\
				alert("yay");\
		}\
		',
	brush = new shJScript()
	;

brush.init({ toolbar: false });
sys.puts(brush.getHtml(code));
