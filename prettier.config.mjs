/** @type {import("prettier").Config} */
export default {
    plugins: ['prettier-plugin-blade', 'prettier-plugin-tailwindcss'],

    tailwindStylesheet: './resources/css/app.css',

    printWidth: 100,
    tabWidth: 4,
    useTabs: false,
    singleQuote: true,
    semi: true,
    trailingComma: 'es5',

    overrides: [
        {
            files: ['*.blade.php'],
            options: {
                parser: 'blade',
                bladePhpFormatting: 'safe',
            },
        },
    ],
};
