/** @type {import('stylelint').Config} */
export default {
    extends: ['stylelint-config-standard'],

    rules: {
        'at-rule-no-unknown': [
            true,
            {
                ignoreAtRules: [
                    'theme',
                    'source',
                    'utility',
                    'variant',
                    'custom-variant',
                    'plugin',
                    'reference',
                    'apply',
                ],
            },
        ],
        'import-notation': null,
        'declaration-no-important': true,
    },
};
