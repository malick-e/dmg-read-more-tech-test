module.exports = {
	root: true,
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	env: { browser: true, es6: true, jest: true },
	rules: {
		'react/prop-types': 'off',
	},
};
