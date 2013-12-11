var tokens = require('./tokens');

/**
 * Callback method for Array.sort() which sorts matches by
 * index position and then by length.
 *
 * @param {Match} m1  Left object.
 * @param {Match} m2    Right object.
 * @return {Number}     Returns -1, 0 or -1 as a comparison result.
 */
function matchesSortCallback(m1, m2)
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

/**
 * Applies all regular expression to the code and stores all found
 * matches in the `this.matches` array.
 * @param {Array} regexList   List of regular expressions.
 * @param {String} code     Source code.
 * @return {Array}        Returns list of matches.
 */
function findMatches(regexList, code)
{
  var result = [];

  if (regexList != null)
    for (var i = 0, l = regexList.length; i < l; i++)
      // BUG: length returns len+1 for array if methods added to prototype chain (oising@gmail.com)
      if (typeof (regexList[i]) == "object")
        result = result.concat(tokens.extract(code, regexList[i]));

  // sort and remove nested the matches
  return removeNestedMatches(result.sort(matchesSortCallback));
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

function getTokens(code, regexList, opts)
{
  return findMatches(regexList, code);
}

module.exports = {
  getTokens: getTokens
}
