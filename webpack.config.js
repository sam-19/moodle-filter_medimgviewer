const path = require('path')
const webpack = require('webpack')
const TerserPlugin = require('terser-webpack-plugin')

const ASSET_PATH = process.env.ASSET_PATH || './'

module.exports = {
    mode: 'production',
    devtool: 'source-map',
    entry: {
        "src/loader": { import: path.join(__dirname, 'src', 'index.js') },
        "build/loader.min": { import: path.join(__dirname, 'src', 'index.js') },
    },
    output: {
        path: path.resolve(__dirname, 'amd'),
        publicPath: ASSET_PATH,
        filename: '[name].js',
        chunkFilename: '[name].js?v=[contenthash]',
        libraryTarget: 'amd',
    },
    externals: {
        'core/ajax': {
            amd: 'core/ajax'
        },
        'core/str': {
            amd: 'core/str'
        },
        'core/modal_factory': {
            amd: 'core/modal_factory'
        },
        'core/modal_events': {
            amd: 'core/modal_events'
        },
        'core/fragment': {
            amd: 'core/fragment'
        },
        'core/yui': {
            amd: 'core/yui'
        },
        'core/localstorage': {
            amd: 'core/localstorage'
        },
        'core/notification': {
            amd: 'core/notification'
        },
        'jquery': {
            amd: 'jquery'
        },
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery"
        }),
    ],
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin()],
    },
    resolve: {
        extensions: ['.js', '.json'],
    }
}
