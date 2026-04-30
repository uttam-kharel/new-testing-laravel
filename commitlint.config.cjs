module.exports = {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'type-enum': [
            2,
            'always',
            [
                'feat',
                'fix',
                'chore',
                'docs',
                'style',
                'refactor',
                'test',
                'perf',
                'ci',
                'build'
            ],
        ],
        'body-leading-blank': [0],
    },
};
