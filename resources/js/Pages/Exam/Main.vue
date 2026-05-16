<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {XIcon} from '@heroicons/vue/outline'
import {ref, toRefs, onMounted, computed, watch, onUnmounted} from "vue";
import Editor from "@/Components/Editor";
import Card from '@/Components/Card'
import LoadingCircle from '@/Components/LoadingCircle'
import moment from "moment-timezone";
import shuffleArray from "@/Libs/shuffleArray";
import route from "@/Libs/ziggy"
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import ExamNavigation from "@/Layouts/Partials/Exam/ExamNavigation.vue";
import {notification} from "@/Store/notification";
import {Inertia} from "@inertiajs/inertia";
import Modal from "@/Jetstream/Modal.vue";
import axios from "axios";
import SimpleTabLogger from "@/SimpleTabLogger.js";

const props = defineProps({
  taker: {
    type: Object,
  },
  delivery: {
    type: Object,
  },
  exam: {
    type: Object,
  },
  examItems: {
    type: Object
  },
  attempt: {
    type: Object,
  },
  attemptQuestions: {
    type: Object,
  },
  admin: {
    type: Object,
  },
  remainingSeconds: {
    type: Number,
    default: 0,
  },
  examId: {
    type: Number,
    default: null,
  },
  deliveryId: {
    type: Number,
    default: null,
  },
  takerId: {
    type: Number,
    default: null,
  },
  timer: {
    type: Object,
    default: () => ({}),
  },
})

const {exam, examItems, attempt, delivery, attemptQuestions, admin, remainingSeconds, examId, deliveryId, takerId, timer} = toRefs(props);

// 🔒 CRITICAL FIX: Use explicit IDs from backend instead of nested object properties
const examContext = ref({
  examId: examId.value || null,
  deliveryId: deliveryId.value || null,
  takerId: takerId.value || null,
  totalItems: examItems.value?.length || 0,
  initialized: false
});

// 🔒 DEBUG: Log exam context for troubleshooting with explicit IDs
console.log('🎯 EXAM CONTEXT INITIALIZATION:', {
  backendExamId: examId.value,
  backendDeliveryId: deliveryId.value,
  backendTakerId: takerId.value,
  fallbackExamId: exam.value?.id,
  examName: exam.value?.name,
  fallbackDeliveryId: delivery.value?.id,
  deliveryName: delivery.value?.name,
  totalItems: examItems.value?.length,
  examContext: examContext.value
});

const loadingQuestion = ref(false);
const loadingQuestions = ref(false); // NEW: Loading state for question navigation
const lastLoadedIndex = ref(null); // NEW: Track last loaded index to prevent duplicate requests
const vignetteData = ref(null);
const questionData = ref(null);

// NEW: Isolated localStorage keys for this exam session
const getLocalStorageKey = (key) => {
  // Enhanced safety check for examContext
  if (!examContext || !examContext.value) {
    return `exam_unknown_delivery_unknown_${key}`;
  }
  const examId = examContext.value.examId || 'unknown';
  const deliveryId = examContext.value.deliveryId || 'unknown';
  return `exam_${examId}_delivery_${deliveryId}_${key}`;
};

// NEW: Clear any conflicting exam state from localStorage
const clearConflictingExamState = () => {
  const keys = Object.keys(localStorage);
  keys.forEach(key => {
    if (key.startsWith('exam_') && !key.includes(`exam_${examContext.value.examId}_delivery_${examContext.value.deliveryId}_`)) {
      console.log('🗑️ Clearing conflicting exam state:', key);
      localStorage.removeItem(key);
    }
  });
};
// NEW: Exam Context Validation - Prevent mixed exam context
const validateExamContext = (itemHash, responseData) => {
  if (!examContext.value.initialized) {
    examContext.value.initialized = true;
    console.log('🎯 EXAM CONTEXT VALIDATED - First load:', {
      itemHash,
      examId: examContext.value.examId,
      deliveryId: examContext.value.deliveryId,
      questionsReceived: responseData?.questions?.length || 0
    });
    return true;
  }

  // Validate response consistency with current exam context
  const questionCount = responseData?.questions?.length || 0;
  const expectedRange = examContext.value.totalItems > 0; // We have 52 items, not 60

  if (!expectedRange || questionCount === 0) {
    console.warn('⚠️ EXAM CONTEXT WARNING:', {
      itemHash,
      questionCount,
      totalItems: examContext.value.totalItems,
      examId: examContext.value.examId,
      message: 'Invalid question count or missing exam context'
    });

    // Return false to prevent mixed context issues
    return false;
  }

  console.log('✅ EXAM CONTEXT VALIDATED:', {
    itemHash,
    examId: examContext.value.examId,
    deliveryId: examContext.value.deliveryId,
    questionCount,
    totalItems: examContext.value.totalItems
  });

  return true;
};

// 🔒 CRITICAL FIX: Enhanced computed items with comprehensive fallback handling
const items = computed(() => {
  try {
    console.log('🔧 Computing items with validation');

    // 🔒 Validate examItems.value
    if (!examItems.value) {
      console.warn('⚠️ examItems.value is null or undefined, returning empty array');
      return [];
    }

    if (!Array.isArray(examItems.value)) {
      console.warn('⚠️ examItems.value is not an array, returning empty array', {
        examItems: examItems.value,
        type: typeof examItems.value
      });
      return [];
    }

    if (examItems.value.length === 0) {
      console.warn('⚠️ examItems.value is empty, returning empty array');
      return [];
    }

    console.log('📊 Processing items:', examItems.value.length, 'items');

    const processedItems = examItems.value.map((item, index) => {
      try {
        // 🔒 Validate individual item
        if (!item || typeof item !== 'object') {
          console.warn('⚠️ Invalid item at index:', index, 'using fallback item', {
            item: item,
            type: typeof item
          });
          return createFallbackItem(index);
        }

        // 🔒 Validate required item properties
        if (!item.hash) {
          console.warn('⚠️ Item missing hash at index:', index, 'using fallback');
          return createFallbackItem(index, item);
        }

        // 🔒 Validate questions array
        const itemQuestions = item.questions || [];
        if (!Array.isArray(itemQuestions)) {
          console.warn('⚠️ Item has invalid questions at index:', index, 'using empty array');
          item.questions = [];
        }

        const questions = item.is_random ? shuffleArray(itemQuestions) : itemQuestions;

        const processedQuestions = questions.map((question, qIndex) => {
          try {
            // 🔒 Validate individual question
            if (!question || typeof question !== 'object') {
              console.warn('⚠️ Invalid question at item index:', index, 'question index:', qIndex);
              return createFallbackQuestion(qIndex);
            }

            // 🔒 Validate answers array
            const questionAnswers = question.answers || [];
            if (!Array.isArray(questionAnswers)) {
              console.warn('⚠️ Question has invalid answers at item index:', index, 'question index:', qIndex);
              question.answers = [];
            }

            return {
              ...question,
              // 🔒 Ensure critical properties exist
              hash: question.hash || `fallback_question_${index}_${qIndex}`,
              item_hash: question.item_hash || item.hash,
              answers: question.is_random ? shuffleArray(questionAnswers) : questionAnswers
            };
          } catch (questionError) {
            console.error('💥 Error processing question at item index:', index, 'question index:', qIndex, questionError);
            return createFallbackQuestion(qIndex);
          }
        });

        return {
          ...item,
          // 🔒 Ensure critical properties exist
          hash: item.hash,
          name: item.name || `Item ${index + 1}`,
          questions: processedQuestions,
          // 🔒 Ensure required properties for navigation
          item_type: item.item_type || { value: 'multiple-choice' },
          is_random: !!item.is_random,
          is_vignette: !!item.is_vignette,
          content: item.content || null,
          attachments: item.attachments || []
        };
      } catch (itemError) {
        console.error('💥 Error processing item at index:', index, itemError);
        return createFallbackItem(index, item);
      }
    });

    // 🔒 Validate final processed items
    if (!Array.isArray(processedItems) || processedItems.length === 0) {
      console.warn('⚠️ No valid items after processing, returning empty array');
      return [];
    }

    // 🔒 Validate exam.value for randomization
    const shouldRandomize = exam.value && exam.value.is_random;
    const finalItems = shouldRandomize ? shuffleArray(processedItems) : processedItems;

    console.log('✅ Successfully computed items:', finalItems.length, 'items');
    return finalItems;

  } catch (error) {
    console.error('💥 CRITICAL ERROR in items computed:', error);
    console.error('Error details:', {
      message: error.message,
      stack: error.stack,
      examItems: examItems.value,
      exam: exam.value
    });
    // Emergency fallback: return empty array to prevent complete crash
    return [];
  }
});

