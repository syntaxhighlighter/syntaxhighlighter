/**
 * Splits block of text into lines.
 * @param {String} block Block of text.
 * @return {Array} Returns array of lines.
 */
function splitLines(block)
{
  return block.split(/\r?\n/);
}

/**
 * Executes a callback on each line and replaces each line with result from the callback.
 * @param {Object} str      Input string.
 * @param {Object} callback   Callback function taking one string argument and returning a string.
 */
function eachLine(str, callback)
{
  var lines = splitLines(str);

  for (var i = 0, l = lines.length; i < l; i++)
    lines[i] = callback(lines[i], i);

  return lines.join('\n');
}

/**
 * Generates a unique element ID.
 */
function guid(prefix)
{
  return (prefix || '') + Math.round(Math.random() * 1000000).toString();
}

/**
 * Merges two objects. Values from obj2 override values in obj1.
 * Function is NOT recursive and works only for one dimensional objects.
 * @param {Object} obj1 First object.
 * @param {Object} obj2 Second object.
 * @return {Object} Returns combination of both objects.
 */
function merge(obj1, obj2)
{
  var result = {}, name;

  for (name in obj1)
    result[name] = obj1[name];

  for (name in obj2)
    result[name] = obj2[name];

  return result;
}

/**
 * Removes all white space at the begining and end of a string.
 *
 * @param {String} str   String to trim.
 * @return {String}      Returns string without leading and following white space characters.
 */
function trim(str)
{
  return str.replace(/^\s+|\s+$/g, '');
}

/**
 * Converts the source to array object. Mostly used for function arguments and
 * lists returned by getElementsByTagName() which aren't Array objects.
 * @param {List} source Source list.
 * @return {Array} Returns array.
 */
function toArray(source)
{
  return Array.prototype.slice.apply(source);
}

/**
 * Attempts to convert string to boolean.
 * @param {String} value Input string.
 * @return {Boolean} Returns true if input was "true", false if input was "false" and value otherwise.
 */
function toBoolean(value)
{
  var result = {"true" : true, "false" : false}[value];
  return result == null ? value : result;
}

module.exports = {
  splitLines: splitLines,
  eachLine: eachLine,
  guid: guid,
  merge: merge,
  trim: trim,
  toArray: toArray,
  toBoolean: toBoolean
};
