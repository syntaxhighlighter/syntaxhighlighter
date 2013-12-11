var XRegExp = require('xregexp');

module.exports = {
  multiLineCComments          : XRegExp('/\\*.*?\\*/', 'gs'),
  singleLineCComments         : /\/\/.*$/gm,
  singleLinePerlComments      : /#.*$/gm,
  doubleQuotedString          : /"([^\\"\n]|\\.)*"/g,
  singleQuotedString          : /'([^\\'\n]|\\.)*'/g,
  multiLineDoubleQuotedString : XRegExp('"([^\\\\"]|\\\\.)*"', 'gs'),
  multiLineSingleQuotedString : XRegExp("'([^\\\\']|\\\\.)*'", 'gs'),
  xmlComments                 : XRegExp('(&lt;|<)!--.*?--(&gt;|>)', 'gs'),
  url                         : /\w+:\/\/[\w-.\/?%&=:@;#]*/g,
  phpScriptTags               : { left: /(&lt;|<)\?(?:=|php)?/g, right: /\?(&gt;|>)/g, 'eof' : true },
  aspScriptTags               : { left: /(&lt;|<)%=?/g, right: /%(&gt;|>)/g },
  scriptScriptTags            : { left: /(&lt;|<)\s*script.*?(&gt;|>)/gi, right: /(&lt;|<)\/\s*script\s*(&gt;|>)/gi }
};
