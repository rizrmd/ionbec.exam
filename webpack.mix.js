const mix = require('laravel-mix')

mix.js('resources/js/app.js', 'public/js').vue()
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .webpackConfig(require('./webpack.config'))

// Bound minification memory on shared deployment/build hosts.
mix.options({ terser: { parallel: false } })

mix.sourceMaps()
mix.version()
