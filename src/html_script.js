var parser = require('parser');

function HtmlScript(brushClass)
{
  var scriptBrush,
    xmlBrush = new window.SyntaxHighlighter.brushes.Xml()
    ;

  if (brushClass == null)
    return;

  scriptBrush = new brushClass();

  if (scriptBrush.htmlScript == null)
    throw new Error('Brush wasn\'t configured for html-script option: ' + brushClass.brushName);

  xmlBrush.regexList.push(
    { regex: scriptBrush.htmlScript.code, func: process }
  );

  this.regexList = xmlBrush.regexList;

  function offsetMatches(matches, offset)
  {
    for (var j = 0, l = matches.length; j < l; j++)
      matches[j].index += offset;
  }

  function process(match, info)
  {
    var code = match.code,
        results = [],
        regexList = scriptBrush.regexList,
        offset = match.index + match.left.length,
        htmlScript = scriptBrush.htmlScript,
        matches
        ;

    function add(matches)
    {
      results = results.concat(matches);
    }

    matches = parser.parse(code, regexList);
    offsetMatches(matches, offset);
    add(matches);

    // add left script bracket
    if (htmlScript.left != null && match.left != null)
    {
      matches = parser.parse(match.left, [htmlScript.left]);
      offsetMatches(matches, match.index);
      add(matches);
    }

    // add right script bracket
    if (htmlScript.right != null && match.right != null)
    {
      matches = parser.parse(match.right, [htmlScript.right]);
      offsetMatches(matches, match.index + match[0].lastIndexOf(match.right));
      add(matches);
    }

    for (var j = 0, l = results.length; j < l; j++)
      results[j].brushName = brushClass.brushName;

    return results;
  }
};

module.exports = {
  HtmlScript: HtmlScript
};