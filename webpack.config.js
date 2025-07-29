const path = require('path');

module.exports = {
  resolve: {
    alias: {
      '@': path.resolve('resources/js'),
      '@images': path.resolve('resources/images'),
    },
  },
};