// 🔒 Helper functions for fallback item and question creation
const createFallbackItem = (index, originalItem = null) => {
  return {
    hash: originalItem?.hash || `fallback_item_${index}`,
    name: originalItem?.name || `Item ${index + 1}`,
    questions: originalItem?.questions || [],
    item_type: originalItem?.item_type || { value: 'multiple-choice' },
    is_random: !!originalItem?.is_random,
    is_vignette: !!originalItem?.is_vignette,
    content: originalItem?.content || null,
    attachments: originalItem?.attachments || []
  };
};

const createFallbackQuestion = (index) => {
  return {
    hash: `fallback_question_${index}`,
    item_hash: null,
    question: 'Error loading question',
    answers: [],
    is_random: false
  };
};

// 🔒 CRITICAL FIX: Implement robust reactive answerVal state
const rawAnswerVal = ref({});
const laters = ref([]);
const submittingAnswer = ref(false);
const timerCount = ref("00:00");
const activeIntervals = [];
const activeTimeouts = [];
let timerDeadlineMs = null;
let timerExpired = false;
let tabLoggerInstance = null;

const registerInterval = (callback, delay) => {
  const id = setInterval(callback, delay);
  activeIntervals.push(id);
  return id;
};

const registerTimeout = (callback, delay) => {
  const id = setTimeout(() => {
    clearRegisteredTimer(activeTimeouts, id);
    callback();
  }, delay);
  activeTimeouts.push(id);
  return id;
};

const clearRegisteredTimer = (collection, id) => {
  const index = collection.indexOf(id);
  if (index !== -1) {
    collection.splice(index, 1);
  }
};

const cleanupExamResources = () => {
  activeIntervals.splice(0).forEach(clearInterval);
  activeTimeouts.splice(0).forEach(clearTimeout);

  if (tabLoggerInstance && typeof tabLoggerInstance.cleanup === 'function') {
    const logger = tabLoggerInstance;
    if (window.tabLogger === logger) {
      window.tabLogger = null;
    }
    tabLoggerInstance.cleanup();
    tabLoggerInstance = null;
  }

  if (window.tabLogger && typeof window.tabLogger.cleanup === 'function') {
    window.tabLogger.cleanup();
    window.tabLogger = null;
  }
};

// 🔒 CRITICAL FIX: Computed property with guaranteed non-null value
const answerVal = computed({
  get: () => {
    if (!rawAnswerVal.value || typeof rawAnswerVal.value !== 'object') {
      console.warn('⚠️ answerVal.value was invalid, returning empty object', {
        actualValue: rawAnswerVal.value,
        type: typeof rawAnswerVal.value
      });
      return {};
    }
    return rawAnswerVal.value;
  },
  set: (newValue) => {
    if (!newValue || typeof newValue !== 'object') {
      console.error('❌ Cannot set answerVal to invalid value:', newValue);
      return;
    }
    rawAnswerVal.value = newValue;
  }
});

// 🔒 CRITICAL FIX: Safe access helper functions
const safeGetAnswerVal = () => {
  return rawAnswerVal.value || {};
};

const safeSetAnswer = (key, value) => {
  if (!rawAnswerVal.value) {
    rawAnswerVal.value = {};
  }
  if (key && typeof key === 'string') {
    rawAnswerVal.value[key] = value;
  } else {
    console.error('❌ Cannot set answer with invalid key:', key);
  }
};

// 🔒 DEBUG: Monitor rawAnswerVal changes instead
watch(rawAnswerVal, (newVal, oldVal) => {
  if (newVal === undefined || newVal === null) {
    console.error('🚨 CRITICAL: rawAnswerVal became undefined/null!', {
      newVal,
      oldVal,
      stackTrace: new Error().stack,
      timestamp: new Date().toISOString()
    });
    rawAnswerVal.value = {}; // Auto-recovery
  } else {
    console.log('🔄 rawAnswerVal changed:', {
      keys: Object.keys(newVal || {}),
      timestamp: new Date().toISOString()
    });
  }
}, { deep: true });

// 🔍 DEBUG: Watch for attempt availability to start timer sync
watch(attempt, (newAttempt, oldAttempt) => {
  if (newAttempt && newAttempt.hash && (!oldAttempt || !oldAttempt.hash)) {
    console.log('🎯 ATTEMPT DETECTED: Attempt became available - can start timer sync', {
      attemptId: newAttempt.id,
      attemptHash: newAttempt.hash,
      examId: newAttempt.exam_id,
      deliveryId: newAttempt.delivery_id
    });
  }
}, { immediate: true });

// 🕐 WAITING ROOM: Check if user should be redirected to waiting room
const shouldShowWaitingRoom = computed(() => {
  if (!delivery.value) return false;

  const isAutomaticStart = !!delivery.value.automatic_start;
  const hasScheduledTime = !!delivery.value.scheduled_at;

  if (!isAutomaticStart || !hasScheduledTime) {
    console.log('🕐 Waiting Room: Not required - automatic_start:', isAutomaticStart, 'scheduled_at:', hasScheduledTime);
    return false;
  }

  const scheduledTime = new Date(delivery.value.scheduled_at);
  const now = new Date();
  const isInFuture = scheduledTime > now;

  console.log('🕐 Waiting Room Check:', {
    automatic_start: isAutomaticStart,
    scheduled_at: delivery.value.scheduled_at,
    scheduledTime: scheduledTime.toISOString(),
    now: now.toISOString(),
    isInFuture: isInFuture,
    shouldWait: isAutomaticStart && isInFuture
  });

  return isAutomaticStart && isInFuture;
});

// 🕐 WAITING ROOM: Redirect to token login for proper session setup if needed
const checkAndRedirectToWaitingRoom = () => {
  if (shouldShowWaitingRoom.value) {
    console.log('🕐 Waiting Room: Conditions met, redirecting to token login for proper session setup');

    // Enhanced cleanup before redirect
    try {
      const localStorageKey = getLocalStorageKey('exam-state');
      if (localStorageKey && typeof localStorageKey === 'string') {
        localStorage.removeItem(localStorageKey);
      }

      const timerStateKey = getLocalStorageKey('timer-state');
      if (timerStateKey && typeof timerStateKey === 'string') {
        localStorage.removeItem(timerStateKey);
      }
      console.log('✅ Waiting Room: Cleaned up localStorage before redirect');
    } catch (storageError) {
      console.warn('⚠️ Waiting Room: Failed to remove localStorage keys', storageError);
    }

    // Redirect to waiting room so user can wait for proper exam start time
    try {
      console.log('🕐 Waiting Room: Redirecting to waiting room for proper exam access');
      window.location.href = '/exam/waiting-room';
    } catch (redirectError) {
      console.error('💥 Waiting Room: Redirect failed, using final fallback', redirectError);
      window.location.href = '/exam/waiting-room';
    }

    return true; // Indicate that redirect was initiated
  }

  console.log('🕐 Waiting Room: No redirect needed - proceeding with exam');
  return false;
};

