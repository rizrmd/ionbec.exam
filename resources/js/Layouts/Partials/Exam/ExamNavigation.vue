<script setup>
import { CheckCircleIcon, ExclamationCircleIcon, QuestionMarkCircleIcon, ArrowRightIcon, ArrowLeftIcon, XIcon } from '@heroicons/vue/solid'
import {computed, ref, toRefs, onMounted, onBeforeUnmount} from "vue";

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
  skipped: {
    type: Array,
    required: false,
    default: [],
  },
  later: {
    type: Array,
    required: false,
    default: [],
  },
  done: {
    type: Array,
    required: false,
    default: [],
  },
  currentItem: {
    type: Object,
    required: false,
    default: null,
  },
  enableInfo: {
    type: Boolean,
    default: true,
  },
  extraButton: {
    type: String,
    default: null,
  },
  showPrevious: {
    type: Boolean,
    default: false
  },
  showNext: {
    type: Boolean,
    default: false
  }
})

const {items, skipped, later, done, currentItem} = toRefs(props)

// merge questions in all items
// const questions = computed(() => {
//   const rawQuestions = items.value.map((item) => item.questions);
//   return rawQuestions.flat(1);
// })
//
// console.log(questions.value.map((question) => question.hash))

const emit = defineEmits(['click', 'previous', 'next', 'extraButtonAction'])

const btnNavigation = ref(false)
// Keep the bar inside the visible viewport when browser chrome or a keyboard
// changes its height without resizing the layout viewport.
const viewportHeight = ref(0)
const viewportInset = ref(0)
const barHeight = ref(60)
const actions = ref(null)
let barObserver
const updateViewport = () => {
  const viewport = window.visualViewport
  viewportHeight.value = viewport?.height ?? window.innerHeight
  viewportInset.value = Math.max(0, window.innerHeight - viewportHeight.value - (viewport?.offsetTop ?? 0))
  barHeight.value = actions.value?.offsetHeight ?? 60
}
const onKeydown = event => {
  if (event.key === 'Escape') btnNavigation.value = false
}
const selectQuestion = (hash, index) => {
  emit('click', hash, index)
  btnNavigation.value = false
}
onMounted(() => {
  updateViewport()
  window.addEventListener('resize', updateViewport)
  window.visualViewport?.addEventListener('resize', updateViewport)
  window.visualViewport?.addEventListener('scroll', updateViewport)
  document.addEventListener('keydown', onKeydown)
  if (typeof ResizeObserver !== 'undefined') {
    barObserver = new ResizeObserver(updateViewport)
    if (actions.value) barObserver.observe(actions.value)
  }
})
onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewport)
  window.visualViewport?.removeEventListener('resize', updateViewport)
  window.visualViewport?.removeEventListener('scroll', updateViewport)
  document.removeEventListener('keydown', onKeydown)
  barObserver?.disconnect()
})

const checkSkippedQuest = (hash) => skipped.value.indexOf(hash) !== -1;
const checkDoneQuest = (hash) => done.value.indexOf(hash) !== -1;

const countSkipped = computed(() => {
  let subtraction = 0
  const result = [...skipped.value].filter((value) => !done.value.includes(value))

  if (currentItem.value !== null) {
    currentItem.value.questions.forEach((question) => {
      if (!done.value.includes(question.hash)) subtraction++
    })
  }
  return result.length - subtraction;
})

// 🔧 CRITICAL FIX: Calculate unique questions done to prevent double counting
// Each question can have both question.hash and item_hash in done array,
// but we should only count each actual question once
const uniqueQuestionsDone = computed(() => {
  const uniqueHashes = new Set();

  // Collect all unique question hashes from done array
  done.value.forEach(hash => {
    // Check if this hash corresponds to a question by looking through all items
    items.value.forEach(item => {
      item.questions.forEach(question => {
        // Only add if it matches a question's hash (not item hash)
        if (hash === question.hash) {
          uniqueHashes.add(question.hash);
        }
      });
    });
  });

  return uniqueHashes.size;
})

// 🔧 FIXED: Create computed properties for better reactivity
const buttonColorMap = computed(() => {
  const colorMap = new Map();

  // Build color map with proper priority
  items.value.forEach((item) => {
    item.questions.forEach((question) => {
      const isCurrent = currentItem.value && currentItem.value.item_hash === item.hash;
      const isLater = later.value && later.value.includes(question.hash);
      const isDone = done.value && done.value.includes(question.hash);
      const isSkipped = skipped.value && skipped.value.includes(question.hash);

      let colorClass;
      if (isCurrent) {
        colorClass = 'bg-blue-600 text-white';
      } else if (isLater) {
        // Later status takes priority over done and skipped
        colorClass = 'bg-red-600 text-white';
      } else if (isDone) {
        colorClass = 'bg-green-600 text-white';
      } else if (isSkipped) {
        colorClass = 'bg-yellow-400 text-white';
      } else {
        colorClass = 'bg-gray-100 hover:bg-blue-600 hover:text-white';
      }

      colorMap.set(question.hash, colorClass);
    });
  });

  return colorMap;
});

// Lazy exam payloads carry a question count before fetching question content.
// These entries navigate by the existing item index; no question IDs or answers
// are invented or sent to the answer API.
const navigationQuestions = item => {
  if (Array.isArray(item.questions) && item.questions.length) return item.questions
  const count = Number(item.questions_count)
  return Number.isSafeInteger(count) && count > 0
    ? Array.from({length: count}, () => ({hash: null}))
    : []
}

const btnColor = (currentItem, item, question) => {
  if (currentItem?.item_hash === item.hash) return 'bg-blue-600 text-white';
  return buttonColorMap.value.get(question.hash) || 'bg-gray-100 hover:bg-blue-600 hover:text-white';
}
</script>

