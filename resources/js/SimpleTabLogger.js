/**
 * Simple Tab Logger for Multiple Screen Detection
 * Detects and logs multiple tab/window usage during exams
 */
export class SimpleTabLogger {
    constructor(attemptId, config = {}) {
        this.attemptId = attemptId;
        this.sessionKey = this.generateSessionKey();
        this.tabId = this.generateTabId();

        this.config = {
            logEndpoint: '/api/exam/log-multiple-tabs',
            checkInterval: 5000, // 5 seconds
            heartbeatInterval: 120000, // 2 minutes
            flushInterval: 15000, // batch client-side, keep single-log request shape
            minEventInterval: 10000,
            maxQueueSize: 50,
            maxRetries: 3,
            retryDelay: 1000,
            debug: false,
            ...config
        };

        this.tabCount = 1;
        this.lastTabCheck = Date.now();
        this.logQueue = [];
        this.isOnline = navigator.onLine;
        this.retryCount = 0;
        this.lastEventAt = {};
        this.eventListeners = [];
        this.retryTimeouts = [];

        this.debugLog('SimpleTabLogger initialized', {
            attemptId: this.attemptId,
            sessionKey: this.sessionKey,
            tabId: this.tabId
        });

        this.init();
    }

    init() {
        // Register this tab
        this.registerTab();

        // Start monitoring
        this.startTabMonitoring();
        this.startHeartbeat();
        this.startQueueFlush();

        // Setup event listeners
        this.setupEventListeners();

        // Initial log
        this.logEvent('exam_started', 'Tab logger initialized');
        this.flushLogQueue();
    }

    generateSessionKey() {
        const timestamp = Date.now().toString(36);
        const random = Math.random().toString(36).substr(2, 9);
        return `exam_${this.attemptId}_${timestamp}_${random}`;
    }