const submitAnswer = async (partial = false, specificQuestionHash = null) => {
  if (submittingAnswer.value) return false;
  submittingAnswer.value = true;
  let succeeded = true;

  const currentAnswers = answerVal.value; // Safe computed property access

  if (Object.keys(currentAnswers).length >= 1) {
    // 🔒 CRITICAL FIX: For partial submissions with specific question, only submit that question's answer
    let newAnswers;
    if (partial && specificQuestionHash && currentAnswers[specificQuestionHash]) {
      newAnswers = {
        [specificQuestionHash]: currentAnswers[specificQuestionHash]
      };
    } else {
      newAnswers = {
        ...currentAnswers // Safe computed property access
      };
    }

    // check if essay null
    const questionType = questionData.value?.type?.value;
    if (questionType && questionType !== 'multiple-choice') {
      Object.keys(newAnswers).forEach(key => {
        if (String(newAnswers[key] || '').replace(/<\/?[^>]+(>|$)/g, "").trim() === '') {
          delete newAnswers[key];
        }
      })
    } else if (questionType === 'multiple-choice') {
      Object.keys(newAnswers).forEach(key => {
        if (newAnswers[key] === null) {
          delete newAnswers[key];
        }
      })
    }

    // 🔒 CRITICAL FIX: Only process specific question for partial submissions to preserve "mark as later" state
    const questionsToProcess = specificQuestionHash && partial ? [specificQuestionHash] : Object.keys(newAnswers);

    questionsToProcess.forEach(key => {
      // Only process if this question actually has an answer in newAnswers
      if (!newAnswers[key]) return;

      // Add to done quests if not already there
      addToStateArray(doneQuests.value, key)

      // Remove from skipped quests if answered
      removeFromStateArray(skippedQuests.value, key)

      // 🔒 CRITICAL FIX: Only remove from later quests if this is NOT a partial submission OR if this is the specific question being answered
      // This preserves "mark as later" state for other questions when answering a different question
      if (!partial || key === specificQuestionHash) {
        removeFromStateArray(laterQuests.value, key)
        // Also uncheck the checkbox
        if (laters.value[key]) {
          laters.value[key] = false;
        }
      }
    })

    // Update localStorage - CRITICAL: Save answerVal data too!
    localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify({
      skipped: skippedQuests.value,
      later: laterQuests.value,
      done: doneQuests.value,
      answerData: answerVal.value, // Save all answers!
    }))

    if (Object.keys(newAnswers).length >= 1) {
      if (attempt.value) {
        try {
          const {data: responseData} = await axios.post(route('exam.answer'), {
            attempt_hash: attempt.value.hash,
            answers_value: newAnswers
          })

          if (responseData) {
            // Check if the response indicates success
            if (responseData.success === true) {
              // 🔒 ENHANCED: More informative message with answer count
              const answerCount = Object.keys(newAnswers).length;
              const message = responseData.message || `Answer saved successfully (${answerCount} answer${answerCount > 1 ? 's' : ''} saved)`;

              notification.add('success', 'Success', message)
            } else if (responseData.error) {
              // Handle case where server returns error but still HTTP 200
              console.warn('Server returned error in success response:', responseData.error);
              notification.add('error', 'Error', responseData.error);
              succeeded = false;
            } else {
              const answerCount = Object.keys(newAnswers).length;
              notification.add('success', 'Success', `Answer saved (${answerCount} answer${answerCount > 1 ? 's' : ''})`)
            }
          }
          if (!partial) answerVal.value = {}
        } catch (error) {
          safeConsoleError('Error submitting answer:', error)

          // Enhanced error handling with detailed logging
          let errorMessage = 'Failed to save answer.';

          if (error.response) {
            // Server responded with error status
            const status = error.response.status;
            const data = error.response.data;

            safeConsoleError('Server error response:', {
              status,
              data,
              headers: error.response.headers
            });

            if (status === 403 && data?.expired) {
              errorMessage = 'Exam time expired. Please contact administrator.';
            } else if (status === 422) {
              errorMessage = 'Validation error. Please check your answer.';
            } else if (status === 500) {
              errorMessage = data?.error || 'Server error occurred. Please try again.';
            } else {
              errorMessage = data?.error || `Server error (${status}). Please try again.`;
            }
          } else if (error.request) {
            // Network error - request was made but no response received
            safeConsoleError('Network error:', error.request);
            errorMessage = 'Network error. Please check your connection and try again.';
          } else {
            // Other errors (config, etc.)
            safeConsoleError('Request setup error:', error.message);
            errorMessage = `Request error: ${error.message}`;
          }

          notification.add('error', 'Error', errorMessage);
          succeeded = false;

          // Don't clear answerVal on error so user can retry
          safeConsoleLog('Answer preserved due to error:', {
            answerCount: Object.keys(currentAnswers).length,
            answers: currentAnswers
          });
        }
      } else {
        notification.add('error', 'Error', 'Cannot save answer because the exam attempt is unavailable.');
        succeeded = false;
      }
    }
  }
  submittingAnswer.value = false;
  return succeeded;
}

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
const pageNumber = ref(1);
const skippedQuests = ref([]);
const laterQuests = ref([]);
const doneQuests = ref([]);

