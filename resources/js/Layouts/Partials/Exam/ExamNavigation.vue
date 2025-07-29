<script setup>
import { CheckCircleIcon, ExclamationCircleIcon, QuestionMarkCircleIcon, ArrowRightIcon, ArrowLeftIcon } from '@heroicons/vue/solid'
import {computed, ref, toRefs} from "vue";

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
const popup = ref(false)

const btnMouseLeave = () => setTimeout(() => btnNavigation.value = false, 1000)

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

const btnColor = (currentItem, item, question) => {
  if (currentItem !== null && currentItem.item_hash === item.hash) {
    return 'bg-blue-600 text-white'
  } else if (later.value.indexOf(question.hash) !== -1) {
    return 'bg-red-600 text-white'
  } else if (checkDoneQuest(question.hash)) {
    return 'bg-green-600 text-white'
  } else if (checkSkippedQuest(question.hash)) {
    return 'bg-yellow-400 text-white'
  } else {
    return 'bg-gray-100 hover:bg-blue-600 hover:text-white'
  }
}
</script>

<template>
  <transition
    enter-active-class="transition duration-100 ease-out"
    enter-from-class="transform scale-95 opacity-0"
    enter-to-class="transform scale-100 opacity-100"
    leave-active-class="transition duration-75 ease-in"
    leave-from-class="transform scale-100 opacity-100"
    leave-to-class="transform scale-95 opacity-0">
    <div :class="['fixed bottom-14 left-0 right-0 mx-auto w-full', enableInfo ? 'md:w-1/2' : 'md:w-1/3']" @mouseover="popup = true" @mouseleave="popup = false" v-show="btnNavigation || popup">
      <div class="flex flex-col-reverse md:flex-row gap-2 p-2 bg-white shadow-lg rounded-lg max-h-96">
        <div class="flex-1 flex flex-wrap gap-2 overflow-auto">
          <template v-for="(item, itemIndex) in items">
            <button :class="['rounded-md flex justify-center items-center py-2 px-3 w-12 h-12', btnColor(currentItem, item, question)]" @click="$emit('click', question.hash, itemIndex)" v-for="(question, questionIndex) in item.questions">
              {{ itemIndex + 1 }}.{{ questionIndex + 1 }}
            </button>
          </template>
        </div>
        <div class="flex-1 space-y-2" v-if="enableInfo">
          <div class="rounded-md bg-green-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-5 w-5 text-green-400" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">{{ done.length }} Quest Done</h3>
              </div>
            </div>
          </div>
          <div class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <ExclamationCircleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">{{ countSkipped }} Quest Skipped</h3>
              </div>
            </div>
          </div>
          <div class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <QuestionMarkCircleIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">{{ later.length }} Mark As Later</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>

  <div class="fixed bottom-0 left-0 min-w-full backdrop-blur-md bg-white/30 z-10">
    <div class="flex gap-2 justify-center items-center items-center mx-5 my-2">

      <button :class="['flex justify-center items-center text-white rounded-md py-1 px-4', showPrevious ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200 hover:bg-gray-200']" @click="showPrevious ? $emit('previous') : null">
        <ArrowLeftIcon aria-hidden="true" class="h-5 w-5"/>&nbsp;&nbsp;Previous
      </button>
      <button @click="() => btnNavigation = !btnNavigation" @mouseleave="() => btnMouseLeave()" class="flex justify-center items-center text-white bg-primary-600 rounded-md py-1 px-4 hover:bg-primary-700">
        Navigation
      </button>
      <button :class="['flex justify-center items-center text-white rounded-md py-1 px-4', showNext ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200 hover:bg-gray-200 -normal']" @click="showNext ? $emit('next') : null">
        Next&nbsp;&nbsp;<ArrowRightIcon aria-hidden="true" class="h-5 w-5"/>
      </button>
      <button class="ml-4 flex justify-center items-center text-white bg-red-500 rounded-md py-1 px-4 hover:bg-red-700" v-if="extraButton !== null" @click="$emit('extraButtonAction')">
        {{ extraButton }}
      </button>
    </div>
  </div>
</template>

<style scoped>

</style>
