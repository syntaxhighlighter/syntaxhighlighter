# SyntaxHighlighter [![Gittip](http://img.shields.io/gittip/alexgorbatchev.png)](https://www.gittip.com/alexgorbatchev/)

SyntaxHighlighter is THE client side highlighter for the web and apps! It's been around since
2004 and it's used virtually everywhere to seamlessly highlight code for presentation.

The latest doc is currently located at [alexgorbatchev.com/SyntaxHighlighter](http://alexgorbatchev.com/SyntaxHighlighter/)

# Building

1. Have node.js v0.10 or higher
1. From the source folder run `npm install`
1. Then `./node_modules/.bin/bower install` to download dependencies
1. Then `./node_modules/.bin/grunt build` to build
1. Look in the `pkg` folder for results!

# Testing

Testing is something that is still inherited from ages ago and is currently using QUnit. To test the project, it's a two step process:

1. Start HTTP server `./node_modules/.bin/grunt test`
1. Open browser on `http://localhost:3000` and go from there

# Please see the [dev](https://github.com/alexgorbatchev/SyntaxHighlighter/tree/dev) branch
