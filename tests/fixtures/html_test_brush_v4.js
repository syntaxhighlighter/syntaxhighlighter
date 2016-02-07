var BrushBase = require('brush-base');
var regexLib = require('syntaxhighlighter-regex').commonRegExp;

function Brush()
{
  this.regexList = [
    { regex: /'.*$/gm, css: 'comments' },
    { regex: /^\s*#.*$/gm, css: 'preprocessor' },
    { regex: regexLib.doubleQuotedString, css: 'string' },
    { regex: new RegExp(this.getKeywords('hello world'), 'gm'), css: 'keyword' }
  ];

  this.forHtmlScript(regexLib.phpScriptTags);
};

Brush.prototype = new BrushBase();
Brush.aliases = ['html_test_brush_v4'];
module.exports = Brush;