// 🔒 CRITICAL FIX: Enhanced getQuestions with comprehensive validation
const getQuestions = async (index) => {
  try {
    console.log('🚀 getQuestions called with index:', index);

    // 🔒 NEW: Validate index parameter
    if (typeof index !== 'number' || index < 0) {
      console.error('❌ getQuestions: Invalid index parameter', { index, type: typeof index });
      return null;
    }

    // 🔒 NEW: Validate items array
    if (!items.value || !Array.isArray(items.value)) {
      console.error('❌ getQuestions: items.value is not a valid array', {
        items: items.value,
        type: typeof items.value,
        isArray: Array.isArray(items.value)
      });
      return null;
    }

    // 🔒 NEW: Validate index bounds
    if (index >= items.value.length) {
      console.error('❌ getQuestions: Index out of bounds', {
        index,
        itemsLength: items.value.length,
        lastValidIndex: items.value.length - 1
      });
      return null;
    }

    const item = items.value[index];

    // 🔒 NEW: Validate item object
    if (!item || typeof item !== 'object') {
      console.error('❌ getQuestions: Invalid item at index', {
        index,
        item: item,
        type: typeof item
      });
      return null;
    }

    // 🔒 NEW: Validate required item properties
    if (!item.hash) {
      console.error('❌ getQuestions: Item missing required hash property', {
        index,
        item: item,
        hasHash: !!item.hash
      });
      return null;
    }

    // NEW: Prevent duplicate requests and track loading state
    if (loadingQuestions.value || lastLoadedIndex.value === index) {
      console.log('⏭️ Skipping duplicate request for index:', index, 'loading:', loadingQuestions.value);
      return null;
    }

  if (questionData.value !== null && questionData.value.item_hash === item.hash) return null;

  loadingQuestions.value = true;
  console.log('🔄 Loading questions for index:', index, 'item:', item.hash);

  // 🔒 NEW: Submit answer with error handling
  try {
    await submitAnswer(true) // Use partial=true to avoid resetting answerVal during navigation
  } catch (submitError) {
    console.error('❌ Error submitting answer during navigation:', submitError);
    // Continue with question loading even if submit fails
  }

    // 🔒 ENHANCED: Preserve existing questions during loading to prevent blank screen
    const previousQuestions = questionData.value?.questions || [];
    const previousQuestionData = {...questionData.value};

    // 🔒 CRITICAL: Save current answers before navigation
    const currentAnswers = answerVal.value;
    console.log('🔄 getQuestions: Preserving answers before navigation', {
      answerCount: Object.keys(currentAnswers).length,
      fromIndex: previousQuestionData?.index,
      toIndex: index
    });

    // 🔒 ENHANCED: Update metadata but preserve questions and loading state
    questionData.value = {
      ...previousQuestionData,
      index: index,
      item_hash: item.hash,
      type: item.item_type,
      is_random: item.is_random,
      questions: previousQuestions.length > 0 ? previousQuestions : item.questions, // Keep previous if available
      attachments: item.attachments,
      loading: true,
      // Preserve answer state reference
      answerState: currentAnswers
    }

    vignetteData.value = item.is_vignette ? item.content : null

    // Only add to skipped if not already done or skipped
    item.questions.forEach((question) => {
      if (!checkSkippedQuest(question.hash) && !checkDoneQuest(question.hash)) {
        addToStateArray(skippedQuests.value, question.hash)
      }
    })

    // Sync laters checkbox state with laterQuests
    item.questions.forEach((question) => {
      laters.value[question.hash] = laterQuests.value.indexOf(question.hash) !== -1;
    })

    loadingQuestion.value = true;
    console.log('📡 Fetching server data for item:', item.hash);

    const {data: responseData} = await axios.get(route('exam.get-taker-answer', { item_hash: item.hash }));
    console.log('✅ Server response received for item:', item.hash, responseData);

    // NEW: Validate exam context to prevent mixed exam issues
    if (!validateExamContext(item.hash, responseData)) {
      console.error('❌ EXAM CONTEXT VALIDATION FAILED - Rejecting mixed context response');
      loadingQuestion.value = false;
      loadingQuestions.value = false;
      return;
    }

    if (responseData && responseData.questions) {
      const questions = responseData.questions;
      const attempt = responseData.attempt;

      // 🔥 CRITICAL FIX: Handle attachments from API response
      if (responseData.attachments && Array.isArray(responseData.attachments)) {
        console.log('🔗 ATTACHMENTS: Using attachments from API response', responseData.attachments.length);
        item.attachments = responseData.attachments;
      } else {
        console.log('🔗 ATTACHMENTS: No attachments in API response, using item attachments', item.attachments?.length || 0);
      }

      // Load questions into the item
      item.questions = questions;

      // Sync laters checkbox state with laterQuests
      item.questions.forEach((question) => {
        laters.value[question.hash] = laterQuests.value.indexOf(question.hash) !== -1;
      })

      // Process attempt answers if they exist
      if (attempt && attempt.questions && attempt.questions.length > 0) {
        const attemptAnswer = attempt.questions;
        attemptAnswer.forEach((question) => {
          // Only process if question has pivot (was answered)
          if (question.pivot) {
            // 🔒 CRITICAL FIX: Use question.hash for storage to prevent vignette collision
            const hashForStorage = question.hash; // Always use question.hash for storage
            const hashForMatching = question.item_hash || question.hash // For state tracking
            const answerValue = (item.item_type.value === 'multiple-choice') ? question.pivot.answer_hash : question.pivot.answer

            // Check if answer actually exists (not null/empty)
            const hasAnswer = answerValue !== null && answerValue !== undefined && answerValue !== ''

            if (hasAnswer) {
              // 🔒 SAFE: Use computed property access
              const currentAnswers = answerVal.value;
              currentAnswers[hashForStorage] = answerValue;

              // Add to doneQuests if not already there (use hashForMatching for state tracking)
              if (!doneQuests.value.includes(hashForMatching)) {
                addToStateArray(doneQuests.value, hashForMatching)
              }

              // Remove from skipped if answered (use hashForMatching for state tracking)
              removeFromStateArray(skippedQuests.value, hashForMatching)

              // Remove from later if answered (use hashForMatching for state tracking)
              removeFromStateArray(laterQuests.value, hashForMatching)
              // Also uncheck the checkbox
              if (laters.value[hashForMatching]) {
                laters.value[hashForMatching] = false;
              }
            }
          }
        })
      }

      console.log('Done quests after processing:', doneQuests.value)
      // CRITICAL: Update localStorage AFTER processing server response
      // This ensures the server state is properly merged with local state
      localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify({
        skipped: skippedQuests.value,
        later: laterQuests.value,
        done: doneQuests.value,
      }))
    } else {
      loadingQuestion.value = false;
      pageNumber.value = Math.ceil((index + 1) / 20)

      // Also update localStorage when no server data
      localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify({
        skipped: skippedQuests.value,
        later: laterQuests.value,
        done: doneQuests.value,
      }))
    }

    // NEW: Final update with fresh data
    questionData.value = {
      index: index,
      item_hash: item.hash,
      type: item.item_type,
      is_random: item.is_random,
      questions: item.questions,
      attachments: item.attachments,
      loading: false // NEW: Remove loading flag
    };

    lastLoadedIndex.value = index;
    console.log('✅ Successfully loaded questions for index:', index);

  } catch (error) {
    console.error('💥 CRITICAL ERROR in getQuestions for index:', index, error);
    console.error('Error details:', {
      message: error.message,
      stack: error.stack,
      index: index,
      itemsLength: items.value?.length,
      itemType: typeof items.value,
      hasItems: !!items.value
    });

    // 🔒 ENHANCED: Comprehensive error recovery with answer state preservation
    try {
      // Attempt to restore loading states
      loadingQuestions.value = false;
      loadingQuestion.value = false;

      // Keep previous questions on error to prevent blank screen
      if (questionData.value) {
        questionData.value.loading = false;

        // 🔒 CRITICAL: Ensure answer state is preserved during error recovery
        if (!questionData.value.answerState && rawAnswerVal.value) {
          questionData.value.answerState = rawAnswerVal.value;
          console.log('🔄 getQuestions: Answer state preserved during error recovery');
        }
      }

      // Try to set a safe page number
      if (typeof index === 'number' && index >= 0) {
        pageNumber.value = Math.ceil((index + 1) / 20);
      }

      // 🔒 CRITICAL: Verify answer integrity after error
      const currentAnswers = answerVal.value;
      console.log('🔄 getQuestions: Answer state verification after error recovery', {
        answerCount: Object.keys(currentAnswers).length,
        hasRawAnswerVal: !!rawAnswerVal.value,
        rawAnswerCount: Object.keys(rawAnswerVal.value || {}).length
      });

    } catch (recoveryError) {
      console.error('💥 Recovery also failed:', recoveryError);
      // Last resort: force loading states to false
      loadingQuestions.value = false;
      loadingQuestion.value = false;
    }

    return null;

  } finally {
    // Always ensure loading states are reset
    loadingQuestions.value = false;
    loadingQuestion.value = false;
    console.log('🏁 getQuestions completed for index:', index);
  }
};
const checkSkippedQuest = (hash) => skippedQuests.value.indexOf(hash) !== -1;
const checkDoneQuest = (hash) => doneQuests.value.indexOf(hash) !== -1;

// 🔒 CRITICAL FIX: Smart answer lookup with computed property safety
const getAnswerForQuestion = (question) => {
  // Guard against undefined question
  if (!question || typeof question !== 'object') {
    return null;
  }

  // 🔒 SAFE: Computed property guarantees non-null object
  const currentAnswers = answerVal.value;

  // 🔒 CRITICAL FIX: Use consistent key strategy matching selectAnswer
  // Primary: question.hash (main storage key only - dual storage removed to prevent collision)
  const possibleKeys = [
    question.hash,        // Primary key - this is what selectAnswer uses as main storage
  ].filter(Boolean);

  // 🔒 BACKWARD COMPATIBILITY: Check for existing answers stored with item_hash
  // Only for reading, not for new storage to prevent collision
  if (question.item_hash && question.item_hash !== question.hash) {
    possibleKeys.push(question.item_hash);
  }

  // Try all possible keys to find the stored answer
  for (const key of possibleKeys) {
    if (currentAnswers[key]) {
      return currentAnswers[key];
    }
  }

  return null;
};

// CRITICAL FIX: Smart answer selection with hash matching
const isAnswerSelected = (answerHash, question) => {
  const storedAnswer = getAnswerForQuestion(question);
  return storedAnswer === answerHash;
};

// Helper function to add item to array without duplicates
const addToStateArray = (array, item) => {
  if (!array.includes(item)) {
    array.push(item);
  }
};

