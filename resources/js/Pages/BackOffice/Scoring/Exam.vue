<script setup>
import DetailScoring from '@/Components/Pages/DetailScoring'
import { ExclamationCircleIcon } from '@heroicons/vue/solid'

const props = defineProps({
  items: {
    type: Object,
  },
  attempt: {
    type: Object,
  },
  attemptQuests: {
    type: Object,
  },
  takerCode: {
    type: String,
    default: null,
  },
  isInterview: {
    type: Boolean,
    default: false
  },
  hasAttemptQuestions: {
    type: Boolean,
    default: false
  },
  delivery: {
    type: Object,
    default: null
  },
  error: {
    type: String,
    default: null
  }
})
</script>

<template>
  <!-- Error state -->
  <div v-if="error" class="min-h-screen flex items-center justify-center">
    <div class="text-center p-8 bg-red-50 border border-red-200 rounded-lg">
      <ExclamationCircleIcon class="w-16 h-16 text-red-500 mx-auto mb-4" />
      <h2 class="text-xl font-bold text-red-800 mb-2">⚠️ Loading Error</h2>
      <p class="text-red-600 mb-4">{{ error }}</p>
      <button @click="$inertia.reload()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
        Retry
      </button>
    </div>
  </div>

  <!-- Normal content -->
  <DetailScoring
    v-else
    :items="items"
    :attempt="attempt"
    :attemptQuests="attemptQuests"
    :takerCode="takerCode"
    :is-interview="isInterview"
    :has-attempt-questions="hasAttemptQuestions"
    :delivery="delivery"
  />
</template>
