Building SyntaxHighlighter
==========================

1. Install dependencies with NPM.

  `npm install`

2. Install bower

  `npm install bower`

3. Install node-sass

  `npm install node-sass`

  *nb*: If you're using node v0.8, you'll have to use node-sass 0.3.0. In that case, you'll need to run `npm install node-sass@0.3.0`

4. Use bower to install bower.json dependencies

  `./node_modules/.bin/bower install`

5. Run jake to build SyntaxHightighter

  `./node_modules/.bin/jake`

Notes
-----
* You can install and run jake and/or bower globally and just run them like so
  `bower install`
  `jake install`

* bower requires node 0.8.0 +.
