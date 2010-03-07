<?php
header('Content-type: application/xhtml+xml; charset=utf-8'); 
?><? echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-W3CDTD XHTML 1.0 StrictEN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>SyntaxHighlighter XHTML Test</title>
	<script type="text/javascript" src="Scripts/XRegExp.js"></script> <!-- XRegExp is bundled with the final shCore.js during build -->
	<script type="text/javascript" src="Scripts/shCore.js"></script>
	<script type="text/javascript" src="Scripts/shBrushAS3.js"></script>
	<script type="text/javascript" src="Scripts/shBrushAppleScript.js"></script>
	<link type="text/css" rel="stylesheet" href="Styles/shCore.css"/>
	<link type="text/css" rel="Stylesheet" href="Styles/shThemeDefault.css" id="theme" />
	
	<script type="text/javascript">
	SyntaxHighlighter.config.stripBrs = true;
	SyntaxHighlighter.all();
	</script>
</head>

<body>

<h1>SyntaxHighlighter XHTML Test</h1>
<p>Insures that SyntaxHighlighter works content type XHTML</p>

<pre class="brush: as3;">
package free.cafekiwi.gotapi;

function foo()
{
	/* 
	
	comment 
	
	*/
	
	// comment
	
	"string"
	
	'string'
	
	"multiline
	string shouldnt match"

	'multiline
	string shouldnt match'
}
</pre>

</body>
</html>
