import domready from 'domready';
import SyntaxHighlighter from './core';

// configured through the `--compat` parameter.
if (COMPAT) {
  require('./compatibility_layer_v3');
}

domready(SyntaxHighlighter.highlight);

export * from './core';
