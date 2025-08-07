/**
 * Contact Form Test Suite
 * Tests for contact form validation, character counting, and AJAX submission
 * 
 * @package Kei_Portfolio
 */

describe('Contact Form Functionality', () => {
  let mockForm, mockButton, mockTextarea, mockCounter;
  let mockSuccessMessage, mockErrorMessage;
  let mockSubmitText, mockSubmitLoader;
  
  beforeEach(() => {
    // Setup DOM structure
    document.body.innerHTML = `
      <form id="contact-form">
        <input type="text" name="name" required value="">
        <input type="email" name="email" required value="">
        <textarea id="message" name="message" required></textarea>
        <div id="message-count">0</div>
        <button id="submit-button" type="submit">
          <span id="submit-text">無料相談を申し込む</span>
          <span id="submit-loader" class="hidden"></span>
        </button>
      </form>
      <div id="submit-success-message" class="hidden">
        <p>送信が完了しました。</p>
      </div>
      <div id="submit-error-message" class="hidden">
        <p></p>
      </div>
    `;
    
    // Get elements
    mockForm = document.getElementById('contact-form');
    mockButton = document.getElementById('submit-button');
    mockTextarea = document.getElementById('message');
    mockCounter = document.getElementById('message-count');
    mockSuccessMessage = document.getElementById('submit-success-message');
    mockErrorMessage = document.getElementById('submit-error-message');
    mockSubmitText = document.getElementById('submit-text');
    mockSubmitLoader = document.getElementById('submit-loader');
    
    // Reset jQuery mocks
    jQuery.mockClear();
    jQuery.ajax = jest.fn();
    
    // Load the script
    require('../../assets/js/contact-form.js');
  });
  
  afterEach(() => {
    jest.clearAllMocks();
  });
  
  describe('Character Counter', () => {
    test('should update character count on input', () => {
      const mockJQueryTextarea = {
        on: jest.fn((event, callback) => {
          if (event === 'input') {
            // Simulate input event
            mockTextarea.value = 'Test message';
            callback.call(mockTextarea);
          }
        }),
        val: jest.fn(() => mockTextarea.value)
      };
      
      const mockJQueryCounter = {
        text: jest.fn(),
        parent: jest.fn(() => ({
          addClass: jest.fn(),
          removeClass: jest.fn()
        }))
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#message') return mockJQueryTextarea;
        if (selector === '#message-count') return mockJQueryCounter;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          on: jest.fn(),
          prop: jest.fn()
        };
      });
      
      // Re-run the script with new mocks
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(mockJQueryTextarea.on).toHaveBeenCalledWith('input', expect.any(Function));
      expect(mockJQueryCounter.text).toHaveBeenCalledWith(12); // Length of "Test message"
    });
    
    test('should disable submit button when message exceeds 500 characters', () => {
      const longMessage = 'a'.repeat(501);
      const mockJQueryButton = { prop: jest.fn() };
      const mockJQueryCounter = {
        text: jest.fn(),
        parent: jest.fn(() => ({
          addClass: jest.fn(),
          removeClass: jest.fn()
        }))
      };
      
      const mockJQueryTextarea = {
        on: jest.fn((event, callback) => {
          if (event === 'input') {
            mockTextarea.value = longMessage;
            callback.call(mockTextarea);
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#message') return mockJQueryTextarea;
        if (selector === '#message-count') return mockJQueryCounter;
        if (selector === '#submit-button') return mockJQueryButton;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          on: jest.fn()
        };
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(mockJQueryButton.prop).toHaveBeenCalledWith('disabled', true);
      expect(mockJQueryCounter.parent().addClass).toHaveBeenCalledWith('text-red-500');
    });
    
    test('should enable submit button when message is within limit', () => {
      const normalMessage = 'Normal length message';
      const mockJQueryButton = { prop: jest.fn() };
      const mockJQueryCounter = {
        text: jest.fn(),
        parent: jest.fn(() => ({
          addClass: jest.fn(),
          removeClass: jest.fn()
        }))
      };
      
      const mockJQueryTextarea = {
        on: jest.fn((event, callback) => {
          if (event === 'input') {
            mockTextarea.value = normalMessage;
            callback.call(mockTextarea);
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#message') return mockJQueryTextarea;
        if (selector === '#message-count') return mockJQueryCounter;
        if (selector === '#submit-button') return mockJQueryButton;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          on: jest.fn()
        };
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(mockJQueryButton.prop).toHaveBeenCalledWith('disabled', false);
      expect(mockJQueryCounter.parent().removeClass).toHaveBeenCalledWith('text-red-500');
    });
  });
  
  describe('Form Submission', () => {
    test('should prevent default form submission', () => {
      const preventDefaultSpy = jest.fn();
      const mockEvent = { preventDefault: preventDefaultSpy };
      
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback(mockEvent);
          }
        }),
        0: { reset: jest.fn() }
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          on: jest.fn(),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn()
        };
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(preventDefaultSpy).toHaveBeenCalled();
    });
    
    test('should show loading state during submission', () => {
      const mockJQueryButton = { prop: jest.fn() };
      const mockJQuerySubmitText = { text: jest.fn() };
      const mockJQuerySubmitLoader = { 
        removeClass: jest.fn(() => mockJQuerySubmitLoader),
        addClass: jest.fn(() => mockJQuerySubmitLoader)
      };
      
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-button') return mockJQueryButton;
        if (selector === '#submit-text') return mockJQuerySubmitText;
        if (selector === '#submit-loader') return mockJQuerySubmitLoader;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn()
        };
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(mockJQueryButton.prop).toHaveBeenCalledWith('disabled', true);
      expect(mockJQuerySubmitText.text).toHaveBeenCalledWith('送信中...');
      expect(mockJQuerySubmitLoader.removeClass).toHaveBeenCalledWith('hidden');
      expect(mockJQuerySubmitLoader.addClass).toHaveBeenCalledWith('inline-block');
    });
    
    test('should handle successful AJAX submission', (done) => {
      const mockReset = jest.fn();
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        }),
        0: { reset: mockReset }
      };
      
      const mockJQuerySuccessMessage = {
        removeClass: jest.fn(),
        addClass: jest.fn(),
        offset: jest.fn(() => ({ top: 200 }))
      };
      
      const mockJQueryMessageCount = { text: jest.fn() };
      const mockAnimateSpy = jest.fn((options, duration) => {});
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-success-message') return mockJQuerySuccessMessage;
        if (selector === '#message-count') return mockJQueryMessageCount;
        if (selector === 'html, body') return { animate: mockAnimateSpy };
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn(),
          find: jest.fn(() => ({ text: jest.fn() }))
        };
      });
      
      jQuery.ajax = jest.fn((options) => {
        // Call success callback
        options.success({ success: true });
        // Call complete callback
        options.complete();
        
        // Verify success handling
        expect(mockJQuerySuccessMessage.removeClass).toHaveBeenCalledWith('hidden');
        expect(mockReset).toHaveBeenCalled();
        expect(mockJQueryMessageCount.text).toHaveBeenCalledWith('0');
        expect(mockAnimateSpy).toHaveBeenCalledWith(
          { scrollTop: 100 }, // 200 - 100
          500
        );
        done();
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
    });
    
    test('should handle server error response', (done) => {
      const errorMessage = 'サーバーエラーが発生しました';
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        })
      };
      
      const mockJQueryErrorMessage = {
        removeClass: jest.fn(),
        addClass: jest.fn(),
        find: jest.fn(() => ({ text: jest.fn() }))
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-error-message') return mockJQueryErrorMessage;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn()
        };
      });
      
      jQuery.ajax = jest.fn((options) => {
        // Call success callback with error
        options.success({ success: false, data: errorMessage });
        // Call complete callback
        options.complete();
        
        // Verify error handling
        expect(mockJQueryErrorMessage.find).toHaveBeenCalledWith('p');
        expect(mockJQueryErrorMessage.find().text).toHaveBeenCalledWith(errorMessage);
        expect(mockJQueryErrorMessage.removeClass).toHaveBeenCalledWith('hidden');
        done();
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
    });
    
    test('should handle AJAX network error', (done) => {
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        })
      };
      
      const mockJQueryErrorMessage = {
        removeClass: jest.fn(),
        addClass: jest.fn()
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-error-message') return mockJQueryErrorMessage;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn(),
          find: jest.fn(() => ({ text: jest.fn() }))
        };
      });
      
      jQuery.ajax = jest.fn((options) => {
        // Call error callback
        options.error();
        // Call complete callback
        options.complete();
        
        // Verify error handling
        expect(mockJQueryErrorMessage.removeClass).toHaveBeenCalledWith('hidden');
        done();
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
    });
    
    test('should reset button state after submission', (done) => {
      const mockJQueryButton = { prop: jest.fn() };
      const mockJQuerySubmitText = { text: jest.fn() };
      const mockJQuerySubmitLoader = { addClass: jest.fn() };
      
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-button') return mockJQueryButton;
        if (selector === '#submit-text') return mockJQuerySubmitText;
        if (selector === '#submit-loader') return mockJQuerySubmitLoader;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          find: jest.fn(() => ({ text: jest.fn() })),
          0: { reset: jest.fn() }
        };
      });
      
      jQuery.ajax = jest.fn((options) => {
        // Call complete callback
        options.complete();
        
        // Verify button reset
        expect(mockJQueryButton.prop).toHaveBeenCalledWith('disabled', false);
        expect(mockJQuerySubmitText.text).toHaveBeenCalledWith('無料相談を申し込む');
        expect(mockJQuerySubmitLoader.addClass).toHaveBeenCalledWith('hidden');
        done();
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
    });
    
    test('should send correct FormData with AJAX request', () => {
      const mockFormData = new FormData();
      mockFormData.append = jest.fn();
      
      global.FormData = jest.fn(() => mockFormData);
      
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            const mockThis = document.getElementById('contact-form');
            callback.call(mockThis, { preventDefault: jest.fn() });
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn()
        };
      });
      
      jQuery.ajax = jest.fn();
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      expect(mockFormData.append).toHaveBeenCalledWith('action', 'kei_portfolio_contact_submit');
      expect(jQuery.ajax).toHaveBeenCalledWith(expect.objectContaining({
        url: 'http://localhost/wp-admin/admin-ajax.php',
        type: 'POST',
        data: mockFormData,
        processData: false,
        contentType: false
      }));
    });
  });
  
  describe('Auto-hide Messages', () => {
    test('should auto-hide messages after 5 seconds', () => {
      jest.useFakeTimers();
      
      const mockJQuerySuccessMessage = { addClass: jest.fn() };
      const mockJQueryErrorMessage = { addClass: jest.fn() };
      
      const mockJQueryForm = {
        on: jest.fn((event, callback) => {
          if (event === 'submit') {
            callback({ preventDefault: jest.fn() });
          }
        })
      };
      
      jQuery.mockImplementation((selector) => {
        if (selector === '#contact-form') return mockJQueryForm;
        if (selector === '#submit-success-message') return mockJQuerySuccessMessage;
        if (selector === '#submit-error-message') return mockJQueryErrorMessage;
        return {
          ready: jest.fn(cb => cb(jQuery)),
          addClass: jest.fn(),
          removeClass: jest.fn(),
          prop: jest.fn(),
          text: jest.fn()
        };
      });
      
      jQuery.ajax = jest.fn((options) => {
        options.complete();
      });
      
      jest.isolateModules(() => {
        require('../../assets/js/contact-form.js');
      });
      
      // Fast-forward time by 5 seconds
      jest.advanceTimersByTime(5000);
      
      expect(mockJQuerySuccessMessage.addClass).toHaveBeenCalledWith('hidden');
      expect(mockJQueryErrorMessage.addClass).toHaveBeenCalledWith('hidden');
      
      jest.useRealTimers();
    });
  });
});