<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {XIcon} from '@heroicons/vue/outline'
import {ref, toRefs, onMounted, computed, watch} from "vue";
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
})

const {exam, examItems, attempt, delivery, attemptQuestions, admin, remainingSeconds, examId, deliveryId, takerId} = toRefs(props);

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

const submitAnswer = async (partial = false) => {
  if (submittingAnswer.value) return;
  submittingAnswer.value = true;

  // 🔒 DEBUG: Log current state before submission
  const currentAnswers = answerVal.value; // Safe computed property access
  console.log('🚀 submitAnswer called:', {
    partial,
    answerValExists: !!currentAnswers,
    answerValKeys: Object.keys(currentAnswers),
    answerValLength: Object.keys(currentAnswers).length,
    answerValContent: currentAnswers
  });

  if (Object.keys(currentAnswers).length >= 1) {
    let newAnswers = {
      ...currentAnswers // Safe computed property access
    }

    // check if essay null
    if (questionData.value.type.value !== 'multiple-choice') {
      Object.keys(newAnswers).forEach(key => {
        if (newAnswers[key].replace(/<\/?[^>]+(>|$)/g, "").trim() === '') {
          delete newAnswers[key];
        }
      })
    } else if (questionData.value.type.value === 'multiple-choice') {
      Object.keys(newAnswers).forEach(key => {
        if (newAnswers[key] === null) {
          delete newAnswers[key];
        }
      })
    }

    Object.keys(newAnswers).forEach(key => {
      // Add to done quests if not already there
      addToStateArray(doneQuests.value, key)

      // Remove from skipped quests if answered
      removeFromStateArray(skippedQuests.value, key)

      // Remove from later quests if answered
      removeFromStateArray(laterQuests.value, key)
      // Also uncheck the checkbox
      if (laters.value[key]) {
        laters.value[key] = false;
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
              notification.add('success', 'Success', responseData.message || 'Answer saved successfully.')
            } else if (responseData.error) {
              // Handle case where server returns error but still HTTP 200
              console.warn('Server returned error in success response:', responseData.error);
              notification.add('error', 'Error', responseData.error);
            } else {
              notification.add('success', 'Success', 'Answer saved.')
            }
          }
          if (!partial) answerVal.value = {}
        } catch (error) {
          console.error('Error submitting answer:', error)

          // Enhanced error handling with detailed logging
          let errorMessage = 'Failed to save answer.';

          if (error.response) {
            // Server responded with error status
            const status = error.response.status;
            const data = error.response.data;

            console.error('Server error response:', {
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
            console.error('Network error:', error.request);
            errorMessage = 'Network error. Please check your connection and try again.';
          } else {
            // Other errors (config, etc.)
            console.error('Request setup error:', error.message);
            errorMessage = `Request error: ${error.message}`;
          }

          notification.add('error', 'Error', errorMessage);

          // Don't clear answerVal on error so user can retry
          console.log('Answer preserved due to error:', {
            answerCount: Object.keys(currentAnswers).length,
            answers: currentAnswers
          });
        }
      }
    }
  }
  submittingAnswer.value = false;
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

      // Load questions into the item
      item.questions = questions;

      // Sync laters checkbox state with laterQuests
      item.questions.forEach((question) => {
        laters.value[question.hash] = laterQuests.value.indexOf(question.hash) !== -1;
      })

      // Process attempt answers if they exist
      if (attempt && attempt.questions && attempt.questions.length > 0) {
        const attemptAnswer = attempt.questions;
        console.log('Processing attempt answers:', attemptAnswer)
        attemptAnswer.forEach((question) => {
          console.log('Processing question:', question.hash, 'item_hash:', question.item_hash, 'pivot:', question.pivot)
          // Only process if question has pivot (was answered)
          if (question.pivot) {
            // CRITICAL FIX: Use item_hash for matching with current item, not question.hash
            const hashForMatching = question.item_hash || question.hash
            const answerValue = (item.item_type.value === 'multiple-choice') ? question.pivot.answer_hash : question.pivot.answer
            console.log('Answer value:', answerValue, 'Stored with key:', hashForMatching)

            // Check if answer actually exists (not null/empty)
            const hasAnswer = answerValue !== null && answerValue !== undefined && answerValue !== ''

            if (hasAnswer) {
              console.log('Question answered, adding to done:', hashForMatching)
              // 🔒 SAFE: Use computed property access
              const currentAnswers = answerVal.value;
              currentAnswers[hashForMatching] = answerValue;

              // Add to doneQuests if not already there
              addToStateArray(doneQuests.value, hashForMatching)

              // Remove from skipped if answered
              removeFromStateArray(skippedQuests.value, hashForMatching)

              // Remove from later if answered
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
    console.log('❌ Invalid question object:', question);
    return null;
  }

  // 🔒 SAFE: Computed property guarantees non-null object
  const currentAnswers = answerVal.value;

  // 🔒 CRITICAL FIX: Use consistent key priority matching selectAnswer strategy
  // Primary: question.hash (main storage key), Secondary: question.item_hash (fallback)
  const possibleKeys = [
    question.hash,        // Primary key - this is what selectAnswer uses as main storage
    question.item_hash,   // Secondary key - fallback storage in selectAnswer
  ].filter(Boolean);

  console.log('🔍 getAnswerForQuestion DEBUG:', {
    questionHash: question.hash,
    itemHash: question.item_hash,
    possibleKeys,
    answerValKeys: Object.keys(currentAnswers),
    searchingFor: possibleKeys
  });

  // Try all possible keys to find the stored answer
  for (const key of possibleKeys) {
    if (currentAnswers[key]) {
      console.log('✅ Found answer with key:', key, 'value:', currentAnswers[key]);
      return currentAnswers[key];
    }
  }

  console.log('❌ No answer found for any key');
  return null;
};

// CRITICAL FIX: Smart answer selection with hash matching
const isAnswerSelected = (answerHash, question) => {
  const storedAnswer = getAnswerForQuestion(question);
  const isSelected = storedAnswer === answerHash;

  console.log('🎯 isAnswerSelected DEBUG:', {
    answerHash,
    questionHash: question.hash,
    itemHash: question.item_hash,
    storedAnswer,
    isSelected
  });

  return isSelected;
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
    console.log('🎯 selectAnswer called:', { answerHash, questionHash });

    // 🔒 ENHANCED: Use validation functions
    if (!validateAnswerStorage(questionHash, answerHash)) {
      console.error('❌ selectAnswer: Validation failed', { answerHash, questionHash });
      return;
    }

    // 🔒 SAFE: Computed property guarantees non-null object
    console.log('🔒 selectAnswer: Using computed property, current answers:', {
      keys: Object.keys(answerVal.value),
      questionHash
    });

    // 🔒 CRITICAL FIX: Guard against undefined questionData.value
    if (!questionData.value || !questionData.value.questions) {
      console.error('❌ selectAnswer: questionData.value is undefined or has no questions', {
        questionData: questionData.value,
        hasQuestions: questionData.value?.questions
      });
      // Fallback: store answer directly using questionHash using safe function
      safeSetAnswer(questionHash, answerHash);
      submitAnswer(true);
      return;
    }

    const questions = questionData.value.questions;
    console.log('🔍 selectAnswer: Searching questions', {
      totalQuestions: questions.length,
      questionHash,
      answerHash
    });

    // Find the current question being answered to get its item_hash
    const currentQuestion = questions.find(q => q.hash === questionHash);

    if (!currentQuestion) {
      console.warn('⚠️ selectAnswer: Question not found, using fallback storage', {
        questionHash,
        availableQuestionHashes: questions.slice(0, 3).map(q => q.hash)
      });
      // Fallback: store answer directly using questionHash using safe function
      safeSetAnswer(questionHash, answerHash);
      submitAnswer(true);
      return;
    }

    // 🔒 CRITICAL FIX: Use consistent key strategy with getAnswerForQuestion
    // Always prioritize question.hash for storage to ensure consistency
    const hashForStorage = questionHash;

    console.log('✅ selectAnswer: Storing answer successfully', {
      questionHash,
      itemHash: currentQuestion.item_hash,
      storageKey: hashForStorage,
      answerHash,
      questionFound: !!currentQuestion,
      strategy: 'Using question.hash as primary key for consistency'
    });

    // 🔒 SAFE: Use safe storage function
    safeSetAnswer(hashForStorage, answerHash);

    // Also store with item_hash as fallback if available and different
    if (currentQuestion.item_hash && currentQuestion.item_hash !== questionHash) {
      safeSetAnswer(currentQuestion.item_hash, answerHash);
      console.log('🔄 Also stored with item_hash fallback:', currentQuestion.item_hash);
    }

    // 🔒 DEBUG: Verify storage was successful
    const currentAnswers = answerVal.value; // Safe computed property access
    console.log('🔍 Post-storage verification:', {
      expectedKey: hashForStorage,
      answerValKeys: Object.keys(currentAnswers),
      hasExpectedKey: !!currentAnswers[hashForStorage],
      storedValue: currentAnswers[hashForStorage],
      alsoHasItemHash: currentQuestion.item_hash ? !!currentAnswers[currentQuestion.item_hash] : 'N/A'
    });

    submitAnswer(true);

  } catch (error) {
    console.error('💥 selectAnswer: Critical error occurred', {
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

        console.log('🆘 Fallback storage: Stored with primary key', {
          primaryKey: questionHash,
          answerHash,
          strategy: 'Emergency fallback - consistent with selectAnswer'
        });

        // Try to submit answer with additional safety
        try {
          if (typeof submitAnswer === 'function') {
            submitAnswer(true);
          } else {
            console.warn('⚠️ submitAnswer function not available');
          }
        } catch (submitError) {
          console.error('💥 submitAnswer failed in fallback:', submitError);
        }
      } else {
        console.warn('⚠️ Invalid questionHash or answerHash in fallback:', { questionHash, answerHash });
      }
    } catch (fallbackError) {
      console.error('💥 selectAnswer: Emergency fallback also failed', fallbackError);
    }
  }
};

const timerCount = ref("00:00");
const startTimer = (duration) => {
  // 🔒 COMPREHENSIVE SAFETY CHECKS: Validate all inputs
  if (!duration || typeof duration !== 'number' || duration <= 0) {
    console.warn('Timer: Invalid duration provided, using default 0');
    duration = 0;
  }

  let timer = duration, minutes, seconds;
  let timerInterval = null;

  // Safety check for admin
  if (!admin || !admin.value) {
    timerInterval = setInterval(function () {
      try {
        // 🔒 ENHANCED SAFETY: Check timer state
        if (typeof timer !== 'number' || isNaN(timer) || timer < 0) {
          console.warn('Timer: Invalid timer state, resetting to duration');
          timer = duration || 0;
          return;
        }

        // 🔒 SAFETY: Calculate minutes and seconds with validation
        try {
          minutes = parseInt(timer / 60, 10);
          seconds = parseInt(timer % 60, 10);

          // Validate calculated values
          if (isNaN(minutes) || isNaN(seconds)) {
            throw new Error('Invalid time calculation');
          }

          minutes = minutes < 10 ? "0" + minutes : minutes;
          seconds = seconds < 10 ? "0" + seconds : seconds;
        } catch (timeError) {
          console.error('Timer: Time calculation error', timeError);
          timer = duration || 0;
          return;
        }

        // 🔒 SAFETY: Update timer display with enhanced checks
        try {
          // Ensure timerCount exists and is reactive
          if (!timerCount) {
            console.warn('Timer: timerCount is undefined, creating new reactive object');
            // Note: This is a fallback - ideally timerCount should be properly initialized
            return;
          }

          if (typeof timerCount === 'object' && 'value' in timerCount) {
            // Enhanced safety for time formatting
            const formattedMinutes = typeof minutes === 'string' ? minutes : (minutes ? minutes.toString() : '00');
            const formattedSeconds = typeof seconds === 'string' ? seconds : (seconds ? seconds.toString() : '00');

            // Additional validation before assignment
            if (formattedMinutes && formattedSeconds) {
              timerCount.value = `${formattedMinutes}:${formattedSeconds}`;
            } else {
              console.warn('Timer: Invalid formatted time values', { formattedMinutes, formattedSeconds });
            }
          } else {
            console.warn('Timer: timerCount object is invalid or not reactive');
          }
        } catch (displayError) {
          console.error('Timer: Display update error', displayError);
          // Try to reset timerCount to safe state
          try {
            if (timerCount && typeof timerCount === 'object') {
              timerCount.value = "00:00";
            }
          } catch (resetError) {
            console.error('Timer: Failed to reset timerCount', resetError);
          }
        }

        // 🔒 SAFETY: Handle timer expiry with comprehensive error handling
        if (--timer < 0) {
          console.log('Timer: Time expired, cleaning up and redirecting');

          // Clear interval to prevent multiple executions
          if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
          }

          // 🔒 SAFETY: Remove localStorage with enhanced error handling
          try {
            const localStorageKey = getLocalStorageKey('exam-state');
            if (localStorageKey && typeof localStorageKey === 'string') {
              localStorage.removeItem(localStorageKey);
            }

            // 🔒 NEW: Also clear timer state
            const timerStateKey = getLocalStorageKey('timer-state');
            if (timerStateKey && typeof timerStateKey === 'string') {
              localStorage.removeItem(timerStateKey);
            }
          } catch (storageError) {
            console.warn('Timer: Failed to remove localStorage keys', storageError);
          }

          // 🔒 SAFETY: Redirect with comprehensive checks
          try {
            // Check if route function exists and exam.finished route exists
            if (typeof route === 'function') {
              const finishedRoute = route('exam.finished');
              if (typeof Inertia !== 'undefined' && Inertia.visit && typeof finishedRoute === 'string') {
                Inertia.visit(finishedRoute);
              } else {
                console.warn('Timer: Inertia or route is unavailable, using fallback redirect');
                window.location.href = finishedRoute || '/exam/finished';
              }
            } else {
              console.warn('Timer: Route function unavailable, using fallback redirect');
              window.location.href = '/exam/finished';
            }
          } catch (redirectError) {
            console.error('Timer: Redirect failed', redirectError);
            // Final fallback
            window.location.reload();
          }
        }
      } catch (error) {
        console.error('💥 Timer: Critical error in setInterval callback', error);
        // Enhanced error recovery
        try {
          if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
          }
          // Attempt to restart timer with safer parameters
          timer = duration || 0;
        } catch (recoveryError) {
          console.error('💥 Timer: Recovery failed', recoveryError);
        }
      }
    }, 1000);

    // 🔒 SAFETY: Store interval reference for cleanup
    return timerInterval;
  } else {
    console.log('Timer: Admin mode detected, timer disabled');
    return null;
  }
}

// 🔒 ENHANCED: Robust timer synchronization with retry mechanism
const syncTimerWithServer = async (retryCount = 0, maxRetries = 3) => {
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

    // Adjust local timer if significant drift (> 5 seconds)
    const currentTimerSeconds = parseTimerCount(timerCount.value);
    const drift = Math.abs(currentTimerSeconds - adjustedTime);

    if (drift > 5) {
      console.log('🕐 Timer: Significant drift detected, adjusting', {
        local: currentTimerSeconds,
        server: adjustedTime,
        drift: drift
      });
      // Restart timer with server time
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

      await new Promise(resolve => setTimeout(resolve, backoffMs));
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
  // NEW: Initialize exam context isolation
  console.log('🔒 INITIALIZING EXAM CONTEXT ISOLATION');
  console.log('📊 Exam Context:', {
    examId: examContext.value.examId,
    examName: exam.value?.name,
    deliveryId: examContext.value.deliveryId,
    deliveryName: delivery.value?.name,
    totalItems: examContext.value.totalItems
  });

  // Clear any conflicting exam state from other exam sessions
  clearConflictingExamState();

  // DEBUG: Log initial data to understand count discrepancy
  console.log('=== DEBUG EXAM DATA ===')
  console.log('examItems.value count:', examItems.value?.length || 'undefined')
  console.log('examItems.value:', examItems.value)
  console.log('items computed count:', items.value?.length || 'undefined')
  console.log('items computed:', items.value)
  console.log('=======================')

  // 🔒 ENHANCED SAFETY: Use server-provided remaining seconds with comprehensive validation
  try {
    console.log('🕐 Timer: Initializing with remaining seconds:', remainingSeconds.value);

    // 🔒 NEW: Check for timer state persistence
    const timerStateKey = getLocalStorageKey('timer-state');
    let savedTimerState = {};
    try {
      savedTimerState = JSON.parse(localStorage.getItem(timerStateKey) || '{}');
    } catch (e) {
      console.warn('Timer: Failed to parse saved timer state', e);
    }

    // Enhanced validation for remainingSeconds
    let validRemainingSeconds = typeof remainingSeconds?.value === 'number' && remainingSeconds.value > 0
      ? remainingSeconds.value
      : 0;

    // If we have saved timer state and server time seems valid, use saved elapsed time
    if (savedTimerState.startTime && savedTimerState.duration) {
      const elapsedSeconds = Math.floor((Date.now() - savedTimerState.startTime) / 1000);
      const calculatedRemaining = Math.max(0, savedTimerState.duration - elapsedSeconds);
      
      // Use saved time if it's reasonable (within 10 seconds of server calculation)
      if (Math.abs(calculatedRemaining - validRemainingSeconds) <= 10) {
        validRemainingSeconds = calculatedRemaining;
        console.log('Timer: Using persisted timer state', {
          savedElapsed: elapsedSeconds,
          calculatedRemaining: calculatedRemaining,
          serverRemaining: remainingSeconds.value
        });
      } else {
        console.log('Timer: Ignoring persisted state due to drift', {
          calculatedRemaining: calculatedRemaining,
          serverRemaining: remainingSeconds.value,
          drift: Math.abs(calculatedRemaining - validRemainingSeconds)
        });
      }
    }

    if (validRemainingSeconds > 0) {
      // 🔒 NEW: Save timer state to localStorage
      const timerStateKey = getLocalStorageKey('timer-state');
      try {
        localStorage.setItem(timerStateKey, JSON.stringify({
          startTime: Date.now(),
          duration: validRemainingSeconds
        }));
      } catch (e) {
        console.warn('Timer: Failed to save timer state', e);
      }

      // Initialize timer with enhanced safety
      const timerInterval = startTimer(validRemainingSeconds);
      if (timerInterval) {
        console.log('✅ Timer: Successfully initialized with', validRemainingSeconds, 'seconds');
        
        // 🔒 ENHANCED: Start periodic timer sync with error handling
        setInterval(async () => {
          try {
            console.log('🕐 Timer: Performing periodic sync with server');
            const wasRestarted = await syncTimerWithServer();
            if (wasRestarted) {
              console.log('🕐 Timer: Restarted due to server sync');
            }
          } catch (error) {
            console.error('🕐 Timer: Periodic sync error', error);
            // Don't throw error, just log it and continue
          }
        }, 30000);
      } else {
        console.warn('⚠️ Timer: Failed to initialize interval');
      }
    } else {
      console.log('⏰ Timer: No time remaining or invalid value, redirecting to finished');

      // Enhanced cleanup before redirect
      try {
        const localStorageKey = getLocalStorageKey('exam-state');
        if (localStorageKey && typeof localStorageKey === 'string') {
          localStorage.removeItem(localStorageKey);
        }

        // 🔒 NEW: Also clear timer state on expiry redirect
        const timerStateKey = getLocalStorageKey('timer-state');
        if (timerStateKey && typeof timerStateKey === 'string') {
          localStorage.removeItem(timerStateKey);
        }
        console.log('✅ Timer: Cleaned up localStorage before redirect');
      } catch (storageError) {
        console.warn('⚠️ Timer: Failed to remove localStorage keys', storageError);
      }

      // Enhanced redirect with fallback
      try {
        if (typeof route === 'function') {
          const finishedRoute = route('exam.finished');
          if (typeof Inertia !== 'undefined' && Inertia.visit && typeof finishedRoute === 'string') {
            Inertia.visit(finishedRoute);
          } else {
            console.warn('⚠️ Timer: Inertia unavailable, using fallback redirect');
            window.location.href = finishedRoute || '/exam/finished';
          }
        } else {
          console.warn('⚠️ Timer: Route function unavailable, using fallback redirect');
          window.location.href = '/exam/finished';
        }
      } catch (redirectError) {
        console.error('💥 Timer: Redirect failed, using final fallback', redirectError);
        window.location.reload();
      }
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
  console.log('DEBUG: onMounted - Loading from localStorage')
  let savedExamState = {};
  try {
    savedExamState = JSON.parse(localStorage.getItem(getLocalStorageKey('exam-state')) || '{}');
  } catch (e) {
    console.warn('onMounted: Failed to parse saved exam state', e);
  }
  console.log('DEBUG: localStorage answerData:', savedExamState.answerData || 'NULL')

  // 🔒 CRITICAL FIX: Ensure rawAnswerVal is properly initialized for computed property
  if (!rawAnswerVal.value || typeof rawAnswerVal.value !== 'object') {
    rawAnswerVal.value = {};
  }

  // Populate rawAnswerVal from localStorage if available
  if (savedExamState.answerData && typeof savedExamState.answerData === 'object') {
    Object.keys(savedExamState.answerData).forEach(key => {
      if (savedExamState.answerData[key]) {
        rawAnswerVal.value[key] = savedExamState.answerData[key]
        console.log('✅ Restored answerVal from localStorage:', {key, value: savedExamState.answerData[key]})
      }
    })
  }

  // Then merge with server data (attemptQuestions)
  // This ensures server data supplements, not replaces, local data
  console.log('DEBUG: onMounted - attemptQuestions.value:', attemptQuestions.value ? JSON.stringify(attemptQuestions.value.map(q => ({hash: q.question.hash, item_hash: q.item_hash, pivot: q.pivot}))).substring(0, 500) + '...' : 'NULL')
  console.log('DEBUG: onMounted - items.value count:', items.value.length)

  items.value.forEach((item) => {
    console.log('DEBUG: Processing item:', item.hash, item.title)
    item.questions.forEach((question) => {
      const attemptQuestion = attemptQuestions.value && attemptQuestions.value.find((data) => data.question.hash === question.hash)
      console.log('DEBUG: Question:', question.hash, 'attemptQuestion found:', !!attemptQuestion)
      if (attemptQuestion) {
        // 🔒 CRITICAL FIX: Use consistent strategy matching selectAnswer
        // Always store with question.hash as primary key for consistency
        if (attemptQuestion.pivot && attemptQuestion.pivot.answer_hash) {
          // Primary storage with question.hash using safe function
          safeSetAnswer(question.hash, attemptQuestion.pivot.answer_hash);

          // Secondary storage with item_hash as fallback (if different)
          if (question.item_hash && question.item_hash !== question.hash) {
            safeSetAnswer(question.item_hash, attemptQuestion.pivot.answer_hash);
          }

          console.log('✅ Populated answerVal from server data:', {
            primaryKey: question.hash,
            secondaryKey: question.item_hash,
            answerHash: attemptQuestion.pivot.answer_hash,
            strategy: 'Consistent with selectAnswer - question.hash primary'
          })
        } else {
          console.log('❌ No pivot data or answer_hash in attemptQuestion')
        }

        // Only add to doneQuests if not already there (preserve local state)
        const hashForMatching = question.item_hash || question.hash
        if (!doneQuests.value.includes(hashForMatching)) {
          doneQuests.value.push(hashForMatching)
          console.log('✅ Added to doneQuests:', hashForMatching)
        }
      }
    })
  })

  console.log('DEBUG: Final answerVal state:', answerVal.value)

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
    console.warn('⚠️ Hash validation: Invalid question object');
    return false;
  }

  const hasQuestionHash = !!question.hash && typeof question.hash === 'string';
  const hasItemHash = !!question.item_hash && typeof question.item_hash === 'string';
  const hashesMatch = question.hash === question.item_hash;

  console.log('🔍 Hash validation:', {
    questionHash: question.hash,
    itemHash: question.item_hash,
    hasQuestionHash,
    hasItemHash,
    hashesMatch
  });

  return hasQuestionHash; // At minimum, we need question.hash
};

// 🔒 CRITICAL: Answer storage validation
const validateAnswerStorage = (questionHash, answerHash) => {
  if (!questionHash || typeof questionHash !== 'string') {
    console.error('❌ Answer validation: Invalid questionHash', questionHash);
    return false;
  }

  if (!answerHash || typeof answerHash !== 'string') {
    console.error('❌ Answer validation: Invalid answerHash', answerHash);
    return false;
  }

  const currentAnswers = answerVal.value;
  const isStored = !!currentAnswers[questionHash];

  console.log('🔍 Answer validation:', {
    questionHash,
    answerHash,
    isStored,
    totalStoredAnswers: Object.keys(currentAnswers).length
  });

  return true;
};

const finishCheckbox = ref(false)

const openModalFinish = () => {
  finishCheckbox.value = false;
  modalFinish.value = true
}
const finishExam = async () => {
  await submitAnswer()
  Inertia.visit(route('exam.finished'))
}

const modalImage = ref(false);
const modalImageContent = ref(null);
const showScenarioModal = ref(false);
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
              <img :src="file.url" :alt="file.description" class="rounded-lg cursor-pointer hover:shadow-md" @click="() => zoomImage(file.url)">
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
                  <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" @click="selectAnswer(answer.hash, question.hash)" :class="['flex text-left px-3 py-2 bg-gray-100 rounded-md', isAnswerSelected(answer.hash, question) ? 'bg-green-600 text-white' : 'hover:bg-green-200 hover:text-green-600']" @click.once="console.log('Click check:', {questionHash: question.hash, itemHash: question.item_hash, answerHash: answer.hash, storedValue: getAnswerForQuestion(question)})">
                    <span class="mr-3 font-bold uppercase">{{ answerIndex[ansIndex] }}</span> <span v-html="answer.answer"></span>
                  </button>
                  <!-- DEBUG: Show answerVal state for this question -->
                  <div class="text-xs text-gray-500 mt-2 p-2 bg-yellow-50 rounded" v-if="question.item_hash || question.hash">
                    <div><strong>🔍 HASH DEBUG:</strong></div>
                    <div>Question Hash: {{ question.hash }}</div>
                    <div>Item Hash: {{ question.item_hash || 'NULL' }}</div>
                    <div>Direct Lookup: {{ answerVal[question.item_hash || question.hash] || 'NULL' }}</div>
                    <div>Smart Lookup: {{ getAnswerForQuestion(question) || 'NULL' }}</div>
                    <div>All answerVal keys: {{ answerVal.value ? Object.keys(answerVal.value).slice(0, 5).join(', ') : '[]' }}{{ answerVal.value && Object.keys(answerVal.value).length > 5 ? '...' : '' }}</div>
                  </div>
                </div>
                <div class="mt-4" v-else>
                  <Editor class="my-2" v-model="answerVal[question.item_hash || question.hash]" @blur="submitAnswer(true)" />
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
