<script setup>
import DetailScoring from '@/Components/Pages/DetailScoring'

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
  }
})
</script>

<template>
  <DetailScoring :items="items" :attempt="attempt" :attemptQuests="attemptQuests" :takerCode="takerCode" :isInterview="isInterview" />
</template>
