import sizzle from 'sizzle';
import {expect} from 'chai';
import SyntaxHighlighter, {registerBrush} from '..';

registerBrush(require('./test_brush_v4'));
registerBrush(require('./html_test_brush_v4'));
registerBrush(require('brush-xml'));

function expectSelectorToBePresent(element, selector, count = 1) {
  const el = sizzle(selector, element);
  expect(el.length).to.eql(count);
}

function html2element(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  return div;
}

function remove(el) {
  if(el.parentNode) el.parentNode.removeChild(el);
}

describe('integrations', function() {
  let highlighter;
  let pre;

  function createHighlighter(html) {
    pre = html2element(html);
    document.body.appendChild(pre);
    SyntaxHighlighter.highlight();
    highlighter = sizzle('.syntaxhighlighter')[0];
  }

  function itHasCommonElements() {
    describe('highlighted element', function() {
      it('exists', function() {
        expect(highlighter).to.be.ok;
      });

      it('has gutter', function() {
        expectSelectorToBePresent(highlighter, 'td.gutter');
      });

      it('has code', function() {
        expectSelectorToBePresent(highlighter, 'td.code');
      });
    });
  }

  afterEach(function() {
    if (pre) remove(pre);
    if (highlighter) remove(highlighter);

    pre = highlighter = null;
  });

  describe('element detection', function() {
    describe('using `pre class="..."`', function() {
      beforeEach(() => createHighlighter(`<pre class="brush: test_brush">hello world</pre>`));
      itHasCommonElements();
    });

    describe('using `script type="syntaxhighlighter"`', function() {
      beforeEach(() => createHighlighter(`<script type="syntaxhighlighter" class="brush: test_brush">hello world</script>`));
      itHasCommonElements();
    });

    describe('using `script type="type/syntaxhighlighter"`', function() {
      beforeEach(() => createHighlighter(`<script type="syntaxhighlighter" class="brush: test_brush">hello world</script>`));
      itHasCommonElements();
    });
  });

  describe('regular brush', function() {
    beforeEach(function() {
      createHighlighter(`
        <pre class="brush: test_brush">
          hello world
          how are things?
        </pre>
      `);
    });

    itHasCommonElements();

    describe('class names', function() {
      it('applies brush name', function() {
        expectSelectorToBePresent(highlighter, 'td.code .line.number1 > code.test_brush.keyword:contains(hello)');
      });
    });
  });

  describe('html-script brush', function() {
    beforeEach(function() {
      createHighlighter(`
        <pre class="brush: html_test_brush; html-script: true">
          world &lt;script>&lt;?= hello world ?>&lt;/script>
          how are you?
        </pre>
      `);
    });

    itHasCommonElements();

    describe('class names', function() {
      it('applies htmlscript class name', function() {
        expectSelectorToBePresent(highlighter, 'td.code .line.number1 > code.htmlscript.keyword:contains(script)', 2);
      });

      it('applies brush class name', function() {
        expectSelectorToBePresent(highlighter, 'td.code .line.number1 > code.html-test-brush.keyword:contains(hello)');
      });
    });
  });
});
