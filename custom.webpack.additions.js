module.exports = {
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                loader: "babel-loader",
                options: {
                    presets: ["react"]
                }
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    }
}