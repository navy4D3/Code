const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // .setPublicPath(Encore.isDevServer() ? 'http://localhost:8080/build' : '/build')
    // .setManifestKeyPrefix('build/')

    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('datepicker', './assets/js/datepicker.js')
    .addEntry('googlePlace', './assets/js/googlePlace.js')
    .addEntry('searchTrajet', './assets/js/searchTrajet.js')
    .addEntry('registerLogin', './assets/js/registerLogin.js')
    .addEntry('register2', './assets/js/register2.js')
    .addEntry('addTrajet', './assets/js/addTrajet.js')
    .addEntry('addVoiture', './assets/js/addVoiture.js')
    .addEntry('resultatsRecherche', './assets/js/resultatsRecherche.js')
    .addEntry('devenirChauffeur', './assets/js/devenirChauffeur.js')
    .addEntry('reserver', './assets/js/reserver.js')
    .addEntry('profil', './assets/js/profil.js')
    .addEntry('trajet', './assets/js/trajet.js')
    .addEntry('addAvis', './assets/js/addAvis.js')
    .addEntry('employe', './assets/js/employe.js')
    .addEntry('admin', './assets/js/admin.js')
    .addEntry('contact', './assets/js/contact.js')
    .addStyleEntry('style-app', './assets/styles/app.scss')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    // .enableVersioning()
    .enableSassLoader()
    
    // enables hashed filenames (e.g. app.abc123.css)

    .configureDevServerOptions(options => {
        options.allowedHosts = 'all';
        options.liveReload = true;  // Recharge la page en cas de modification
        // options.hot = true;         // Active le Hot Module Replacement (HMR)
        options.static = {
            watch: false
        }
        options.client = { 
            overlay: true,          // Affiche les erreurs dans le navigateur
        };
        options.watchFiles = {
            paths: ['src/**/*.php', 'templates/**/*', 'assets/**/*']
        }
    })
    

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    // .configureBabelPresetEnv((config) => {
    //     config.useBuiltIns = 'usage';
    //     config.corejs = '3.38';
    // })

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
