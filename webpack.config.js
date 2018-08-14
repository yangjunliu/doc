const path = require('path')
const HtmlWebpackPlugin = require('html-webpack-plugin')

module.exports = {
    entry: "./src/app.js",
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'public'),
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    module: {
        rules: [
            {
                test: /.jsx$/,
                loader: 'babel-loader'
            },
            {
                test: /.js$/,
                loader: 'babel-loader',
                exclude: [
                    path.resolve(__dirname, 'node_modules')
                ]
            }
        ],
        loaders: [
            {
                test: /\.js$/,
                exclude: /(node_modules|bower_components)/,
                loader: 'babel-loader',
                query: {
                    presets: ['es2015']
                }
            },
            {
                test:/\.html$/,
                loader:'html-loader'
            }
        ]
    },
    plugins: [
        new HtmlWebpackPlugin({template: path.resolve(__dirname, './src/index.html')}),
    ],
    devServer: {
        contentBase: path.resolve(__dirname, 'public'),
        compress: true,
        port: 8888,
    }
}
