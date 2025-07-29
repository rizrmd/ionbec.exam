<script setup>
  import AuthoringQuestionSet from '@/Components/Pages/AuthoringQuestionSet';

  const props = defineProps({
    item: {
      type: Object,
    },
    tests: {
      type: Object
    },
    typeOptions: {
      type: Object,
    },
    categories: {
      type: Object
    },
    questions: {
      type: Object
    },
    chartData: {
      type: Object
    },
  })
</script>

<template>
  <AuthoringQuestionSet :item="item" :tests="tests" :typeOptions="typeOptions" :categories="categories" :questions="questions" :chartData="chartData" />
</template>
