// compatability layer to support V3 brushes

import core from './core';
window.SyntaxHighlighter = core;
window.XRegExp = require('syntaxhighlighter-regex').XRegExp;
