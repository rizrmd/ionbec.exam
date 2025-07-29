<script setup>
import {reactive, ref, toRefs, onMounted} from 'vue'
import {BadgeCheckIcon} from '@heroicons/vue/outline'

const props = defineProps({
  exam: {
    type: Object,
  },
  itemTypes: {
    type: Object,
  },
  delivery: {
    type: Object,
  },
  attemptQuests: {
    type: Object,
  }
})

const {delivery, attemptQuests, exam} = toRefs(props);

onMounted(() => {
  window.print();
})

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

const getTakerAnswer = (questionHash, multiChoiceHash = null) => {
  const takerAnswer = attemptQuests.value.find((data) => data.question.hash === questionHash)
  if (takerAnswer) {
    if (multiChoiceHash === null) {
      return takerAnswer.answer;
    } else {
      return multiChoiceHash === takerAnswer.answer_hash
    }
  }

  return '';
}

console.log(exam.value.items)
</script>

<template>
  <div class="container">
    <div class="mb-7">
      <h1 class="text-2xl font-bold">{{ delivery.name }}</h1>
      <h3 class="text-xl"><span class="font-bold">{{ exam.name }}</span> ({{ exam.code }})</h3>
      <h6>{{ exam.description ?? '-' }}</h6>
    </div>

    <div>
      <div class="min-h-full flex-1 mb-8" v-for="(item, key) in exam.items">

        <div class="flex flex-col items-center gap-3 my-5">
          <div v-for="file in item.attachments" v-if="item.attachments.length >= 1" class="w-full flex gap-2 max-w-xl">
            <div class="flex-auto flex justify-center items-center">
              <img :src="file.url" :alt="file.description" class="rounded-lg">
            </div>
          </div>
        </div>

        <div v-for="(question, questKey) in item.questions" class="question">
          <template v-if="questKey === 0">
            <div class="flex justify-between items-center">
              <div class="relative">
                <div class="text-xl font-bold mb-2">Question #{{ key + 1 }}</div>
              </div>
              <div>
                <span class="px-2 py-1 border-2 border-gray-300 rounded-md text-sm" v-if="item.type">{{ itemTypes[item.type] }}</span>
              </div>
            </div>
          </template>

          <div class="pt-5">
            <div class="border-2 border-gray-300 p-4 rounded-lg">
              <div v-html="question.question"></div>
              <div class="flex flex-col gap-2 mt-6 w-auto" v-if="question.type !== null && question.type.name === 'multiple-choice'">
                <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" :class="['cursor-default flex text-left px-3 py-2 bg-gray-100 rounded-md items-center', getTakerAnswer(question.hash, answer.hash) ? 'border border-gray-300' : '', answer.is_correct_answer ? 'border border-gray-900' : '']">
                  <span class="mr-3 font-bold uppercase" v-if="!answer.is_correct_answer">{{ answerIndex[ansIndex] }}</span>
                  <span class="mr-3" v-else><BadgeCheckIcon aria-hidden="true" class="-mx-1 h-5 w-5" v-if="!question.show_answer"/></span>
                  <span v-html="answer.answer"></span>
                </button>
              </div>
              <div class="mt-2" v-else>
                <div class="bg-gray-100 rounded-lg w-full h-36 border-2 border-gray-100 p-3">
                  <span v-html="getTakerAnswer(question.hash)"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  padding-top: 24px;
  padding-bottom: 24px;
  background: white;
}

.question {
  /*page-break-after: always;*/
  break-inside: avoid;
}

@media print
{
  .container {
    padding-top: 0;
  }
}
</style>

<style>
body, #app {
  background: white !important;
}
</style>