// Helper function to remove item from array safely
const removeFromStateArray = (array, item) => {
  const index = array.indexOf(item);
  if (index !== -1) {
    array.splice(index, 1);
  }
};
// 🔒 CRITICAL FIX: Enhanced selectAnswer with comprehensive validation
const selectAnswer = function (answerHash, questionHash) {
  try {
    safeConsoleLog('🎯 selectAnswer called:', { answerHash, questionHash });

    // 🔒 ENHANCED: Use validation functions
    if (!validateAnswerStorage(questionHash, answerHash)) {
      safeConsoleError('❌ selectAnswer: Validation failed', { answerHash, questionHash });
      return;
    }

    // 🔒 SAFE: Computed property guarantees non-null object
    safeConsoleLog('🔒 selectAnswer: Using computed property, current answers:', {
      keys: Object.keys(answerVal.value),
      questionHash
    });

    // 🔒 CRITICAL FIX: Guard against undefined questionData.value
    if (!questionData.value || !questionData.value.questions) {
      safeConsoleError('❌ selectAnswer: questionData.value is undefined or has no questions', {
        questionData: questionData.value,
        hasQuestions: questionData.value?.questions
      });
      // Fallback: store answer directly using questionHash using safe function
      safeSetAnswer(questionHash, answerHash);
      submitAnswer(true, questionHash); // Pass specific question hash
      return;
    }

    const questions = questionData.value.questions;
    // Find the current question being answered to get its item_hash
    const currentQuestion = questions.find(q => q.hash === questionHash);

    if (!currentQuestion) {
      console.warn('⚠️ selectAnswer: Question not found, using fallback storage', {
        questionHash,
        availableQuestionHashes: questions.slice(0, 3).map(q => q.hash)
      });
      // Fallback: store answer directly using questionHash using safe function
      safeSetAnswer(questionHash, answerHash);
      submitAnswer(true, questionHash); // Pass specific question hash
      return;
    }

    // 🔒 CRITICAL FIX: Use consistent key strategy with getAnswerForQuestion
    // Always prioritize question.hash for storage to ensure consistency
    const hashForStorage = questionHash;

    // 🔒 SAFE: Use safe storage function
    safeSetAnswer(hashForStorage, answerHash);

    // 🔒 CRITICAL FIX: Remove dual storage to prevent vignette answer collision
    // Only store with question.hash to ensure each question has unique answer storage
    submitAnswer(true, questionHash); // Pass specific question hash

  } catch (error) {
    safeConsoleError('💥 selectAnswer: Critical error occurred', {
      error: error.message,
      stack: error.stack,
      answerHash,
      questionHash,
      questionData: questionData.value
    });

    // Emergency fallback: try to store answer anyway
    try {
      // 🔒 CRITICAL FIX: Use safe storage function even in fallback
      if (questionHash && typeof questionHash === 'string' && answerHash && typeof answerHash === 'string') {
        // Primary storage with question.hash using safe function
        safeSetAnswer(questionHash, answerHash);

        safeConsoleLog('🆘 Fallback storage: Stored with primary key', {
          primaryKey: questionHash,
          answerHash,
          strategy: 'Emergency fallback - consistent with selectAnswer'
        });

        // Try to submit answer with additional safety
        try {
          if (typeof submitAnswer === 'function') {
            submitAnswer(true, questionHash);
          } else {
            safeConsoleWarn('⚠️ submitAnswer function not available');
          }
        } catch (submitError) {
          safeConsoleError('💥 submitAnswer failed in fallback:', submitError);
        }
      } else {
        safeConsoleWarn('⚠️ Invalid questionHash or answerHash in fallback:', { questionHash, answerHash });
      }
    } catch (fallbackError) {
      safeConsoleError('💥 selectAnswer: Emergency fallback also failed', fallbackError);
    }
  }
};

const clearTimerStorage = () => {
  try {
    const localStorageKey = getLocalStorageKey('exam-state');
    if (localStorageKey && typeof localStorageKey === 'string') {
      localStorage.removeItem(localStorageKey);
    }

    const timerStateKey = getLocalStorageKey('timer-state');
    if (timerStateKey && typeof timerStateKey === 'string') {
      localStorage.removeItem(timerStateKey);
    }
  } catch (storageError) {
    console.warn('Timer: Failed to remove localStorage keys', storageError);
  }
};

const redirectToFinished = () => {
  cleanupExamResources();
  clearTimerStorage();

  const visitFinished = () => {
    const finishedRoute = typeof route === 'function' ? route('exam.finished') : '/exam/finished';
    if (typeof Inertia !== 'undefined' && Inertia.visit && typeof finishedRoute === 'string') {
      Inertia.visit(finishedRoute);
    } else {
      window.location.href = finishedRoute || '/exam/finished';
    }
  };

  try {
    axios.post('/exam/finish')
      .then((response) => {
        if (response.data?.redirect) {
          Inertia.visit(response.data.redirect);
          return;
        }
        visitFinished();
      })
      .catch(() => visitFinished());
  } catch (redirectError) {
    console.error('Timer: Redirect failed', redirectError);
    visitFinished();
  }
};

const updateTimerDisplay = () => {
  const remaining = timerDeadlineMs
    ? Math.max(0, Math.ceil((timerDeadlineMs - Date.now()) / 1000))
    : 0;

  timerCount.value = formatTime(remaining);

  if (remaining <= 0 && !timerExpired) {
    timerExpired = true;
    redirectToFinished();
  }
};

const setTimerDeadline = ({ remaining_seconds, expires_at }) => {
  const remaining = typeof remaining_seconds === 'number' ? Math.max(0, remaining_seconds) : 0;
  const expiresAtMs = expires_at ? Date.parse(expires_at) : NaN;
  timerDeadlineMs = Number.isFinite(expiresAtMs) ? expiresAtMs : Date.now() + (remaining * 1000);

  try {
    localStorage.setItem(getLocalStorageKey('timer-state'), JSON.stringify({
      expiresAt: new Date(timerDeadlineMs).toISOString(),
      remainingSeconds: remaining,
      syncedAt: Date.now()
    }));
  } catch (e) {
    console.warn('Timer: Failed to save timer state', e);
  }

  updateTimerDisplay();
};

const startTimer = (duration, expiresAt = null) => {
  if (admin?.value) {
    console.log('Timer: Admin mode detected, timer disabled');
    return null;
  }

  setTimerDeadline({
    remaining_seconds: typeof duration === 'number' ? duration : 0,
    expires_at: expiresAt
  });

  return registerInterval(updateTimerDisplay, 1000);
};

const updateTimerFromBackend = async (retryCount = 0, maxRetries = 1) => {
  try {
    console.log('🕐 Timer: Getting time from backend', { attempt: retryCount + 1, maxRetries });

    if (!attempt.value?.hash) {
      console.warn('⚠️ Timer: No attempt hash available, skipping update');
      return false;
    }

    const pingStart = Date.now();

    // 🔒 FIX: Use correct GET endpoint instead of non-existent POST endpoint
    const response = await axios.get(route('exam.timer.sync'), {
      params: {
        attempt_hash: attempt.value.hash
      }
    });

    const pingEnd = Date.now();
    const latency = (pingEnd - pingStart) / 2;

    if (response.data && typeof response.data.remaining_seconds === 'number') {
      setTimerDeadline(response.data);

      console.log('🕐 Timer: Updated from backend', {
        backendTime: Math.max(0, response.data.remaining_seconds),
        expiresAt: response.data.expires_at,
        formattedTime: timerCount.value,
        latency: latency.toFixed(1),
        expired: response.data.expired
      });

      // Check if exam has expired according to server
      if (response.data.expired) {
        console.log('🕐 Timer: Server confirms exam expired, redirecting');
        redirectToFinished();
        return true;
      }

      return true;
    } else {
      console.warn('⚠️ Timer: Invalid backend response', response.data);
      return false;
    }
  } catch (error) {
    console.error('❌ Timer: Backend update failed', {
      error: error.message,
      status: error.response?.status
    });

    if (retryCount < maxRetries) {
      const backoffMs = 1000 * (retryCount + 1);
      console.log(`🕐 Timer: Retrying in ${backoffMs}ms...`);
      await new Promise(resolve => registerTimeout(resolve, backoffMs));
      return updateTimerFromBackend(retryCount + 1, maxRetries);
    }

    return false;
  }
};