    generateTabId() {
        return `tab_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
    }

    registerTab() {
        try {
            const storageKey = `exam_session_${this.attemptId}`;
            const tabData = {
                tabId: this.tabId,
                sessionKey: this.sessionKey,
                timestamp: Date.now(),
                userAgent: navigator.userAgent
            };

            // Check for existing tabs
            const existingTabData = localStorage.getItem(storageKey);
            if (existingTabData) {
                const existing = JSON.parse(existingTabData);
                this.debugLog('Existing tab detected:', existing);

                if (existing.tabId !== this.tabId) {
                    this.tabCount++;
                    this.logEvent('multiple_tabs_detected', `New tab ${this.tabId} detected existing tab ${existing.tabId}`);
                }
            }

            // Set current tab as active
            localStorage.setItem(storageKey, JSON.stringify(tabData));

        } catch (error) {
            console.warn('Failed to register tab:', error);
        }
    }

    startTabMonitoring() {
        this.tabCheckInterval = setInterval(() => {
            this.checkForMultipleTabs();
        }, this.config.checkInterval);
    }

    checkForMultipleTabs() {
        try {
            const storageKey = `exam_session_${this.attemptId}`;
            const currentTabData = localStorage.getItem(storageKey);

            if (!currentTabData) {
                // Tab data cleared - another tab might have taken over
                this.logEvent('tab_data_cleared', 'Tab data was cleared by another tab');
                this.registerTab(); // Re-register this tab
                return;
            }

            const currentData = JSON.parse(currentTabData);

            // If current active tab is different, multiple tabs are active
            if (currentData.tabId !== this.tabId) {
                if (this.tabCount === 1) { // Only log when first detected
                    this.logEvent('multiple_tabs_active', `Active tab: ${currentData.tabId}, This tab: ${this.tabId}`);
                    this.tabCount++;
                }
            } else {
                if (this.tabCount > 1) { // Multiple tabs were detected but now this is active
                    this.logEvent('tab_focus_regained', `Focus regained to tab ${this.tabId}`);
                    this.tabCount = 1;
                }
            }

            // Update timestamp to show this tab is active
            currentData.timestamp = Date.now();
            localStorage.setItem(storageKey, JSON.stringify(currentData));

        } catch (error) {
            console.warn('Error checking for multiple tabs:', error);
        }
    }

    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            this.logEvent('heartbeat', `Tab ${this.tabId} is active, ${this.tabCount} tabs detected`);
        }, this.config.heartbeatInterval);
    }

    startQueueFlush() {
        this.flushInterval = setInterval(() => {
            this.flushLogQueue();
        }, this.config.flushInterval);
    }

    setupEventListeners() {
        // Page visibility
        this.addEventListener(document, 'visibilitychange', () => {
            const isVisible = !document.hidden;
            this.logEvent('visibility_change', isVisible ? 'Page became visible' : 'Page became hidden');
        });

        // Window focus/blur
        this.addEventListener(window, 'focus', () => {
            this.logEvent('window_focus', 'Window gained focus');
        });

        this.addEventListener(window, 'blur', () => {
            this.logEvent('window_blur', 'Window lost focus');
        });

        // Network connectivity
        this.addEventListener(window, 'online', () => {
            this.isOnline = true;
            this.logEvent('network_online', 'Connection restored');
            this.flushLogQueue();
        });

        this.addEventListener(window, 'offline', () => {
            this.isOnline = false;
            this.logEvent('network_offline', 'Connection lost');
        });

        // Before unload - clean up
        this.addEventListener(window, 'beforeunload', () => {
            this.logEvent('tab_closing', `Tab ${this.tabId} is closing`);
            this.flushWithBeacon();
            this.cleanup(false);
        });
    }

    addEventListener(target, eventName, handler) {
        target.addEventListener(eventName, handler);
        this.eventListeners.push({ target, eventName, handler });
    }

    shouldThrottle(eventType, notes) {
        const now = Date.now();
        const key = `${eventType}:${notes}`;
        const noisyEvents = ['heartbeat', 'visibility_change', 'window_focus', 'window_blur'];

        if (!noisyEvents.includes(eventType)) {
            return false;
        }

        if (this.lastEventAt[key] && now - this.lastEventAt[key] < this.config.minEventInterval) {
            return true;
        }

        this.lastEventAt[key] = now;
        return false;
    }

    logEvent(eventType, notes = '') {
        if (this.shouldThrottle(eventType, notes)) {
            return;
        }

        const logEntry = {
            attempt_id: this.attemptId,
            session_key: this.sessionKey,
            tab_count: this.tabCount,
            event_type: eventType,
            notes: notes,
            tab_id: this.tabId,
            timestamp: Date.now(),
            user_agent: navigator.userAgent
        };

        this.logQueue.push(logEntry);
        if (this.logQueue.length > this.config.maxQueueSize) {
            this.logQueue.splice(0, this.logQueue.length - this.config.maxQueueSize);
        }

        if (this.isOnline) {
            this.flushLogQueue();
        }

        this.debugLog('TabLogger Event:', eventType, notes);
    }

    async sendLog(logEntry) {
        try {
            const response = await fetch(this.config.logEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(logEntry)
            });

            if (response.ok) {
                this.retryCount = 0; // Reset retry count on success
                this.debugLog('Log sent successfully');
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            console.warn('Failed to send log entry:', error);
            this.logQueue.unshift(logEntry);

            // Retry logic
            if (this.retryCount < this.config.maxRetries) {
                this.retryCount++;
                const retryTimeout = setTimeout(() => {
                    this.retryTimeouts = this.retryTimeouts.filter((id) => id !== retryTimeout);
                    this.retryFailedLogs();
                }, this.config.retryDelay * this.retryCount);
                this.retryTimeouts.push(retryTimeout);
            }
        }
    }

    async retryFailedLogs() {
        if (this.logQueue.length === 0 || !this.isOnline) return;

        const logs = [...this.logQueue];
        this.logQueue = [];

        for (const log of logs) {
            await this.sendLog(log);
            if (!this.isOnline) {
                break;
            }
        }
    }

    async flushLogQueue() {
        await this.retryFailedLogs();
    }

    flushWithBeacon() {
        if (!navigator.sendBeacon || this.logQueue.length === 0) return;

        const logs = [...this.logQueue];
        this.logQueue = [];

        logs.forEach((log) => {
            const payload = new Blob([JSON.stringify(log)], { type: 'application/json' });
            navigator.sendBeacon(this.config.logEndpoint, payload);
        });
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    cleanup(sendClosingLog = true) {
        if (sendClosingLog) {
            this.logEvent('tab_closing', `Tab ${this.tabId} is closing`);
            this.flushWithBeacon();
        }

        // Clear intervals
        if (this.tabCheckInterval) {
            clearInterval(this.tabCheckInterval);
        }
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
        if (this.flushInterval) {
            clearInterval(this.flushInterval);
        }
        this.retryTimeouts.forEach(clearTimeout);
        this.retryTimeouts = [];

        this.eventListeners.forEach(({ target, eventName, handler }) => {
            target.removeEventListener(eventName, handler);
        });
        this.eventListeners = [];

        // Remove tab from localStorage
        try {
            const storageKey = `exam_session_${this.attemptId}`;
            const currentTabData = localStorage.getItem(storageKey);

            if (currentTabData) {
                const currentData = JSON.parse(currentTabData);
                if (currentData.tabId === this.tabId) {
                    localStorage.removeItem(storageKey);
                }
            }
        } catch (error) {
            console.warn('Failed to cleanup tab data:', error);
        }
    }

    // Public methods
    getCurrentTabCount() {
        return this.tabCount;
    }

    getSessionKey() {
        return this.sessionKey;
    }

    getTabId() {
        return this.tabId;
    }

    forceMultipleTabCheck() {
        this.checkForMultipleTabs();
    }

    debugLog(...args) {
        if (this.config.debug) {
            console.log(...args);
        }
    }

    // Static method for easy initialization
    static init(attemptId, config = {}) {
        // Only initialize if we're in an exam context
        if (!attemptId) {
            console.warn('SimpleTabLogger: No attempt ID provided');
            return null;
        }

        return new SimpleTabLogger(attemptId, config);
    }
}

// Auto-initialize if attemptId is available on window object
if (typeof window !== 'undefined' && window.attemptId) {
    window.tabLogger = SimpleTabLogger.init(window.attemptId);
}

// Export for use in other modules
export default SimpleTabLogger;
