// window.SyntaxHighlighter = {
//   brushes: {},

//   Highlighter: require('syntaxhighlighter-brush').Brush,

//   highlight: function()
//   {

//   },

//   all: function()
//   {

//   }
// };

console.log(1)
window.XRegExp = require('xregexp');
console.log(2)
window.SyntaxHighlighter = require('./core');
console.log(3)
window.SyntaxHighlighter.Match = require('syntaxhighlighter-parser/lib/match').Match;
console.log(4)
window.SyntaxHighlighter.Highlighter = require('syntaxhighlighter-brush').Brush;
console.log(5)
window.SyntaxHighlighter.regexLib = require('./regexlib');
console.log(6)
