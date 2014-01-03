module.exports = {
  space : '&nbsp;',

  /** Enables use of <SCRIPT type="syntaxhighlighter" /> tags. */
  useScriptTags : true,

  /** Blogger mode flag. */
  bloggerMode : false,

  stripBrs : false,

  /** Name of the tag that SyntaxHighlighter will automatically look for. */
  tagName : 'pre',

  strings : {
    expandSource : 'expand source',
    help : '?',
    alert: 'SyntaxHighlighter\n\n',
    noBrush : 'Can\'t find brush for: ',
    brushNotHtmlScript : 'Brush wasn\'t configured for html-script option: ',

    // this is populated by the build script
    aboutDialog : require('./about.coffee')
  }
};