<template>
  <Teleport to="body">
    <div class="exam-navigation" :style="{
      bottom: `${viewportInset}px`,
      '--exam-visible-height': `${viewportHeight}px`,
      '--exam-actions-height': `${barHeight}px`,
    }">
      <section v-if="btnNavigation" id="exam-question-navigation" class="exam-navigation-panel" :class="{ 'no-info': !enableInfo }" aria-label="Question navigation">
        <div class="exam-navigation-heading">
          <span>Questions</span>
          <button type="button" aria-label="Close question navigation" @click="btnNavigation = false"><XIcon class="h-5 w-5"/></button>
        </div>
        <div class="exam-navigation-content">
          <div class="exam-question-grid">
            <template v-for="(item, itemIndex) in items" :key="item.hash">
              <button v-for="(question, questionIndex) in navigationQuestions(item)" :key="`${item.hash}:${questionIndex}`" type="button"
                      :class="['rounded-md flex justify-center items-center', btnColor(currentItem, item, question)]"
                      :aria-current="currentItem?.item_hash === item.hash ? 'step' : null"
                      @click="selectQuestion(question.hash, itemIndex)">
                {{ itemIndex + 1 }}.{{ questionIndex + 1 }}
              </button>
            </template>
          </div>
          <div v-if="enableInfo" class="exam-navigation-summary">
            <div class="bg-green-50 text-green-800"><CheckCircleIcon class="h-5 w-5 text-green-400"/><span>{{ uniqueQuestionsDone }} Quest Done</span></div>
            <div class="bg-yellow-50 text-yellow-800"><ExclamationCircleIcon class="h-5 w-5 text-yellow-400"/><span>{{ countSkipped }} Quest Skipped</span></div>
            <div class="bg-red-50 text-red-800"><QuestionMarkCircleIcon class="h-5 w-5 text-red-400"/><span>{{ later.length }} Mark As Later</span></div>
          </div>
        </div>
      </section>
      <nav ref="actions" class="exam-navigation-bar" aria-label="Exam controls">
        <div class="exam-navigation-actions" :class="{ 'has-extra': extraButton !== null }">
          <button type="button" :disabled="!showPrevious" class="exam-step" @click="$emit('previous')">
            <ArrowLeftIcon aria-hidden="true" class="h-4 w-4"/><span>Previous</span>
          </button>
          <button type="button" class="exam-toggle" :aria-expanded="btnNavigation" aria-controls="exam-question-navigation" @click="btnNavigation = !btnNavigation">Navigation</button>
          <button type="button" :disabled="!showNext" class="exam-step" @click="$emit('next')">
            <span>Next</span><ArrowRightIcon aria-hidden="true" class="h-4 w-4"/>
          </button>
          <button v-if="extraButton !== null" type="button" class="exam-extra" @click="$emit('extraButtonAction')">{{ extraButton }}</button>
        </div>
      </nav>
    </div>
  </Teleport>
</template>

<style scoped>
.exam-navigation, .exam-navigation * { box-sizing: border-box; }
.exam-navigation {
  position: fixed;
  left: 0;
  right: 0;
  z-index: 30;
  pointer-events: none;
}
.exam-navigation-bar {
  width: 100%;
  background: white;
  border-top: 1px solid #e5e7eb;
  box-shadow: 0 -2px 10px rgb(0 0 0 / 0.08);
  pointer-events: auto;
}
.exam-navigation-actions {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .5rem;
  max-width: 44rem;
  margin: 0 auto;
  padding: .5rem max(.5rem, env(safe-area-inset-right, 0px)) max(.5rem, env(safe-area-inset-bottom, 0px)) max(.5rem, env(safe-area-inset-left, 0px));
}
.exam-navigation-actions button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .25rem;
  min-width: 0;
  min-height: 44px;
  padding: .25rem;
  border-radius: .375rem;
  color: white;
  font-size: .875rem;
}
.exam-step { background: #2563eb; }
.exam-step:disabled { background: #e5e7eb; color: #4b5563; cursor: default; }
.exam-toggle { background: #04a95a; }
.exam-extra { grid-column: 1 / -1; background: #ef4444; }
.exam-navigation-panel {
  display: flex;
  flex-direction: column;
  width: calc(100% - 1rem);
  max-width: 60rem;
  max-height: calc(var(--exam-visible-height) - var(--exam-actions-height) - 1rem);
  min-height: 0;
  margin: 0 auto .5rem;
  padding: .5rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: .5rem;
  box-shadow: 0 4px 16px rgb(0 0 0 / .15);
  overflow: hidden;
  pointer-events: auto;
}
.exam-navigation-panel.no-info { max-width: 32rem; }
.exam-navigation-heading { display: flex; flex-shrink: 0; align-items: center; justify-content: space-between; font-weight: 600; }
.exam-navigation-heading button { min-width: 44px; min-height: 32px; display: grid; place-items: center; }
.exam-navigation-content { display: flex; flex-direction: column-reverse; gap: .5rem; min-height: 0; }
.exam-question-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); gap: .5rem; overflow: auto; min-height: 0; align-content: start; }
.exam-question-grid button { min-height: 44px; }
.exam-navigation-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .5rem; flex-shrink: 0; }
.exam-navigation-summary > div { display: flex; align-items: center; gap: .25rem; padding: .5rem; border-radius: .375rem; font-size: .75rem; }
.exam-navigation-summary svg { flex-shrink: 0; }
@media (min-width: 768px) {
  .exam-navigation-content { flex-direction: row; }
  .exam-question-grid { flex: 1; }
  .exam-navigation-summary { flex: 1; display: flex; flex-direction: column; }
}
@media (min-width: 640px) {
  .exam-navigation-actions.has-extra { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .exam-extra { grid-column: auto; }
}
</style>
