module.exports = require('flarum-webpack-config')({
  // Provide the extension IDs of all extensions from which your extension will be importing.
  // Do this for both full and optional dependencies.
  useExtensions: ['flarum-tags', 'flarum-lock', 'flarum-flags'],
});
