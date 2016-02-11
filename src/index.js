import domready from 'domready';
import SyntaxHighlighter from './core';
import * as dasherize from './dasherize';

// configured through the `--compat` parameter.
if (COMPAT) {
  require('./compatibility_layer_v3');
}

domready(() => SyntaxHighlighter.highlight(dasherize.object(window.syntaxhighlighterConfig || {})));

export * from './core';
