tabs = require '../../../src/preparsers/tabs'

CODE_4 = """
  the\t\twords\tin\t\tthis\tparagraph
  should\tlook\tlike\tthey\tare
  evenly\tspaced\tbetween\tcolumns
"""

CODE_8 = """
  the\twords\t\tin\t\tthis\t\tparagraph
  should\tlook\t\tlike\t\tthey\t\tare
  evenly\tspaced\t\tbetween\t\tcolumns
"""

describe 'preparsers/tabs', ->
  describe 'smart tabs', ->
    it 'uses 4 spaces', ->
      actual = tabs CODE_4, 'smart-tabs': true, 'tab-size': 4
      expect(actual).to.equal """
        the     words   in      this    paragraph
        should  look    like    they    are
        evenly  spaced  between columns
      """

    it 'uses 8 spaces', ->
      actual = tabs CODE_8, 'smart-tabs': true, 'tab-size': 8
      expect(actual).to.equal """
        the     words           in              this            paragraph
        should  look            like            they            are
        evenly  spaced          between         columns
      """

  describe 'regular tabs', ->
    it 'uses 4 spaces', ->
      actual = tabs CODE_4, 'tab-size': 4
      expect(actual).to.equal """
        the        words    in        this    paragraph
        should    look    like    they    are
        evenly    spaced    between    columns
      """

    it 'uses 8 spaces', ->
      actual = tabs CODE_8, 'tab-size': 8
      expect(actual).to.equal """
        the        words                in                this                paragraph
        should        look                like                they                are
        evenly        spaced                between                columns
      """
