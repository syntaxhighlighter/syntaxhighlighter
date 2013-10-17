/**
 * SyntaxHighlighter
 * http://alexgorbatchev.com/
 *
 * SyntaxHighlighter is donationware. If you are using it, please donate.
 * http://alexgorbatchev.com/wiki/SyntaxHighlighter:Donate
 *
 * @version
 * 2.1.364 (October 15 2009)
 * 
 * @copyright
 * Copyright (C) 2004-2009 Alex Gorbatchev.
 *
 * @license
 * This file is part of SyntaxHighlighter.
 * 
 * SyntaxHighlighter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * SyntaxHighlighter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with SyntaxHighlighter.  If not, see <http://www.gnu.org/copyleft/lesser.html>.
 */
SyntaxHighlighter.brushes.Powerbuilder = function()
{
	var keywords =	'_debug and alias autoinstantiate call case choose close commit connect constant continue create cursor ' + 
'declare delete describe descriptor destroy disconnect do dynamic else elseif end enumerated event execute ' + 
'exit external false fetch first for forward from function global goto halt if immediate indirect insert ' + 
'into intrinsic is last library loop next not of on open or parent post prepare prior private privateread ' + 
'privatewrite procedure protected protectedread protectedwrite prototypes public readonly ref return rollback ' + 
'rpcfunc select selectblob shared static step subroutine super system systemread systemwrite then this to ' + 
'trigger true type until update updateblob using variables while with within any blob boolean char character ' + 
'date datetime dec decimal double int integer long real string time uint ulong unsignedint unsignedinteger unsignedlong try catch';

	function fixComments(match, regexInfo)
	{
		var css = (match[0].indexOf("///") == 0)
			? 'color1'
			: 'comments'
			;
			
		return [new SyntaxHighlighter.Match(match[0], match.index, css)];
	}

	this.regexList = [
		{ regex: SyntaxHighlighter.regexLib.singleLineCComments,	func : fixComments },		// one line comments
		{ regex: SyntaxHighlighter.regexLib.multiLineCComments,		css: 'comments' },			// multiline comments
		{ regex: /@"(?:[^"]|"")*"/g,								css: 'string' },			// @-quoted strings
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },			// strings
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },			// strings
		{ regex: /^\s*#.*/gm,										css: 'preprocessor' },		// preprocessor tags like #region and #endregion
		{ regex: new RegExp(this.getKeywords(keywords), 'gmi'),		css: 'keyword' },			// c# keyword
		{ regex: /\bpartial(?=\s+(?:class|interface|struct)\b)/g,	css: 'keyword' },			// contextual keyword: 'partial'
		{ regex: /\byield(?=\s+(?:return|break)\b)/g,				css: 'keyword' }			// contextual keyword: 'yield'
		];
		
	this.forHtmlScript(SyntaxHighlighter.regexLib.aspScriptTags);
};

SyntaxHighlighter.brushes.Powerbuilder.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.Powerbuilder.aliases	= ['powerbuilder', 'pb'];

