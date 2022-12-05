const Encore = require('@symfony/webpack-encore');
//const CopyWebpackPlugin = require('copy-webpack-plugin');

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
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

/*
 * ENTRY CONFIG
 *
 * Each entry will result in one JavaScript file (e.g. app.js)
 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
 */
.addEntry('app', './assets/app.js')
    .addEntry('admin', './assets/admin.js')
    .addEntry('admin-tools', './assets/admin-tools.js')
    .addEntry('admin-dates-reservation-data', './assets/admin-dates-reservation-data-manager.js')
    .addEntry('admin-send-message', './assets/admin-send-message.js')
    .addEntry('admin-parentChild-select-form', './assets/admin_parentChild_select_form.js') //??
    .addEntry('admin-inscriptions', './assets/admin-inscriptions.js')
    .addEntry('admin-media', './assets/media.js')
    .addEntry('admin-upload-media', './assets/upload-media.js')
    .addEntry('datepicker', './assets/datepicker.js')
    .addEntry('documents', './assets/documents.js')
    .addEntry('reservation', './assets/reservation.js')
    .addEntry('reservation-payment', './assets/reservation-payment.js')
    .addEntry('reservation-data-manager', './assets/reservation-data-manager.js')
    .addEntry('reservation-dropzone', './assets/reservation-dropzone.js')
    .addEntry('reservation-docs', './assets/reservation-docs.js')
    .addEntry('reservation-travellers', './assets/reservation-travellers.js')
    .addEntry('reservation-invoices', './assets/reservation-invoices.js')
    .addEntry('photogallery', './assets/photogallery.js')
    .addEntry('reservations-user', './assets/reservations-user.js')
    .addEntry('infodocs', './assets/infodocs.js')
    .addEntry('popups', './assets/popups.js')
    .addEntry('readmore', './assets/readmore.js')
    .addEntry('cookie-notice', './assets/cookie-notice.js')

//.addStyleEntry('photogalleryStyle', './assets/styles/photogallery.css')
.addStyleEntry('email', './assets/styles/email.scss')
    .addStyleEntry('reservations', './assets/styles/reservations.scss')
    .addStyleEntry('reservations-admin', './assets/styles/reservations_admin.scss')

// enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
.enableStimulusBridge('./assets/controllers.json')

// When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
.splitEntryChunks()

// will require an extra script tag for runtime.js
// but, you probably want this, unless you're building a single-page app
//.enableSingleRuntimeChunk()
.disableSingleRuntimeChunk()

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
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

.configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
})



// enables Sass/SCSS support
.enableSassLoader()


// configure Babel
// .configureBabel((config) => {
//     config.plugins.push('@babel/a-babel-plugin');
// })

// enables and configure @babel/preset-env polyfills
.configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = '3.23';
})


// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
.autoProvidejQuery()

//.addPlugin(new CopyWebpackPlugin({
//    patterns: [
//        { from: './assets/fonts', to: 'fonts' }
//    ],
//})) */
.copyFiles([
    { from: './node_modules/ckeditor/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false },
    { from: './node_modules/ckeditor/adapters', to: 'ckeditor/adapters/[path][name].[ext]' },
    { from: './node_modules/ckeditor/lang', to: 'ckeditor/lang/[path][name].[ext]' },
    { from: './node_modules/ckeditor/plugins', to: 'ckeditor/plugins/[path][name].[ext]' },
    { from: './node_modules/ckeditor/skins', to: 'ckeditor/skins/[path][name].[ext]' }
]);


module.exports = Encore.getWebpackConfig();