// Helper function to format time as MM:SS
const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
};

// 🔒 DEPRECATED: Legacy sync function - replaced by updateTimerFromBackend for simpler approach
const syncTimerWithServer = async (retryCount = 0, maxRetries = 3) => {
  console.warn('🕐 Timer: Using deprecated syncTimerWithServer - consider migrating to updateTimerFromBackend');

  try {
    console.log('🕐 Timer: Starting sync attempt', { attempt: retryCount + 1, maxRetries });

    // Validate required data
    if (!attempt.value?.hash) {
      console.error('❌ Timer: No attempt hash available for sync');
      return false;
    }

    const pingStart = Date.now();
    const response = await axios.get(route('exam.timer.sync'), {
      params: {
        attempt_hash: attempt.value.hash
      },
      timeout: 10000 // 10 second timeout
    });

    const latency = (Date.now() - pingStart) / 2; // One-way latency
    const adjustedTime = response.data.remaining_seconds - Math.round(latency/1000);

    console.log('🕐 Timer: Sync successful', {
      serverTime: response.data.remaining_seconds,
      adjustedTime,
      latency,
      response: response.data
    });

    if (response.data.expired) {
      console.log('🕐 Timer: Server confirms exam expired, redirecting');
      if (typeof Inertia !== 'undefined' && Inertia.visit) {
        Inertia.visit(route('exam.finished'));
      } else {
        window.location.href = route('exam.finished');
      }
      return true;
    }

    // Adjust local timer if significant drift (> 30 seconds for stable UX)
    const currentTimerSeconds = parseTimerCount(timerCount.value);
    const drift = Math.abs(currentTimerSeconds - adjustedTime);

    // 🔧 FIX: Disable timer restarts entirely - let backend timer updates handle synchronization
    // The periodic updateTimerFromBackend() calls will keep the timer in sync without jumps
    if (false && drift > 300) { // Disabled - never trigger timer restarts
      console.log('🕐 Timer: Significant drift detected, adjusting', {
        local: currentTimerSeconds,
        server: adjustedTime,
        drift: drift
      });
      // Restart timer with server time only for major discrepancies
      return startTimer(adjustedTime);
    }

    console.log('🕐 Timer: No adjustment needed');
    return false;

  } catch (error) {
    console.error('🕐 Timer: Sync failed', {
      error: error.message,
      status: error.response?.status,
      statusText: error.response?.statusText,
      attempt: retryCount + 1,
      maxRetries
    });

    // Implement retry logic with exponential backoff
    if (retryCount < maxRetries) {
      const backoffMs = Math.min(1000 * Math.pow(2, retryCount), 5000); // Max 5 seconds
      console.log(`🕐 Timer: Retrying in ${backoffMs}ms...`);

      await new Promise(resolve => registerTimeout(resolve, backoffMs));
      return syncTimerWithServer(retryCount + 1, maxRetries);
    } else {
      console.error('🕐 Timer: Max retries reached, sync failed permanently');

      // Fallback: continue with local timer but log warning
      if (error.response?.status === 404) {
        console.warn('🕐 Timer: Route not found (404), using local timer only');
      } else if (error.response?.status >= 500) {
        console.warn('🕐 Timer: Server error, continuing with local timer');
      }

      return false;
    }
  }
};

// 🔒 ENHANCED: Robust timer count parser with validation
const parseTimerCount = (timerString) => {
  if (!timerString || typeof timerString !== 'string') {
    console.warn('⚠️ Timer: Invalid timer string format:', timerString);
    return 0;
  }

  try {
    const parts = timerString.split(':');
    if (parts.length !== 2) {
      console.warn('⚠️ Timer: Unexpected timer format:', timerString);
      return 0;
    }

    const hours = parseInt(parts[0]) || 0;
    const minutes = parseInt(parts[1]) || 0;

    if (isNaN(hours) || isNaN(minutes)) {
      console.warn('⚠️ Timer: NaN values in timer string:', timerString);
      return 0;
    }

    const totalSeconds = hours * 60 + minutes;

    if (totalSeconds < 0) {
      console.warn('⚠️ Timer: Negative time calculated:', totalSeconds);
      return 0;
    }

    return totalSeconds;
  } catch (error) {
    console.error('💥 Timer: Error parsing timer string:', timerString, error);
    return 0;
  }
};

