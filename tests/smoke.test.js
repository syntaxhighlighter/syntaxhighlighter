import sizzle from 'sizzle';
import {expect} from 'chai';
import SyntaxHighlighter, {registerBrush} from '..';

registerBrush(require('brush-xml'));
registerBrush(require('./fixtures/test_brush_v4'));
registerBrush(require('./fixtures/html_test_brush_v4'));

// Simulates `--compat`
window.SyntaxHighlighter = SyntaxHighlighter;

require('./fixtures/test_brush_v3');
require('./fixtures/html_test_brush_v3');

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

describe('integration', function() {
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

  function testElementDetection(brushName) {
    describe('element detection', function() {
      describe(`using '<pre class="brush: ${brushName}">'`, function() {
        beforeEach(() => createHighlighter(`<pre class="brush: ${brushName}">hello world</pre>`));
        itHasCommonElements();
      });

      describe(`using '<script type="syntaxhighlighter" class="brush: ${brushName}">'`, function() {
        beforeEach(() => createHighlighter(`<script type="syntaxhighlighter" class="brush: ${brushName}">hello world</script>`));
        itHasCommonElements();
      });

      describe(`using '<script type="text/syntaxhighlighter" class="brush: ${brushName}">'`, function() {
        beforeEach(() => createHighlighter(`<script type="text/syntaxhighlighter" class="brush: ${brushName}">hello world</script>`));
        itHasCommonElements();
      });
    });
  }

  function testRegularBrush(brushName) {
    describe(`regular brush '<pre class="brush: ${brushName}">'`, function() {
      beforeEach(function() {
        createHighlighter(`
          <pre class="brush: ${brushName}">
            hello world
            how are things?
          </pre>
        `);
      });

      itHasCommonElements();

      describe('class names', function() {
        it('applies brush name', function() {
          expectSelectorToBePresent(highlighter, `td.code .line.number1 > code.${brushName}.keyword:contains(hello)`);
        });
      });
    });
  }

  function testHtmlScriptBrush(brushName) {
    describe(`html-script brush '<pre class="brush: ${brushName}; html-script: true">'`, function() {
      beforeEach(function() {
        createHighlighter(`
          <pre class="brush: ${brushName}; html-script: true">
            world &lt;script>&lt;?= hello world ?>&lt;/script>
            how are you?
          </pre>
        `);
      });

      itHasCommonElements();

      describe('class names', function() {
        it('applies htmlscript class name', function() {
          expectSelectorToBePresent(highlighter, `td.code .line.number1 > code.htmlscript.keyword:contains(script)`, 2);
        });

        it('applies brush class name', function() {
          expectSelectorToBePresent(highlighter, `td.code .line.number1 > code.${brushName}.keyword:contains(hello)`);
        });
      });
    });
  }

  describe('v4 brushes', function () {
    testElementDetection('test_brush_v4');
    testRegularBrush('test_brush_v4');
    testHtmlScriptBrush('html_test_brush_v4')
  });

  describe('v3 brushes', function () {
    testElementDetection('test_brush_v3');
    testRegularBrush('test_brush_v3');
    testHtmlScriptBrush('html_test_brush_v3')
  });
});
