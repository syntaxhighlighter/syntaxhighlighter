;(function()
{
  // CommonJS
  typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

  function Brush()
  {
    var keywords =  'asserterror begin case do downto else' +
            'end exit for if of repeat then to until while with' +
            'false true';

    this.regexList = [
      { regex: SyntaxHighlighter.regexLib.singleLineCComments,  css: 'comments' },   // one line comments
      { regex: /\{[\s\S]*?\}/gm,   css: 'comments' },      // multiline comments
      { regex: /@"(?:[^"]|"")*"/g,                css: 'string' },      // @-quoted strings
      { regex: SyntaxHighlighter.regexLib.doubleQuotedString,   css: 'string' },      // strings
      { regex: SyntaxHighlighter.regexLib.singleQuotedString,   css: 'string' },      // strings
      { regex: /^(\w+)\((.*)\)$/gmi, css: 'bold' }, // function def.
      { regex: new RegExp(this.getKeywords(keywords), 'gmi'),    css: 'keyword' },     // keywords
      ];
  };

  Brush.prototype = new SyntaxHighlighter.Highlighter();
  Brush.aliases = ['cal', 'c-al'];

  SyntaxHighlighter.brushes.CAL = Brush;

  // CommonJS
  typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();