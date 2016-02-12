import sizzle from 'sizzle';
import {expect} from 'chai';
import SyntaxHighlighter, {registerBrush, clearRegisteredBrushes} from '../..';
import Brush from '../fixtures/test_brush_v4';

function setupSyntaxHighlighter(html) {
  let div;

  before(done => {
    registerBrush(Brush);

    div = document.createElement('div');
    div.innerHTML = html;
    document.body.appendChild(div);

    SyntaxHighlighter.highlight({gutter: false});

    function wait() {
      if (sizzle('.syntaxhighlighter').length) {
        done();
      } else {
        setTimeout(wait, 900);
      }
    }

    wait();
  });

  after(() => {
    clearRegisteredBrushes();
    document.body.removeChild(div);
  });
}

describe('integration/commonjs', () => {
  describe('first render pass', () => {
    setupSyntaxHighlighter(`<pre class="brush: test_brush_v4;">first</pre>`);
    it('has applied the brush', () => expect(sizzle('.syntaxhighlighter')[0].innerHTML).to.contain(`<code class="test_brush_v4 plain">first</code>`));
    it('does not render gutter', () => expect(sizzle('.syntaxhighlighter td.gutter').length).to.equal(0));
  });

  describe('second render pass', () => {
    setupSyntaxHighlighter(`<pre class="brush: test_brush_v4;">second</pre>`);
    it('has applied the brush', () => expect(sizzle('.syntaxhighlighter')[0].innerHTML).to.contain(`<code class="test_brush_v4 plain">second</code>`));
  });
});
