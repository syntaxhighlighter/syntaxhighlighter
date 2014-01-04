describe '3.x-compat/smoke_check', ->
  pre = highlighter = null

  before ->
    pre = $ '<pre class="brush: compat">hello world</pre>'
    $(document.body).append pre
    SyntaxHighlighter.highlight()
    highlighter = $ '.syntaxhighlighter'

  after ->
    pre.remove()
    highlighter.remove()

  it 'creates highlighter element', ->
    expect(highlighter.length).to.equal 1

  describe 'highlighted element', ->
    it 'has gutter', ->
      expect(highlighter).to.have 'td.gutter'

    it 'has code', ->
      expect(highlighter).to.have 'td.code'

    it 'has keywords', ->
      expect(highlighter).to.have '.line.number1 > code.compat.keyword:contains(hello)'
      expect(highlighter).to.have '.line.number1 > code.compat.keyword:contains(world)'

