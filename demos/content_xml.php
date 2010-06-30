<?php
header('Content-type: application/xhtml+xml; charset=utf-8'); 
?><? echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-W3CDTD XHTML 1.0 StrictEN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>SyntaxHighlighter XHTML Demo</title>
	<script type="text/javascript" src="../scripts/XRegExp.js"></script> <!-- XRegExp is bundled with the final shCore.js during build -->
	<script type="text/javascript" src="../scripts/shCore.js"></script>
	<script type="text/javascript" src="../scripts/shBrushAS3.js"></script>
	<link type="text/css" rel="stylesheet" href="../styles/shCore.css"/>
	<link type="text/css" rel="Stylesheet" href="../styles/shThemeDefault.css"/>
	<script type="text/javascript">SyntaxHighlighter.all();</script>
</head>

<body>

<h1>SyntaxHighlighter XHTML Demo</h1>

<p>
	The purpose of this demo is to show XML content compatibility. Since there's no
	way to force HTTP headers to have required content content type, this demo requires
	a PHP featured HTTP server such as Apache.
</p>

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
