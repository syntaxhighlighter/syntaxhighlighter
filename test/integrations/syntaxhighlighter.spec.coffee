describe 'SyntaxHighlighter', ->
  it 'is defined', ->
    expect(window.SyntaxHighlighter).to.be.ok

  describe 'sanity check', ->
    pre = highlighter = null

    before ->
      pre = $ '<pre class="brush: plain">hello world</pre>'
      $(document.body).append pre
      window.SyntaxHighlighter.highlight()
      highlighter = $ '.syntaxhighlighter'

    after ->
      pre.remove()
      highlighter.remove()

    it 'creates highlighter element', ->
      expect(highlighter.length).to.equal 1

    describe 'highlighter element', ->
      it 'has gutter', ->
        expect(highlighter).to.have 'td.gutter'

      it 'has code', ->
        expect(highlighter).to.have 'td.code'