onMounted(() => {
  // 🕐 WAITING ROOM: Check if user should be redirected to waiting room FIRST
  console.log('🕐 WAITING ROOM: Checking if redirect is needed...');
  if (checkAndRedirectToWaitingRoom()) {
    console.log('🕐 WAITING ROOM: Redirect initiated, stopping exam initialization');
    return; // Stop exam initialization if redirecting to waiting room
  }

  // NEW: Initialize exam context isolation
  console.log('🔒 INITIALIZING EXAM CONTEXT ISOLATION');
  console.log('📊 Exam Context:', {
    examId: examContext.value.examId,
    examName: exam.value?.name,
    deliveryId: examContext.value.deliveryId,
    deliveryName: delivery.value?.name,
    totalItems: examContext.value.totalItems
  });

  // 🔒 NEW: Initialize Simple Tab Logger for multiple screen detection
  console.log('🖥️ INITIALIZING TAB LOGGER');
  try {
    // Initialize tab logger only if attempt exists
    if (attempt.value && attempt.value.id) {
      // Make attempt ID available globally for the logger
      window.attemptId = attempt.value.id;

      // Initialize the tab logger
      const tabLogger = SimpleTabLogger.init(attempt.value.id, {
        checkInterval: 10000,
        heartbeatInterval: 120000,
        logEndpoint: '/api/exam/log-multiple-tabs'
      });

      if (tabLogger) {
        tabLoggerInstance = tabLogger;
        console.log('✅ Tab Logger: Successfully initialized');
        console.log('📋 Tab Logger Info:', {
          attemptId: attempt.value.id,
          sessionKey: tabLogger.getSessionKey(),
          tabId: tabLogger.getTabId()
        });

        // Store reference globally for debugging
        window.tabLogger = tabLogger;
      } else {
        console.warn('⚠️ Tab Logger: Failed to initialize');
      }
    } else {
      console.log('ℹ️ Tab Logger: No attempt found, skipping initialization');
    }
  } catch (error) {
    console.error('❌ Tab Logger: Initialization failed', error);
  }

  // Clear any conflicting exam state from other exam sessions
  clearConflictingExamState();

  // DEBUG: Log initial data to understand count discrepancy
  console.log('=== DEBUG EXAM DATA ===')
  console.log('examItems.value count:', examItems.value?.length || 'undefined')
  console.log('examItems.value:', examItems.value)
  console.log('items computed count:', items.value?.length || 'undefined')
  console.log('items computed:', items.value)
  console.log('=======================')

  // Use server-provided time as the source of truth, then tick locally.
  try {
    console.log('🕐 Timer: Initializing with remaining seconds:', remainingSeconds.value);

    let validRemainingSeconds = typeof remainingSeconds?.value === 'number' && remainingSeconds.value > 0
      ? remainingSeconds.value
      : 0;

    if (validRemainingSeconds > 0) {
      const timerInterval = startTimer(validRemainingSeconds, timer.value?.expires_at || null);
      if (timerInterval) {
        console.log('✅ Timer: Successfully initialized with', validRemainingSeconds, 'seconds');

        const startBackendSync = () => {
          if (!attempt.value?.hash) {
            console.log('🕐 Timer: No attempt available yet, will retry sync initialization');
            return false;
          }

          console.log('🕐 Timer: Starting backend synchronization with attempt:', attempt.value.hash);

          registerTimeout(async () => {
            try {
              await updateTimerFromBackend();
            } catch (error) {
              console.error('🕐 Timer: Initial sync error', error);
            }
          }, 2000);

          // Sparse backend sync: local countdown handles per-second UI updates.
          registerInterval(async () => {
            try {
              await updateTimerFromBackend();
            } catch (error) {
              console.error('🕐 Timer: Backend sync error', error);
            }
          }, 120000);

          return true;
        };

        if (!startBackendSync()) {
          const watchForAttempt = registerInterval(() => {
            if (startBackendSync()) {
              clearInterval(watchForAttempt);
              clearRegisteredTimer(activeIntervals, watchForAttempt);
              console.log('🕐 Timer: Backend sync successfully started after attempt became available');
            }
          }, 2000);

          registerTimeout(() => {
            clearInterval(watchForAttempt);
            clearRegisteredTimer(activeIntervals, watchForAttempt);
            console.log('🕐 Timer: Stopped watching for attempt (timeout)');
          }, 120000);
        }
      } else {
        console.warn('⚠️ Timer: Failed to initialize interval');
      }
    } else {
      console.log('⏰ Timer: No time remaining or invalid value, redirecting to finished');
      redirectToFinished();
      return;
    }
  } catch (timerInitError) {
    console.error('💥 Timer: Critical error during timer initialization', timerInitError);

    // Emergency fallback redirect
    try {
      window.location.href = '/exam/finished';
    } catch (emergencyError) {
      console.error('💥 Timer: Emergency redirect failed', emergencyError);
      window.location.reload();
    }
    return;
  }

  // CRITICAL: First load localStorage to preserve any existing state
  let examState = null;
  try {
    examState = JSON.parse(localStorage.getItem(getLocalStorageKey('exam-state')));
  } catch (e) {
    console.warn('onMounted: Failed to parse localStorage state', e);
  }
  if (examState) {
    // Load all state from localStorage first
    skippedQuests.value = examState.skipped ?? []
    laterQuests.value = examState.later ?? []
    doneQuests.value = examState.done ?? []
  }

  // CRITICAL FIX: Load answer data from localStorage first
  let savedExamState = {};
  try {
    savedExamState = JSON.parse(localStorage.getItem(getLocalStorageKey('exam-state')) || '{}');
  } catch (e) {
    console.warn('onMounted: Failed to parse saved exam state', e);
  }
  // 🔒 CRITICAL FIX: Ensure rawAnswerVal is properly initialized for computed property
  if (!rawAnswerVal.value || typeof rawAnswerVal.value !== 'object') {
    rawAnswerVal.value = {};
  }

  // Populate rawAnswerVal from localStorage if available
  if (savedExamState.answerData && typeof savedExamState.answerData === 'object') {
    Object.keys(savedExamState.answerData).forEach(key => {
      if (savedExamState.answerData[key]) {
        rawAnswerVal.value[key] = savedExamState.answerData[key]
      }
    })
  }

  // Then merge with server data (attemptQuestions)
  // This ensures server data supplements, not replaces, local data

  items.value.forEach((item) => {
    item.questions.forEach((question) => {
      const attemptQuestion = attemptQuestions.value && attemptQuestions.value.find((data) => data.question.hash === question.hash)
      if (attemptQuestion) {
        // 🔒 CRITICAL FIX: Use consistent strategy matching selectAnswer
        // Always store with question.hash as primary key for consistency
        if (attemptQuestion.pivot && attemptQuestion.pivot.answer_hash) {
          // Primary storage with question.hash using safe function
          safeSetAnswer(question.hash, attemptQuestion.pivot.answer_hash);

          // 🔒 CRITICAL FIX: Remove dual storage to prevent vignette answer collision
          // Only store with question.hash to ensure each question has unique answer storage
        } else {
          console.log('❌ No pivot data or answer_hash in attemptQuestion')
        }

        // Only add to doneQuests if not already there (preserve local state)
        const hashForMatching = question.item_hash || question.hash
        if (!doneQuests.value.includes(hashForMatching)) {
          doneQuests.value.push(hashForMatching)
        }
      }
    })
  })

  // Update localStorage with merged state - include answerVal data!
  const stateToSave = {
    skipped: skippedQuests.value,
    later: laterQuests.value,
    done: doneQuests.value,
    answerData: answerVal.value, // Save current answer state
  };

  console.log('💾 Saving state to localStorage:', {
    answerDataKeys: Object.keys(stateToSave.answerData || {}),
    answerDataLength: Object.keys(stateToSave.answerData || {}).length,
    stateSize: JSON.stringify(stateToSave).length
  });

  localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify(stateToSave));

  getQuestions(0)
})

onUnmounted(() => {
  cleanupExamResources();
});

const modalFinish = ref(false)

// 🔒 ENHANCED: Robust navigation with state validation and persistence
const navigationClicked = async (hash, index) => {
  try {
    console.log('🧭 Navigation: Starting navigation', { hash, index });

    // Validate inputs
    if (typeof index !== 'number' || index < 0) {
      console.error('❌ Navigation: Invalid index', { index, hash });
      return false;
    }

    // Validate items data
    if (!items.value || !Array.isArray(items.value) || index >= items.value.length) {
      console.error('❌ Navigation: Invalid items data', {
        items: items.value,
        index,
        itemsLength: items.value?.length
      });
      return false;
    }

    // Save current answer state before navigation
    const currentAnswers = answerVal.value;
    console.log('🧭 Navigation: Saving state before navigation', {
      currentAnswerCount: Object.keys(currentAnswers).length,
      currentIndex: questionData.value?.index,
      targetIndex: index
    });

    // Persist current state to localStorage before navigation
    try {
      const stateToSave = {
        skipped: skippedQuests.value,
        later: laterQuests.value,
        done: doneQuests.value,
        answerData: currentAnswers,
        navigationTimestamp: Date.now(),
        navigationIndex: index
      };

      localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify(stateToSave));
      console.log('🧭 Navigation: State saved before navigation');
    } catch (storageError) {
      console.warn('⚠️ Navigation: Failed to save state', storageError);
    }

    // Perform navigation
    await getQuestions(index);
    return true;

  } catch (error) {
    console.error('💥 Navigation: Critical error', {
      error: error.message,
      hash,
      index,
      stack: error.stack
    });

    // Fallback: Try to reload current question
    try {
      if (questionData.value?.index !== undefined) {
        console.log('🧭 Navigation: Attempting to reload current question');
        await getQuestions(questionData.value.index);
      }
    } catch (fallbackError) {
      console.error('💥 Navigation: Fallback also failed', fallbackError);
    }

    return false;
  }
};

// 🔒 CRITICAL: Hash consistency validation for answer storage
const validateHashConsistency = (question) => {
  if (!question || typeof question !== 'object') {
    return false;
  }

  const hasQuestionHash = !!question.hash && typeof question.hash === 'string';
  return hasQuestionHash; // At minimum, we need question.hash
};

// 🔒 CRITICAL: Answer storage validation
const validateAnswerStorage = (questionHash, answerHash) => {
  if (!questionHash || typeof questionHash !== 'string') {
    safeConsoleError('Invalid question hash');
    return false;
  }

  if (!answerHash || typeof answerHash !== 'string') {
    safeConsoleError('Invalid answer hash');
    return false;
  }

  return true;
};

// 🔒 SAFE: Console logging with fallback for environments where console is undefined
const safeConsoleLog = (...args) => {
  try {
    if (typeof console !== 'undefined' && console.log) {
      console.log(...args);
    }
  } catch (error) {
    // Silently fail if console is not available
  }
};

// 🔒 SAFE: Console error with fallback for environments where console is undefined
const safeConsoleError = (...args) => {
  try {
    if (typeof console !== 'undefined' && console.error) {
      console.error(...args);
    }
  } catch (error) {
    // Silently fail if console is not available
  }
};

// 🔒 SAFE: Console warning with fallback for environments where console is undefined
const safeConsoleWarn = (...args) => {
  try {
    if (typeof console !== 'undefined' && console.warn) {
      console.warn(...args);
    }
  } catch (error) {
    // Silently fail if console is not available
  }
};

const finishCheckbox = ref(false)

