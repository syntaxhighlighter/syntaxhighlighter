import {expect} from 'chai';
import Brush from '../fixtures/test_brush_v4';

describe('unit/commonjs', () => {
  let html;

  describe('using a brush', () => {
    let brush;

    before(() => {
      brush = new Brush();
      html = brush.getHtml('hello foo bar world!', {gutter: false});
    });

    it('returns html', () => expect(html).to.be.ok);
    it('renders content', () => expect(html).to.contain('<div class="line number1 index0 alt2"><code class="keyword">hello</code> <code class="plain">foo bar </code><code class="keyword">world</code><code class="plain">!</code></div>'));
    it('does not render gutter', () => expect(html).to.not.contain('class="gutter'));
  });
});
