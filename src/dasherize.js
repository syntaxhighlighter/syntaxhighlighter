export const string = value =>
  value
    .replace(/^([A-Z])/g, (_, character) => character.toLowerCase())
    .replace(/([A-Z])/g, (_, character) => '-' + character.toLowerCase());

export const object = value => {
  const result = {};
  Object.keys(value).forEach(key => result[string(key)] = value[key]);
  return result;
}
