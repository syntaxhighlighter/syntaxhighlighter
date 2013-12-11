var
  XRegExp = require('xregexp')
  ;

/**
 * Match object.
 */
function Match(value, index, css)
{
  this.value = value;
  this.index = index;
  this.length = value.length;
  this.css = css;
  this.brushName = null;
};

Match.prototype.toString = function()
{
  return this.value;
};

/**
 * Executes given regular expression on provided code and returns all
 * matches that are found.
 *
 * @param {String} code    Code to execute regular expression on.
 * @param {Object} regex   Regular expression item info from <code>regexList</code> collection.
 * @return {Array}         Returns a list of Match objects.
 */
function getMatches(code, regexInfo)
{
  function defaultAdd(match, regexInfo)
  {
    return match[0];
  };

  var index = 0,
    match = null,
    matches = [],
    func = regexInfo.func ? regexInfo.func : defaultAdd
    pos = 0
    ;

  while((match = XRegExp.exec(code, regexInfo.regex, pos)) != null)
  {
    var resultMatch = func(match, regexInfo);

    if (typeof(resultMatch) == 'string')
      resultMatch = [new Match(resultMatch, match.index, regexInfo.css)];

    matches = matches.concat(resultMatch);
    pos = match.index + match[0].length;
  }

  return matches;
};

module.exports = {
  Match: Match,
  extract: getMatches
};