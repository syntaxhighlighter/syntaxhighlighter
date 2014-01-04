var
  Match = require('./match'),
  XRegExp = require('xregexp')
  ;

/**
 * Executes given regular expression on provided code and returns all matches that are found.
 *
 * @param {String} code    Code to execute regular expression on.
 * @param {Object} regex   Regular expression item info from `regexList` collection.
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
      process = regexInfo.func ? regexInfo.func : defaultAdd
      pos = 0
      ;

  while(match = XRegExp.exec(code, regexInfo.regex, pos))
  {
    var resultMatch = process(match, regexInfo);

    if (typeof resultMatch === 'string')
      resultMatch = [new Match(resultMatch, match.index, regexInfo.css)];

    matches = matches.concat(resultMatch);
    pos = match.index + match[0].length;
  }

  return matches;
};

/**
 * Sorts matches by index position and then by length.
 */
function sortMatches(matches)
{
  function sortMatchesCallback(m1, m2)
  {
    // sort matches by index first
    if(m1.index < m2.index)
      return -1;
    else if(m1.index > m2.index)
      return 1;
    else
    {
      // if index is the same, sort by length
      if(m1.length < m2.length)
        return -1;
      else if(m1.length > m2.length)
        return 1;
    }

    return 0;
  }

  return matches.sort(sortMatchesCallback);
}

/**
 * Checks to see if any of the matches are inside of other matches.
 * This process would get rid of highligted strings inside comments,
 * keywords inside strings and so on.
 */
function removeNestedMatches(matches)
{
  // Optimized by Jose Prado (http://joseprado.com)
  for (var i = 0, l = matches.length; i < l; i++)
  {
    if (matches[i] === null)
      continue;

    var itemI = matches[i],
        itemIEndPos = itemI.index + itemI.length
        ;

    for (var j = i + 1, l = matches.length; j < l && matches[i] !== null; j++)
    {
      var itemJ = matches[j];

      if (itemJ === null)
        continue;
      else if (itemJ.index > itemIEndPos)
        break;
      else if (itemJ.index == itemI.index && itemJ.length > itemI.length)
        matches[i] = null;
      else if (itemJ.index >= itemI.index && itemJ.index < itemIEndPos)
        matches[j] = null;
    }
  }

  return matches;
}

/**
 * Applies all regular expression to the code and stores all found
 * matches in the `this.matches` array.
 */
function parse(code, regexList, opts)
{
  var result = [];

  regexList = regexList || [];

  for (var i = 0, l = regexList.length; i < l; i++)
    // BUG: length returns len+1 for array if methods added to prototype chain (oising@gmail.com)
    if (typeof regexList[i] === 'object')
      result = result.concat(getMatches(code, regexList[i]));

  result = sortMatches(result);
  result = removeNestedMatches(result);

  return result;
}

module.exports = {
  getMatches: getMatches,
  parse: parse
}