const openModalFinish = () => {
  finishCheckbox.value = false;
  modalFinish.value = true
}
const finishExam = async () => {
  const saved = await submitAnswer()
  if (!saved) {
    notification.add('error', 'Error', 'Unable to submit your final answer. Please retry before finishing the exam.')
    return;
  }

  try {
    const response = await axios.post('/exam/finish');
    cleanupExamResources();
    Inertia.visit(response.data?.redirect || route('exam.finished'))
  } catch (error) {
    notification.add('error', 'Error', error.response?.data?.error || 'Unable to finish exam. Please retry.')
  }
}

const modalImage = ref(false);
const modalImageContent = ref(null);
const showScenarioModal = ref(false);

// Simplified image handling - just basic display

const zoomImage = (url, alt) => {
  modalImage.value = true;
  modalImageContent.value = {url, alt};
}

const markAsLater = (e, hash) => {
  if (e.target.checked) {
    addToStateArray(laterQuests.value, hash)
  } else {
    removeFromStateArray(laterQuests.value, hash)
  }

  // Update checkbox state
  laters.value[hash] = e.target.checked;

  // Update localStorage
  localStorage.setItem(getLocalStorageKey('exam-state'), JSON.stringify({
    skipped: skippedQuests.value,
    later: laterQuests.value,
    done: doneQuests.value,
  }))
}
</script>

<template>
  <ExamLayout :title="delivery.name" :taker="taker" :timer="timerCount">
    <!-- NEW: Loading Overlay - Non-intrusive -->
    <div v-if="loadingQuestions" class="loading-overlay">
      <div class="flex items-center gap-3">
        <LoadingCircle />
        <span class="text-sm font-medium">Loading next question...</span>
      </div>
    </div>

    <div class="flex gap-10 pb-32" :class="{ 'question-loading': loadingQuestions }">
      <div class="min-h-full flex-1" v-if="questionData !== null">
        <div class="flex justify-between items-center">
          <div class="relative">
            <div class="text-xl font-bold mb-2">Question #{{ questionData.index + 1 }}</div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-800 bg-gray-200" v-if="questionData.type">{{ questionData.type.name }}</span>
          </div>
          <div>
            <div class="flex gap-5">
              <button v-if="items[items.length - 1].hash === questionData.item_hash" @click="openModalFinish" class="flex justify-center items-center text-white bg-orange-600 rounded-md flex justify-center items-center py-2 px-3 hover:bg-orange-700">
                Submit Exam
              </button>
            </div>
          </div>
        </div>

        <div class="flex flex-col items-center gap-3 my-5">
          <div v-for="file in questionData.attachments" v-if="questionData.attachments.length >= 1" class="w-full flex gap-2 max-w-xl">
            <div class="flex-auto flex justify-center items-center">
              <img
                :src="file.url"
                :alt="file.description"
                class="rounded-lg cursor-pointer hover:shadow-md max-w-full h-auto"
                @click="() => zoomImage(file.url)"
              />
            </div>
          </div>
        </div>

        <div v-if="vignetteData != null" class="my-5 px-6">
          <p class="font-bold">Scenario</p>
          <div v-html="vignetteData"></div>
        </div>

        <div v-for="(question, questionIndex) in questionData.questions" class="mt-5 mb-16">
          <div class="pt-5">
            <div class="flex gap-2 items-center mb-2">
              <div class="font-bold text-lg">
                Question {{ questionData.index + 1 }}.{{ questionIndex + 1 }}
              </div>
              <button 
                v-if="vignetteData != null" 
                @click="showScenarioModal = true"
                class="ml-2 px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
              >
                📋 View Scenario
              </button>
              <div class="flex-1"></div>
              <div class="sm:col-span-2 flex items-center gap-2">
                <input :id="'mark-as-later-checkbox'+question.hash" type="checkbox" class="form-checkbox rounded text-red-500 focus:ring-red-500" v-model="laters[question.hash]" @change="markAsLater($event, question.hash)">
                <label class="flex flex-col justify-start" :for="'mark-as-later-checkbox'+question.hash">
                  <span>Mark As Later</span>
                </label>
              </div>
            </div>
            <Card>
              <div class="whitespace-pre-wrap mb-4" v-html="question.question"></div>
              <div v-if="!loadingQuestion">
                <div class="flex flex-col gap-2 mt-6 w-auto" v-if="question.type !== null && question.type?.name === 'multiple-choice'">
                  <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" @click="selectAnswer(answer.hash, question.hash)" :class="['flex text-left px-3 py-2 bg-gray-100 rounded-md', isAnswerSelected(answer.hash, question) ? 'bg-green-600 text-white' : 'hover:bg-green-200 hover:text-green-600']">
                    <span class="mr-3 font-bold uppercase">{{ answerIndex[ansIndex] }}</span> <span v-html="answer.answer"></span>
                  </button>
                </div>
                <div class="mt-4" v-else>
                  <Editor class="my-2" v-model="answerVal[question.hash]" @blur="() => submitAnswer(true, question.hash)" />
                </div>

                <div class="text-xs text-gray-500" v-if="question.type?.name === 'multiple-choice' && question.answers.length <= 0">
                  It seems the question is Multiple Choices but has empty answers. Please contact the administrator if this problem occurred frequently.
                </div>
              </div>
              <loading-circle v-else class="mx-auto mt-4"/>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </ExamLayout>

  <JetConfirmationModal :show="modalFinish" items="center">
    <template #title>
      Submit exam now?
    </template>

    <template #content>
      You'll not be able to change any answers after submit the exam.
      <div class="mt-0.5 sm:col-span-2 flex items-center gap-2 pt-2">
        <input id="finish-exam-checkbox" type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true" v-model="finishCheckbox">
        <label class="flex flex-col justify-start" for="finish-exam-checkbox">
          <span>Confirm Submission</span>
        </label>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          type="button"
          @click="modalFinish = false">
          Later
        </button>
        <button
          @click="finishExam"
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          :disabled="!finishCheckbox"
        >
          Yes, Submit Now.
        </button>
      </div>
    </template>
  </JetConfirmationModal>

  <ExamNavigation :items="items"
                  :skipped="skippedQuests"
                  :later="laterQuests"
                  :done="doneQuests"
                  :currentItem="questionData"
                  :showPrevious="questionData?.index > 0"
                  :showNext="questionData?.index !== (items.length - 1)"
                  @previous="() => getQuestions(questionData.index - 1)"
                  @next="() => getQuestions(questionData.index + 1)"
                  @click="(hash, index) => navigationClicked(hash, index)" />

  <Modal
    :closeable="true"
    max-width="full"
    :show="modalImage"
    @close="() => modalImage = false"
  >
    <button class="fixed top-2 right-2 rounded-full bg-red-500 text-white p-2 hover:opacity-25" @click="modalImage = false">
      <XIcon class="w-5 h-5"/>
    </button>
    <img :src="modalImageContent?.url" :alt="modalImageContent?.alt" class="w-full" v-if="modalImageContent !== null"/>
  </Modal>

  <Modal
    :closeable="true"
    max-width="4xl"
    :show="showScenarioModal"
    @close="() => showScenarioModal = false"
  >
    <template #header>
      <h3 class="text-lg font-medium text-gray-900">Scenario</h3>
    </template>
    <div class="p-6">
      <div v-html="vignetteData" class="whitespace-pre-wrap"></div>
    </div>
    <template #footer>
      <div class="flex justify-end">
        <button
          @click="showScenarioModal = false"
          class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md"
        >
          Close
        </button>
      </div>
    </template>
  </Modal>
</template>

<!-- NEW: CSS for smooth loading transitions -->
<style scoped>
.loading-overlay {
  position: fixed;
  top: 20px;
  right: 20px;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  z-index: 1000;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(4px);
  transition: all 0.3s ease;
  font-size: 14px;
}

.question-loading {
  transition: opacity 0.2s ease;
}

.question-loading {
  opacity: 0.9;
  pointer-events: none;
}

/* Smooth transition for content updates */
.question-content {
  transition: opacity 0.2s ease;
}

.question-content.loading {
  opacity: 0.7;
}

/* Ensure loading overlay is always visible */
.loading-overlay {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
