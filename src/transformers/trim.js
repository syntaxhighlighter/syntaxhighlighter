module.exports = function(code, opts)
{
  return code
     // This is a special trim which only removes first and last empty lines
     // and doesn't affect valid leading space on the first line.
    .replace(/^[ ]*[\n]+|[\n]*[ ]*$/g, '')

    // IE lets these buggers through
    .replace(/\r/g, ' ')
    ;
};