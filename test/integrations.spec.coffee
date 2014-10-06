describe 'integrations', ->
  pre = highlighter = null

  cleanup = ->
    pre?.remove()
    highlighter?.remove()
    pre = highlighter = null

  expectSelectorToBePresent = (element, selector, count = 1) ->
    el = $ selector, element
    expect(el).to.exist
    expect(el.length).to.eql count

  render = (html) ->
    cleanup()
    pre = $ html
    $(document.body).append pre
    SyntaxHighlighter.highlight()
    highlighter = $ '.syntaxhighlighter'

  itHasCommonElements = ->
    describe 'highlighted element', ->
      it 'exists', ->
        expect(highlighter.length).to.equal 1

      it 'has gutter', ->
        expectSelectorToBePresent highlighter, 'td.gutter'

      it 'has code', ->
        expectSelectorToBePresent highlighter, 'td.code'

  before ->
    cleanup()

  after ->
    cleanup()

  describe 'element detection', ->
    describe 'using `pre class="..."`', ->
      before -> render """<pre class="brush: compat">hello world</pre>"""
      itHasCommonElements()

    describe 'using `script type="syntaxhighlighter"`', ->
      before -> render """<script type="syntaxhighlighter" class="brush: compat">hello world</script>"""
      itHasCommonElements()

    describe 'using `script type="type/syntaxhighlighter"`', ->
      before -> render """<script type="syntaxhighlighter" class="brush: compat">hello world</script>"""
      itHasCommonElements()

  describe 'regular brush', ->
    before ->
      render """
        <pre class="brush: compat">
          hello world
          how are things?
        </pre>
      """

    itHasCommonElements()

    describe 'class names', ->
      it 'applies brush name', ->
        expectSelectorToBePresent highlighter, 'td.code .line.number1 > code.compat.keyword:contains(hello)'

  describe 'html-script brush', ->
    before ->
      render """
        <pre class="brush: compat-html; html-script: true">
          world &lt;script>&lt;?= hello world ?>&lt;/script>
          how are you?
        </pre>
      """

    itHasCommonElements()

    describe 'class names', ->
      it 'applies htmlscript class name', ->
        expectSelectorToBePresent highlighter, 'td.code .line.number1 > .htmlscript.keyword:contains(script)', 2

      it 'applies brush name', ->
        expectSelectorToBePresent highlighter, 'td.code .line.number1 > code.compathtml.keyword:contains(hello)'
