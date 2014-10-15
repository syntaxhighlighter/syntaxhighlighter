window.SyntaxHighlighter = {
  brushes: {},

  Highlighter: require('syntaxhighlighter-brush').Brush,

  highlight: function()
  {

  },

  all: function()
  {

  }
};

window.XRegExp = require('xregexp');
window.SyntaxHighlighter = require('./core');
window.SyntaxHighlighter.Match = require('syntaxhighlighter-parser/lib/match').Match;
window.SyntaxHighlighter.Highlighter = require('syntaxhighlighter-brush').Brush;
window.SyntaxHighlighter.regexLib = require('./regexlib');
