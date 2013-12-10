var XRegExp = require('xregexp');

/**
 * Parses key/value pairs into hash object.
 *
 * Understands the following formats:
 * - name: word;
 * - name: [word, word];
 * - name: "string";
 * - name: 'string';
 *
 * For example:
 *   name1: value; name2: [value, value]; name3: 'value'
 *
 * @param {String} str    Input string.
 * @return {Object}       Returns deserialized object.
 */
function parse(str)
{
  var match,
    result = {},
    arrayRegex = XRegExp("^\\[(?<values>(.*?))\\]$"),
    pos = 0,
    regex = XRegExp(
      "(?<name>[\\w-]+)" +
      "\\s*:\\s*" +
      "(?<value>" +
        "[\\w%#-]+|" +    // word
        "\\[.*?\\]|" +    // [] array
        '".*?"|' +        // "" string
        "'.*?'" +         // '' string
      ")\\s*;?",
      "g"
    )
    ;

  while ((match = XRegExp.exec(str, regex, pos)) != null)
  {
    var value = match.value
      .replace(/^['"]|['"]$/g, '') // strip quotes from end of strings
      ;

    // try to parse array value
    if (value != null && arrayRegex.test(value))
    {
      var m = XRegExp.exec(value, arrayRegex);
      value = m.values.length > 0 ? m.values.split(/\s*,\s*/) : [];
    }

    result[match.name] = value;
    pos = match.index + match[0].length;
  }

  return result;
}

module.exports = {
  parse: parse
};
