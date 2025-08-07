/**
 * Jest Test Setup File
 * Sets up the test environment with necessary mocks and globals
 * 
 * @package Kei_Portfolio
 */

// Mock jQuery
global.jQuery = jest.fn((selector) => {
  const element = {
    ready: jest.fn((callback) => callback(global.jQuery)),
    on: jest.fn(),
    off: jest.fn(),
    addClass: jest.fn(),
    removeClass: jest.fn(),
    prop: jest.fn(),
    text: jest.fn(),
    val: jest.fn(),
    parent: jest.fn(() => element),
    find: jest.fn(() => element),
    offset: jest.fn(() => ({ top: 100 })),
    animate: jest.fn(),
    ajax: jest.fn()
  };
  
  // Array-like methods
  element[0] = {
    reset: jest.fn()
  };
  
  return element;
});

global.$ = global.jQuery;

// Mock WordPress AJAX object
global.kei_portfolio_ajax = {
  ajax_url: 'http://localhost/wp-admin/admin-ajax.php',
  nonce: 'test_nonce_123'
};

// Mock window methods
global.alert = jest.fn();

// Mock Intersection Observer
global.IntersectionObserver = jest.fn().mockImplementation((callback, options) => ({
  observe: jest.fn((target) => {
    // Simulate immediate intersection
    callback([{ isIntersecting: true, target }]);
  }),
  unobserve: jest.fn(),
  disconnect: jest.fn()
}));

// Mock FormData
global.FormData = jest.fn().mockImplementation(() => {
  const data = new Map();
  return {
    append: jest.fn((key, value) => data.set(key, value)),
    get: jest.fn((key) => data.get(key)),
    has: jest.fn((key) => data.has(key)),
    delete: jest.fn((key) => data.delete(key)),
    entries: jest.fn(() => data.entries())
  };
});

// Mock setTimeout and setInterval
global.setTimeout = jest.fn((callback, delay) => {
  callback();
  return 1;
});

global.setInterval = jest.fn((callback, interval) => {
  let count = 0;
  const maxCalls = 100; // Prevent infinite loops
  while (count < maxCalls) {
    callback();
    count++;
  }
  return 1;
});

global.clearInterval = jest.fn();
global.clearTimeout = jest.fn();

// Helper function to create mock DOM elements
global.createMockElement = (tag, props = {}) => {
  const element = document.createElement(tag);
  Object.assign(element, props);
  return element;
};

// Helper to simulate events
global.simulateEvent = (element, eventType, eventData = {}) => {
  const event = new Event(eventType, { bubbles: true, cancelable: true });
  Object.assign(event, eventData);
  element.dispatchEvent(event);
  return event;
};