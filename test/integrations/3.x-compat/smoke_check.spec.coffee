describe '3.x-compat/smoke_check', ->
  pre = highlighter = null

  describeHighlightedElement = ->
    describe 'highlighted element', ->
      it 'has gutter', ->
        expect(highlighter).to.have 'td.gutter'

      it 'has code', ->
        expect(highlighter).to.have 'td.code'

      it 'has keywords', ->
        expect(highlighter).to.have 'td.code .line.number1 > code.keyword:contains(hello)'
        expect(highlighter).to.have 'td.code .line.number2 > code.plain:contains(how)'

      it 'has line numbers', ->
        expect(highlighter).to.have 'td.gutter .line.number1:contains(1)'
        expect(highlighter).to.have 'td.gutter .line.number2:contains(2)'

  describe 'regular brush', ->
    before ->
      pre = $ '<pre class="brush: compat">hello world\nhow are things?</pre>'
      $(document.body).append pre
      SyntaxHighlighter.highlight()
      highlighter = $ '.syntaxhighlighter'

    after ->
      pre.remove()
      highlighter.remove()

    it 'creates highlighter element', ->
      expect(highlighter.length).to.equal 1

    describeHighlightedElement()

    describe 'class names', ->
      it 'applies brush name', ->
        expect(highlighter).to.have 'td.code .line.number1 > code.compat.keyword:contains(hello)'

  describe 'html-script brush', ->
    before ->
      pre = $ '<pre class="brush: compat-html; html-script: true">world &lt;script>&lt;?= hello world ?>&lt;/script>\nhow are you?</pre>'
      $(document.body).append pre
      SyntaxHighlighter.highlight()
      highlighter = $ '.syntaxhighlighter'

    after ->
      pre.remove()
      highlighter.remove()

    it 'creates highlighter element', ->
      expect(highlighter.length).to.equal 1

    describeHighlightedElement()

    describe 'class names', ->
      it 'applies htmlscript class name', ->
        expect(highlighter).to.have 'td.code .line.number1 > .htmlscript.keyword:contains(script)'

      it 'applies brush name', ->
        expect(highlighter).to.have 'td.code .line.number1 > code.compathtml.keyword:contains(hello)'
