const path = require('path');

module.exports = {
  content: [
    path.resolve(__dirname, 'resources/**/*.{vue,js,ts,jsx,tsx,css,scss}')
  ],
  darkMode: 'class',
  safelist: [],
};
