;(function()
{
  // CommonJS
  SyntaxHighlighter = SyntaxHighlighter || (typeof require !== 'undefined'? require('shCore').SyntaxHighlighter : null);

  function Brush()
  {
    this.regexList = [
      { regex: /'.*$/gm, css: 'comments' },
      { regex: /^\s*#.*$/gm, css: 'preprocessor' },
      { regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string' },
      { regex: new RegExp(this.getKeywords('hello world'), 'gm'), css: 'keyword' }
    ];
    this.forHtmlScript(SyntaxHighlighter.regexLib.phpScriptTags);
  };

  Brush.prototype = new SyntaxHighlighter.Highlighter();
  Brush.aliases = ['compat-html'];

  SyntaxHighlighter.brushes.CompatHtml = Brush;

  // CommonJS
  typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();
