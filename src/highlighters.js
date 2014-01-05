var highlighters = {};

module.exports = {
  id: function(id)
  {
    var prefix = 'highlighter_';
    return id.indexOf(prefix) == 0 ? id : prefix + id;
  },

  get: function(id)
  {
    return highlighters[this.id(id)];
  },

  set: function(id, highlighter)
  {
    highlighters[this.id(id)] = highlighter;
  }
};