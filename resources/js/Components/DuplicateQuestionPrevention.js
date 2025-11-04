/**
 * Duplicate Question Prevention
 * Frontend utility to prevent duplicate question submissions
 */

export class DuplicateQuestionPrevention {
    constructor() {
        this.isSubmitting = false;
        this.lastSubmittedContent = null;
        this.debounceTimeout = null;
    }

    /**
     * Check if question content is being submitted too quickly
     * @param {string} content - The question content
     * @returns {boolean} - True if submission should be prevented
     */
    isTooQuickSubmission(content) {
        // Check if we just submitted the same content
        if (this.lastSubmittedContent === content && this.isSubmitting) {
            return true;
        }
        return false;
    }

    /**
     * Set submitting state and track last submitted content
     * @param {string} content - The question content being submitted
     */
    setSubmitting(content) {
        this.isSubmitting = true;
        this.lastSubmittedContent = content;

        // Reset after 5 seconds
        setTimeout(() => {
            this.isSubmitting = false;
            this.lastSubmittedContent = null;
        }, 5000);
    }

    /**
     * Reset submitting state
     */
    resetSubmitting() {
        this.isSubmitting = false;
        this.lastSubmittedContent = null;
    }

    /**
     * Debounce function to prevent rapid submissions
     * @param {Function} callback - Function to debounce
     * @param {number} delay - Delay in milliseconds
     * @returns {Function} - Debounced function
     */
    debounce(callback, delay = 1000) {
        return (...args) => {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => callback(...args), delay);
        };
    }

    /**
     * Show duplicate warning dialog
     * @param {string} message - Warning message to show
     * @returns {Promise<boolean>} - User's decision
     */
    async showDuplicateWarning(message) {
        return new Promise((resolve) => {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-6 max-w-md mx-4">
                    <div class="flex items-center mb-4">
                        <svg class="w-6 h-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Duplicate Question Detected</h3>
                    </div>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <div class="flex justify-end space-x-3">
                        <button id="cancel-btn" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button id="force-submit-btn" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Force Submit Anyway
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            const cancelBtn = modal.querySelector('#cancel-btn');
            const forceSubmitBtn = modal.querySelector('#force-submit-btn');

            const cleanup = () => {
                document.body.removeChild(modal);
            };

            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });

            forceSubmitBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });

            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                    resolve(false);
                }
            });
        });
    }

    /**
     * Compare two strings for similarity
     * @param {string} str1 - First string
     * @param {string} str2 - Second string
     * @param {number} threshold - Similarity threshold (0-1)
     * @returns {boolean} - True if strings are similar
     */
    areSimilar(str1, str2, threshold = 0.85) {
        const clean1 = str1.toLowerCase().replace(/<[^>]*>/g, '').trim();
        const clean2 = str2.toLowerCase().replace(/<[^>]*>/g, '').trim();

        if (clean1 === clean2) return true;

        // Simple similarity check (can be enhanced with more sophisticated algorithms)
        const longer = clean1.length > clean2.length ? clean1 : clean2;
        const shorter = clean1.length > clean2.length ? clean2 : clean1;

        if (longer.length === 0) return true;

        const similarity = (longer.length - this.levenshteinDistance(longer, shorter)) / longer.length;
        return similarity >= threshold;
    }

    /**
     * Calculate Levenshtein distance between two strings
     * @param {string} str1 - First string
     * @param {string} str2 - Second string
     * @returns {number} - Levenshtein distance
     */
    levenshteinDistance(str1, str2) {
        const matrix = [];

        for (let i = 0; i <= str2.length; i++) {
            matrix[i] = [i];
        }

        for (let j = 0; j <= str1.length; j++) {
            matrix[0][j] = j;
        }

        for (let i = 1; i <= str2.length; i++) {
            for (let j = 1; j <= str1.length; j++) {
                if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }

        return matrix[str2.length][str1.length];
    }

    /**
     * Disable submit button and show loading state
     * @param {HTMLButtonElement} button - Submit button
     */
    disableSubmitButton(button) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
    }

    /**
     * Re-enable submit button
     * @param {HTMLButtonElement} button - Submit button
     */
    enableSubmitButton(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Submit';
    }
}

// Create singleton instance
export const duplicatePrevention = new DuplicateQuestionPrevention();