/**
 * Technical Approach Test Suite
 * Tests for animations, interactions, and progress bar functionality
 * 
 * @package Kei_Portfolio
 */

describe('Technical Approach Functionality', () => {
  let mockValuesSection, mockValueItems;
  let mockApproachCards, mockDownloadBtn;
  let observerCallback;
  
  beforeEach(() => {
    // Setup DOM structure
    document.body.innerHTML = `
      <div class="space-y-6">
        <div class="value-item" data-percentage="90">
          <div class="progress-bar" style="width: 0%"></div>
          <div class="percentage-counter">0%</div>
        </div>
        <div class="value-item" data-percentage="85">
          <div class="progress-bar" style="width: 0%"></div>
          <div class="percentage-counter">0%</div>
        </div>
        <div class="value-item" data-percentage="95">
          <div class="progress-bar" style="width: 0%"></div>
          <div class="percentage-counter">0%</div>
        </div>
      </div>
      <div class="approach-card">Card 1</div>
      <div class="approach-card">Card 2</div>
      <button class="download-btn">Download</button>
    `;
    
    // Get elements
    mockValuesSection = document.querySelector('.space-y-6');
    mockValueItems = document.querySelectorAll('.value-item');
    mockApproachCards = document.querySelectorAll('.approach-card');
    mockDownloadBtn = document.querySelector('.download-btn');
    
    // Mock IntersectionObserver
    observerCallback = null;
    global.IntersectionObserver = jest.fn().mockImplementation((callback) => ({
      observe: jest.fn((target) => {
        observerCallback = callback;
      }),
      unobserve: jest.fn(),
      disconnect: jest.fn()
    }));
    
    // Reset mocks
    jest.clearAllMocks();
    global.alert = jest.fn();
    jest.useFakeTimers();
  });
  
  afterEach(() => {
    jest.clearAllMocks();
    jest.useRealTimers();
  });
  
  describe('Progress Bar Animation', () => {
    test('should initialize IntersectionObserver on page load', () => {
      require('../../assets/js/technical-approach.js');
      
      expect(global.IntersectionObserver).toHaveBeenCalledWith(
        expect.any(Function),
        { threshold: 0.5 }
      );
    });
    
    test('should observe values section if it exists', () => {
      const mockObserver = {
        observe: jest.fn(),
        unobserve: jest.fn()
      };
      
      global.IntersectionObserver = jest.fn().mockImplementation(() => mockObserver);
      
      require('../../assets/js/technical-approach.js');
      
      expect(mockObserver.observe).toHaveBeenCalledWith(mockValuesSection);
    });
    
    test('should animate progress bars when intersection occurs', () => {
      require('../../assets/js/technical-approach.js');
      
      // Trigger intersection observer callback
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      // Fast forward to complete all animations
      jest.advanceTimersByTime(1000);
      
      // Check first progress bar
      const firstProgressBar = mockValueItems[0].querySelector('.progress-bar');
      expect(firstProgressBar.style.width).toBe('90%');
      
      // Check second progress bar (with delay)
      const secondProgressBar = mockValueItems[1].querySelector('.progress-bar');
      expect(secondProgressBar.style.width).toBe('85%');
      
      // Check third progress bar (with more delay)
      const thirdProgressBar = mockValueItems[2].querySelector('.progress-bar');
      expect(thirdProgressBar.style.width).toBe('95%');
    });
    
    test('should animate percentage counters', () => {
      require('../../assets/js/technical-approach.js');
      
      // Mock setInterval to control animation
      let intervalCallbacks = [];
      global.setInterval = jest.fn((callback) => {
        intervalCallbacks.push(callback);
        return intervalCallbacks.length;
      });
      
      global.clearInterval = jest.fn();
      
      // Trigger intersection observer callback
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      // Execute delayed animations
      jest.advanceTimersByTime(600); // Account for staggered animation delays
      
      // Simulate multiple interval calls for counter animation
      intervalCallbacks.forEach(callback => {
        for (let i = 0; i < 65; i++) { // Simulate animation steps
          callback();
        }
      });
      
      // Check counters have been updated
      const firstCounter = mockValueItems[0].querySelector('.percentage-counter');
      expect(firstCounter.textContent).toMatch(/\d+%/);
      
      const secondCounter = mockValueItems[1].querySelector('.percentage-counter');
      expect(secondCounter.textContent).toMatch(/\d+%/);
      
      const thirdCounter = mockValueItems[2].querySelector('.percentage-counter');
      expect(thirdCounter.textContent).toMatch(/\d+%/);
    });
    
    test('should unobserve after animation triggers', () => {
      const mockObserver = {
        observe: jest.fn(),
        unobserve: jest.fn()
      };
      
      global.IntersectionObserver = jest.fn().mockImplementation((callback) => {
        observerCallback = callback;
        return mockObserver;
      });
      
      require('../../assets/js/technical-approach.js');
      
      // Trigger intersection
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      expect(mockObserver.unobserve).toHaveBeenCalledWith(mockValuesSection);
    });
    
    test('should handle staggered animation delays', () => {
      require('../../assets/js/technical-approach.js');
      
      const setTimeoutCalls = [];
      global.setTimeout = jest.fn((callback, delay) => {
        setTimeoutCalls.push({ callback, delay });
        return delay;
      });
      
      // Trigger intersection
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      // Check staggered delays
      expect(setTimeoutCalls[0].delay).toBe(0);
      expect(setTimeoutCalls[1].delay).toBe(200);
      expect(setTimeoutCalls[2].delay).toBe(400);
      
      // Execute callbacks
      setTimeoutCalls.forEach(call => call.callback());
      
      // Verify animations started
      mockValueItems.forEach((item, index) => {
        const progressBar = item.querySelector('.progress-bar');
        const percentage = parseInt(item.dataset.percentage);
        expect(progressBar.style.width).toBe(percentage + '%');
      });
    });
  });
  
  describe('Counter Animation', () => {
    test('should animate counter from 0 to target percentage', () => {
      require('../../assets/js/technical-approach.js');
      
      const counter = mockValueItems[0].querySelector('.percentage-counter');
      let currentValue = 0;
      
      // Mock setInterval to control animation
      global.setInterval = jest.fn((callback) => {
        // Simulate animation steps
        const targetValue = 90;
        const increment = targetValue / (1000 / 16);
        
        while (currentValue < targetValue) {
          currentValue = Math.min(currentValue + increment, targetValue);
          counter.textContent = Math.floor(currentValue) + '%';
          callback();
        }
        
        return 1;
      });
      
      // Trigger animation
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      jest.advanceTimersByTime(0); // Execute immediate timeout
      
      // Check final value
      expect(counter.textContent).toBe('90%');
    });
    
    test('should clear interval when animation completes', () => {
      require('../../assets/js/technical-approach.js');
      
      let intervalId = null;
      global.setInterval = jest.fn((callback) => {
        intervalId = Math.random();
        // Simulate completing animation
        for (let i = 0; i < 100; i++) {
          callback();
        }
        return intervalId;
      });
      
      global.clearInterval = jest.fn();
      
      // Trigger animation
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      jest.advanceTimersByTime(0);
      
      // Verify clearInterval was called
      expect(global.clearInterval).toHaveBeenCalled();
    });
  });
  
  describe('Approach Cards Hover Effects', () => {
    test('should add hover effect on mouseenter', () => {
      require('../../assets/js/technical-approach.js');
      
      const card = mockApproachCards[0];
      const mouseEnterEvent = new Event('mouseenter');
      
      card.dispatchEvent(mouseEnterEvent);
      
      expect(card.style.transform).toBe('translateY(-5px)');
      expect(card.style.transition).toBe('transform 0.3s ease');
    });
    
    test('should remove hover effect on mouseleave', () => {
      require('../../assets/js/technical-approach.js');
      
      const card = mockApproachCards[0];
      
      // First add hover effect
      const mouseEnterEvent = new Event('mouseenter');
      card.dispatchEvent(mouseEnterEvent);
      
      // Then remove it
      const mouseLeaveEvent = new Event('mouseleave');
      card.dispatchEvent(mouseLeaveEvent);
      
      expect(card.style.transform).toBe('translateY(0)');
    });
    
    test('should apply hover effects to all approach cards', () => {
      require('../../assets/js/technical-approach.js');
      
      mockApproachCards.forEach(card => {
        const mouseEnterEvent = new Event('mouseenter');
        card.dispatchEvent(mouseEnterEvent);
        
        expect(card.style.transform).toBe('translateY(-5px)');
        expect(card.style.transition).toBe('transform 0.3s ease');
      });
    });
  });
  
  describe('Download Button', () => {
    test('should handle download button click', () => {
      require('../../assets/js/technical-approach.js');
      
      const clickEvent = new Event('click');
      mockDownloadBtn.dispatchEvent(clickEvent);
      
      expect(global.alert).toHaveBeenCalledWith('実績資料のダウンロードを開始します。（実装予定）');
    });
    
    test('should not throw error if download button does not exist', () => {
      // Remove download button
      document.body.innerHTML = `
        <div class="space-y-6">
          <div class="value-item" data-percentage="90">
            <div class="progress-bar"></div>
            <div class="percentage-counter">0%</div>
          </div>
        </div>
      `;
      
      // Should not throw error
      expect(() => {
        require('../../assets/js/technical-approach.js');
      }).not.toThrow();
    });
  });
  
  describe('Edge Cases and Error Handling', () => {
    test('should handle missing values section gracefully', () => {
      document.body.innerHTML = '';
      
      expect(() => {
        require('../../assets/js/technical-approach.js');
      }).not.toThrow();
    });
    
    test('should handle missing data-percentage attribute', () => {
      document.body.innerHTML = `
        <div class="space-y-6">
          <div class="value-item">
            <div class="progress-bar"></div>
            <div class="percentage-counter">0%</div>
          </div>
        </div>
      `;
      
      require('../../assets/js/technical-approach.js');
      
      // Trigger animation
      const valuesSection = document.querySelector('.space-y-6');
      observerCallback([{
        isIntersecting: true,
        target: valuesSection
      }]);
      
      jest.advanceTimersByTime(0);
      
      // Should handle NaN gracefully
      const progressBar = document.querySelector('.progress-bar');
      expect(progressBar.style.width).toBe('NaN%');
    });
    
    test('should handle missing progress bar element', () => {
      document.body.innerHTML = `
        <div class="space-y-6">
          <div class="value-item" data-percentage="90">
            <div class="percentage-counter">0%</div>
          </div>
        </div>
      `;
      
      expect(() => {
        require('../../assets/js/technical-approach.js');
        
        const valuesSection = document.querySelector('.space-y-6');
        observerCallback([{
          isIntersecting: true,
          target: valuesSection
        }]);
        
        jest.advanceTimersByTime(0);
      }).not.toThrow();
    });
    
    test('should handle missing percentage counter element', () => {
      document.body.innerHTML = `
        <div class="space-y-6">
          <div class="value-item" data-percentage="90">
            <div class="progress-bar"></div>
          </div>
        </div>
      `;
      
      expect(() => {
        require('../../assets/js/technical-approach.js');
        
        const valuesSection = document.querySelector('.space-y-6');
        observerCallback([{
          isIntersecting: true,
          target: valuesSection
        }]);
        
        jest.advanceTimersByTime(0);
      }).not.toThrow();
    });
    
    test('should not trigger animation when not intersecting', () => {
      require('../../assets/js/technical-approach.js');
      
      const progressBar = mockValueItems[0].querySelector('.progress-bar');
      const initialWidth = progressBar.style.width;
      
      // Trigger with isIntersecting = false
      observerCallback([{
        isIntersecting: false,
        target: mockValuesSection
      }]);
      
      jest.advanceTimersByTime(1000);
      
      // Progress bar should not have changed
      expect(progressBar.style.width).toBe(initialWidth);
    });
  });
  
  describe('Performance and Memory', () => {
    test('should not create memory leaks with event listeners', () => {
      const addEventListener = jest.spyOn(EventTarget.prototype, 'addEventListener');
      
      require('../../assets/js/technical-approach.js');
      
      // Count event listeners added
      const totalListeners = addEventListener.mock.calls.length;
      
      // Should have appropriate number of listeners
      // 2 for each approach card (mouseenter, mouseleave) + 1 for download button
      const expectedListeners = (mockApproachCards.length * 2) + 1;
      expect(totalListeners).toBe(expectedListeners);
      
      addEventListener.mockRestore();
    });
    
    test('should complete animations within reasonable time', () => {
      require('../../assets/js/technical-approach.js');
      
      // Trigger animation
      observerCallback([{
        isIntersecting: true,
        target: mockValuesSection
      }]);
      
      // Animation should complete within 2 seconds
      jest.advanceTimersByTime(2000);
      
      // All counters should show final values
      mockValueItems.forEach(item => {
        const counter = item.querySelector('.percentage-counter');
        const expectedPercentage = parseInt(item.dataset.percentage);
        expect(counter.textContent).toBe(expectedPercentage + '%');
      });
    });
  });
});