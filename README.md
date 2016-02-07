# SyntaxHighlighter v4

[![GratiPay](https://img.shields.io/gratipay/user/alexgorbatchev.svg)](https://gratipay.com/alexgorbatchev/)
[![Build Status](https://travis-ci.org/syntaxhighlighter/theme-swift.svg)](https://travis-ci.org/syntaxhighlighter/syntaxhighlighter)
![Downloads](https://img.shields.io/npm/dm/syntaxhighlighter.svg)
![Version](https://img.shields.io/npm/v/syntaxhighlighter.svg)

SyntaxHighlighter is THE client side highlighter for the web and web-apps! It's been around since 2004 and it's used virtually everywhere to seamlessly highlight code for presentation purposes.

<img src="screenshot.png" width="640"/>

The history of this project predates majority of the common web technologies and it has been a challenge to dedicate time and effort to keep it up to date. Everything used to be in one file and assign `window` variables... Horrors!

For the impatient:

* [[Building]] instructions
* [[Usage]] instructions
* Be sure to read the [[caveats|Caveats]]

SyntaxHighlighter is currently used and has been used in the past by Microsoft, Apache, Mozilla, Yahoo, Wordpress, Bug Labs, Freshbooks and many many other companies and blogs.

## Older Version

If you are looking for v3 documentation and download, you can find it on the [old site](alexgorbatchev.com/SyntaxHighlighter).

## Installation

SyntaxHighlighter requires Node.js & NPM installed on your system. If you don't have it yet, I suggest using [NVM](https://github.com/creationix/nvm) to do the installation and fetch the latest version.

```
$ npm install syntaxhighlighter
$ gulp setup-project
```

## Building

Before you can drop the `<script/>` on your page, you need to make a custom build. This is actually super simple. Older versions of SyntaxHighlighter relied on multiple `<script/>` tags and global variables. This is no longer necessary and everything you want can be packaged into a single `.js` file.

See the [[Building]] wiki page for details.

## Usage

After you've built a custom `syntaxhighlighter.js`, all you have to do is drop a `<script/>` tag somewhere on your site.

See the [[Usage]] wiki page for details.

## Development

```
$ git clone git@github.com:syntaxhighlighter/syntaxhighlighter.git
$ npm install
$ gulp setup-project
```

## Testing

All tests have been spread out across [individual project packages](https://github.com/syntaxhighlighter). The tests included here are smoke and integration only.

```
npm test
```

# License

MIT
