/**
 * Jest Configuration for Kei Portfolio WordPress Theme
 * 
 * @package Kei_Portfolio
 */

module.exports = {
  // Test environment
  testEnvironment: 'jsdom',
  
  // Root directory
  rootDir: '.',
  
  // Test file patterns
  testMatch: [
    '**/tests/js/**/*.test.js',
    '**/tests/js/**/*.spec.js'
  ],
  
  // Module file extensions
  moduleFileExtensions: ['js', 'json'],
  
  // Coverage configuration
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.min.js',
    '!assets/js/vendor/**'
  ],
  
  // Coverage thresholds (corrected typo)
  coverageThreshold: {
    global: {
      branches: 75,
      functions: 75,
      lines: 75,
      statements: 75
    },
    './assets/js/main.js': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    },
    './assets/js/navigation.js': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    },
    './assets/js/portfolio-filter.js': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    },
    './assets/js/contact-form.js': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    },
    './assets/js/technical-approach.js': {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  },
  
  // Setup files
  setupFilesAfterEnv: ['<rootDir>/tests/js/setup.js'],
  
  // Transform files
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  
  // Module name mapper for WordPress globals
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/assets/js/$1'
  },
  
  // Globals
  globals: {
    'kei_portfolio_ajax': {
      'ajax_url': 'http://localhost/wp-admin/admin-ajax.php',
      'nonce': 'test_nonce_123'
    }
  },
  
  // Clear mocks between tests
  clearMocks: true,
  
  // Restore mocks between tests
  restoreMocks: true,
  
  // Coverage directory
  coverageDirectory: 'tests/coverage',
  
  // Verbose output
  verbose: true
};