const mix = require('laravel-mix');


// mix.sass('resources/scss/theme-default.scss', 'public/assets/vendor/css')
//    .options({ processCssUrls: false })
//    .webpackConfig({
//      resolve: { modules: ['node_modules'] }
//    })
//    .sourceMaps();
mix.sass('resources/scss/theme-default.scss', 'public/assets/vendor/css/theme-default.css', {
    sassOptions: {
        quietDeps: false,
        verbose: true,
        outputStyle: 'expanded', // <== Ini WAJIB untuk munculin debug/warn
        logger: {
            warn: (message, options) => {
                console.warn('[SASS WARN]', message);
            },
            debug: (message, options) => {
                console.debug('[SASS DEBUG]', message);
            }
        }
    }
});

mix.webpackConfig({
    resolve: {
        modules: ['node_modules']
    },
    stats: {

        warnings: true,     // Pastikan peringatan ditampilkan
        errors: true,       // Pastikan kesalahan ditampilkan
        errorDetails: true, // Tampilkan detail kesalahan
        warningsFilter: [
            /deprecated/,              // buang deprecated
            /@import/,                 // buang warning soal @import
            /will be removed/,         // buang peringatan penghapusan
            /Use /,                    // buang saran "Use xxx instead"
            /instead/,                 // buang kata instead
            /More info/,               // buang More info + URL
            /sass-lang\.com/,          // buang URL
            /global built-in/,         // buang built-in function
        ]
    }
});
