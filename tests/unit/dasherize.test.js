import {expect} from 'chai';
import {string, object} from '../../src/dasherize';

describe('unit/dasherize', () => {
  describe('string', () => {
    it('works', () => expect(string('helloFooBar')).to.equal('hello-foo-bar'));
    it('does not mess up the first character', () => expect(string('HelloFooBar')).to.equal('hello-foo-bar'));
  });

  describe('object', () => {
    it('works', () => expect(object({'helloFooBar': 1})).to.eql({'hello-foo-bar': 1}));
  });
});
