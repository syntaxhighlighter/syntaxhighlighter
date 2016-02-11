import sizzle from 'sizzle';
import {expect} from 'chai';

const HTML = require('raw!../build-source/index.html');

function createScript(src) {
  const script = document.createElement('script');
  script.src = src;
  return script;
}

function setupSyntaxHighlighter() {
  let div;

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

  after(() => {
    document.body.removeChild(div);
  });
}

describe('integration/compat', function() {
  describe('`--compat` features', () => {
    setupSyntaxHighlighter();

    describe('using <script/> brush', () => {
      it('highlights v3 brush', () => expect(sizzle('.syntaxhighlighter.test_brush_v3')[0]).to.be.ok);
    });

    it('exposes window.SyntaxHighlighter', () => expect(window.SyntaxHighlighter).to.be.ok);
  });

  describe('when XRegExp is already present', () => {
    before(() => {
      window.XRegExp = '...';
    });

    setupSyntaxHighlighter();

    it('does not overwrite existing instance of XRegExp', () => expect(window.XRegExp).to.eql('...'));
  });
});
