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

module.exports = Match;
