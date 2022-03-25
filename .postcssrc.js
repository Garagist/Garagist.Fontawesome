module.exports = {
    plugins: {
        autoprefixer: true,
        cssnano: {
            preset: ["default", { discardComments: { removeAll: true }, svgo: false }],
        },
    },
};
