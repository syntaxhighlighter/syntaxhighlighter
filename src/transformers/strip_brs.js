module.exports = function(code, opts)
{
  var br = /<br\s*\/?>|&lt;br\s*\/?&gt;/gi;

  if (opts['stripBrs'] === true)
    code = code.replace(br, '');

  return code;
};
