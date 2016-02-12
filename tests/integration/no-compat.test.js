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

  before(done => {
    div = document.createElement('div');
    div.innerHTML = HTML;

    div.appendChild(createScript('/base/tests/build-dest/syntaxhighlighter.js'));

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

function testSuite() {
  describe('using only the bundle', () => {
    it('highlights v3 brush', () => expect(sizzle('.syntaxhighlighter.html_test_brush_v3')[0]).to.be.ok);
    it('highlights v4 brush', () => expect(sizzle('.syntaxhighlighter.test_brush_v4')[0]).to.be.ok);
    it('highlights v4 ES6 brush', () => expect(sizzle('.syntaxhighlighter.test_brush_v4_es6')[0]).to.be.ok);
  });

  it('does not expose window.SyntaxHighlighter', () => expect(window.SyntaxHighlighter).to.be.undefined);
}

describe('integration/no-compat', () => {
  describe('default settings', () => {
    setupSyntaxHighlighter();
    testSuite();
  });

  describe('user settings', () => {
    function test(config) {
      before(() => window.syntaxhighlighterConfig = config);
      after(() => delete window.syntaxhighlighterConfig);

      setupSyntaxHighlighter();
      testSuite();

      it('applies custom class name from global config variable to all units', () =>
         expect(sizzle('.foo-bar.syntaxhighlighter').length).to.equal(3)
      );
    }

    describe('dash-case arguments', () => test({'class-name': 'foo-bar'}));
    describe('camel-case arguments', () => test({'className': 'foo-bar'}));
  });
});
