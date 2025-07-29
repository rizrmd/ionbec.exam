<script setup>
import AuthoringQuestionSet from '@/Components/Pages/AuthoringQuestionSet';

const props = defineProps({
  tests: {
    type: Object
  },
  typeOptions: {
    type: Object,
  },
  categories: {
    type: Object
  },
})
</script>

<template>
  <AuthoringQuestionSet  :tests="tests" :typeOptions="typeOptions" :categories="categories" />
</template>
