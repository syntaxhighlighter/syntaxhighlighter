import sizzle from 'sizzle';
import {expect} from 'chai';

const HTML = require('raw!./build-source/index.html');

describe('build', function() {
  let div;

  function createScript(src) {
    const script = document.createElement('script');
    script.src = src;
    return script;
  }

  function waitForSyntaxHighlighter() {
    function next() {
      if (typeof(SyntaxHighlighter) !== 'undefined') {
        SyntaxHighlighter.all();
      } else {
        setTimeout(next, 900);
      }
    }

    next();
  }

  before(function (done) {
    const bootScript = document.createElement('script');
    bootScript.innerHTML = `(${waitForSyntaxHighlighter.toString()})()`;

    div = document.createElement('div');
    div.innerHTML = HTML;

    div.appendChild(createScript('/base/tests/build-dest/syntaxhighlighter.js'));
    div.appendChild(createScript('/base/tests/build-dest/test_brush_v3.js'));
    div.appendChild(bootScript);

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

  it('highlights with legacy v3 brush via <script/>', () => expect(sizzle('.syntaxhighlighter.test_brush_v3')[0]).to.be.ok);
  it('highlights with bundled v3 brush', () => expect(sizzle('.syntaxhighlighter.html_test_brush_v3')[0]).to.be.ok);
  it('highlights with bundled v4 brush', () => expect(sizzle('.syntaxhighlighter.test_brush_v4')[0]).to.be.ok);
});
