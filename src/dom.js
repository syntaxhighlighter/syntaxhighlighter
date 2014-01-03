/**
 * Checks if target DOM elements has specified CSS class.
 * @param {DOMElement} target Target DOM element to check.
 * @param {String} className Name of the CSS class to check for.
 * @return {Boolean} Returns true if class name is present, false otherwise.
 */
function hasClass(target, className)
{
  return target.className.indexOf(className) != -1;
}

/**
 * Adds CSS class name to the target DOM element.
 * @param {DOMElement} target Target DOM element.
 * @param {String} className New CSS class to add.
 */
function addClass(target, className)
{
  if (!hasClass(target, className))
    target.className += ' ' + className;
}

/**
 * Removes CSS class name from the target DOM element.
 * @param {DOMElement} target Target DOM element.
 * @param {String} className CSS class to remove.
 */
function removeClass(target, className)
{
  target.className = target.className.replace(className, '');
}

/**
 * Adds event handler to the target object.
 * @param {Object} obj    Target object.
 * @param {String} type   Name of the event.
 * @param {Function} func Handling function.
 */
function attachEvent(obj, type, func, scope)
{
  function handler(e)
  {
    e = e || window.event;

    if (!e.target)
    {
      e.target = e.srcElement;
      e.preventDefault = function()
      {
        this.returnValue = false;
      };
    }

    func.call(scope || window, e);
  };

  if (obj.attachEvent)
  {
    obj.attachEvent('on' + type, handler);
  }
  else
  {
    obj.addEventListener(type, handler, false);
  }
}

/**
 * Looks for a child or parent node which has specified classname.
 * Equivalent to jQuery's $(container).find(".className")
 * @param {Element} target Target element.
 * @param {String} search Class name or node name to look for.
 * @param {Boolean} reverse If set to true, will go up the node tree instead of down.
 * @return {Element} Returns found child or parent element on null.
 */
function findElement(target, search, reverse /* optional */)
{
  if (target == null)
    return null;

  var nodes     = reverse != true ? target.childNodes : [ target.parentNode ],
    propertyToFind  = { '#' : 'id', '.' : 'className' }[search.substr(0, 1)] || 'nodeName',
    expectedValue,
    found
    ;

  expectedValue = propertyToFind != 'nodeName'
    ? search.substr(1)
    : search.toUpperCase()
    ;

  // main return of the found node
  if ((target[propertyToFind] || '').indexOf(expectedValue) != -1)
    return target;

  for (var i = 0, l = nodes.length; nodes && i < l && found == null; i++)
    found = findElement(nodes[i], search, reverse);

  return found;
}

/**
 * Looks for a parent node which has specified classname.
 * This is an alias to <code>findElement(container, className, true)</code>.
 * @param {Element} target Target element.
 * @param {String} className Class name to look for.
 * @return {Element} Returns found parent element on null.
 */
function findParentElement(target, className)
{
  return findElement(target, className, true);
}

/**
 * Opens up a centered popup window.
 * @param {String} url    URL to open in the window.
 * @param {String} name   Popup name.
 * @param {int} width   Popup width.
 * @param {int} height    Popup height.
 * @param {String} options  window.open() options.
 * @return {Window}     Returns window instance.
 */
function popup(url, name, width, height, options)
{
  var x = (screen.width - width) / 2,
    y = (screen.height - height) / 2
    ;

  options +=  ', left=' + x +
        ', top=' + y +
        ', width=' + width +
        ', height=' + height
    ;
  options = options.replace(/^,/, '');

  var win = window.open(url, name, options);
  win.focus();
  return win;
}

module.exports = {
  popup: popup,
  hasClass: hasClass,
  addClass: addClass,
  removeClass: removeClass,
  attachEvent: attachEvent,
  findElement: findElement,
  findParentElement: findParentElement
}