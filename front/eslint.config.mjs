import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import tseslint from 'typescript-eslint'

export default tseslint.config(
  js.configs.recommended,
  ...tseslint.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    files: ['assets/**/*.vue'],
    languageOptions: {
      parserOptions: {
        parser: tseslint.parser,
        extraFileExtensions: ['.vue'],
        sourceType: 'module',
      },
    },
  },
  {
    files: ['assets/**/*.ts', 'assets/**/*.vue'],
    rules: {
      // Disallow any — matches PHPStan level 10 philosophy on the frontend
      '@typescript-eslint/no-explicit-any': 'error',
      // Unused variables must be prefixed with _ to be ignored
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
      // Vue component naming: multi-word names enforced by atomic design conventions
      'vue/multi-word-component-names': 'off',
      // Enforce consistent component block order: script → template → style
      'vue/component-tags-order': ['error', { order: ['script', 'template', 'style'] }],
      // Disallow v-html to prevent XSS
      'vue/no-v-html': 'error',
      // Require explicit emits declaration
      'vue/require-explicit-emits': 'error',
      // Require v-bind shorthand (:prop vs v-bind:prop)
      'vue/v-bind-style': ['error', 'shorthand'],
      // Require v-on shorthand (@event vs v-on:event)
      'vue/v-on-style': ['error', 'shorthand'],
    },
  },
  {
    ignores: ['public/**', 'node_modules/**', 'dist/**'],
  },
)
