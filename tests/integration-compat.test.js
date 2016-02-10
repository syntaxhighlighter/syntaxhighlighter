import sizzle from 'sizzle';
import {expect} from 'chai';

const HTML = require('raw!./build-source/index.html');

describe('integration-compat', function() {
  let div;

  function createScript(src) {
    const script = document.createElement('script');
    script.src = src;
    return script;
  }

  before(function (done) {
    div = document.createElement('div');
    div.innerHTML = HTML;

    div.appendChild(createScript('/base/tests/build-dest/syntaxhighlighter.js'));
    div.appendChild(createScript('/base/tests/build-dest/test_brush_v3.js'));

    document.body.appendChild(div);

    function wait() {
      if (sizzle('.syntaxhighlighter').length) {
        done();
      } else {
        setTimeout(wait, 900);
      }
    }

    wait();
  });

  describe('using <script/> brush', () => {
    it('highlights v3 brush', () => expect(sizzle('.syntaxhighlighter.test_brush_v3')[0]).to.be.ok);
  });

  it('exposes window.SyntaxHighlighter', () => expect(window.SyntaxHighlighter).to.be.ok);
});
