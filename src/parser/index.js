var
  Match = require('./match'),
  XRegExp = require('xregexp')
  ;

function Parser(code, opts)
{
  this.opts = opts;
  this.code = code;
}

Parser.prototype.parse = function(code)
{

};

module.exports = {
  Match: Match,
  Parser: Parser
};