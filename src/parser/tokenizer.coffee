Match = require './match'
XRegExp = require 'xregexp'

class Tokenizer
  constructor: (@code, @regexList) ->

  # Checks to see if any of the matches are inside of other matches.
  # This process would get rid of highligted strings inside comments,
  # keywords inside strings and so on.
  removeNestedMatches: (matches) ->
    # Optimized by Jose Prado (http://joseprado.com)
    for itemI, i in matches
      continue unless itemI?

      itemIEndPos = itemI.index + itemI.length

      for itemJ, j in matches of [i..]
        j = i + 1
      while j < matches.length and matches[i]?
        j++

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

  getMatches: (regexInfo) ->
    matches = []
    {regex, css, func} = regexInfo
    process = func or (regexMatch) -> regexMatch[0]
    pos = 0

    while regexMatch = XRegExp.exec @code, regex, pos
      resultMatch = process regexMatch, regexInfo

      if typeof resultMatch is 'string'
        resultMatch = [new Match resultMatch, regexMatch.index, css]

      matches.push m for m in resultMatch
      pos = regexMatch.index + regexMatch[0].length

    matches

  scan: ->
    matches = []

    for regexInfo in @regexList
      matches = matches.concat @getMatches regexInfo

    matches

module.exports = Tokenizer
