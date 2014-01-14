describe 'integrations', ->
  pre = highlighter = null

  render = (html) ->
    pre?.remove()
    highlighter?.remove()

    pre = $ html
    $(document.body).append pre
    SyntaxHighlighter.highlight()
    highlighter = $ '.syntaxhighlighter'

  itHasElements = ->
    describe 'highlighted element', ->
      it 'exists', ->
        expect(highlighter.length).to.equal 1

      it 'has gutter', ->
        expect(highlighter).to.have 'td.gutter'

      it 'has code', ->
        expect(highlighter).to.have 'td.code'

  before ->
    pre = highlighter = null

  after ->
    pre?.remove()
    highlighter?.remove()
    pre = highlighter = null

  describe 'element detection', ->
    describe 'using `pre class="..."`', ->
      before -> render """<pre class="brush: compat">hello world</pre>"""
      itHasElements()

    # describe 'using `pre data-syntaxhighlighter="..."`', ->
    #   before -> render """<pre data-syntaxhighlighter="brush: compat">hello world</pre>"""
    #   itHasElements()

    describe 'using `script type="syntaxhighlighter"`', ->
      before -> render """<script type="syntaxhighlighter" class="brush: compat">hello world</script>"""
      itHasElements()

    describe 'using `script type="type/syntaxhighlighter"`', ->
      before -> render """<script type="syntaxhighlighter" class="brush: compat">hello world</script>"""
      itHasElements()

  describe 'regular brush', ->
    before ->
      render """
        <pre class="brush: compat">
          hello world
          how are things?
        </pre>
      """

    itHasElements()

    describe 'class names', ->
      it 'applies brush name', ->
        expect(highlighter).to.have 'td.code .line.number1 > code.compat.keyword:contains(hello)'

  describe 'html-script brush', ->
    before ->
      render """
        <pre class="brush: compat-html; html-script: true">
          world &lt;script>&lt;?= hello world ?>&lt;/script>
          how are you?
        </pre>
      """

    itHasElements()

    describe 'class names', ->
      it 'applies htmlscript class name', ->
        expect(highlighter).to.have 'td.code .line.number1 > .htmlscript.keyword:contains(script)'

      it 'applies brush name', ->
        expect(highlighter).to.have 'td.code .line.number1 > code.compathtml.keyword:contains(hello)'
