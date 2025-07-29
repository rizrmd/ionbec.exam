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
  },
  taker: {
    type: Object
  },
  takerCode: {
    type: String
  }
})

const {delivery, attemptQuests, exam, taker, takerCode} = toRefs(props);

onMounted(() => {
  window.print();
})

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

const getTakerAnswer = (questionHash, multiChoiceHash = null, asObject = false) => {
  const takerAnswer = attemptQuests.value.find((data) => data.question.hash === questionHash)
  if (takerAnswer) {
    if (asObject) {
      return takerAnswer
    } else if (multiChoiceHash === null) {
      return takerAnswer.answer
    } else {
      return takerAnswer
    }
  }

  return '';
}

</script>

<template>
  <div class="container">
    <div class="mb-7">
      <h1 class="text-2xl font-bold">{{ delivery.name }}</h1>
      <h6><span class="font-bold">{{ exam.name }}</span> ({{ exam.code }})</h6>
      <h6 v-if="exam.description" class="mt-1">{{ exam.description ?? '-' }}</h6>
      <table class="table mt-4 ml-4">
        <tr>
          <td>Participant</td>
          <td>: {{ takerCode }}</td>
        </tr>
        <tr>
          <td>Schedule</td>
          <td>: {{ delivery.scheduled_at }} - {{ delivery.ended_at }} ({{ delivery.duration }} menit)</td>
        </tr>
        <tr>
          <td class="pr-2">Total Questions</td>
          <td>: {{ delivery.questions_count }}</td>
        </tr>
      </table>
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
                <span class="px-2 py-1 border-2 border-gray-300 rounded-md text-sm"
                      v-if="item.type">{{ itemTypes[item.type] }}</span>
              </div>
            </div>
          </template>

          <div class="pt-5">
            <div class="border-2 border-gray-300 p-4 rounded-lg">
              <div class="text-right text-sm mb-2" v-if="delivery.is_interview">
                Given score: {{ getTakerAnswer(question.hash, null, true).score ?? '-' }}
              </div>
              <div v-html="question.question"></div>
              <div class="flex flex-col gap-2 mt-6 w-auto"
                   v-if="question.type !== null && question.type.name === 'multiple-choice'">
                <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" :class="[
                  'cursor-default flex text-left px-3 py-2 bg-gray-100 rounded-md items-center',
                  answer.is_correct_answer ? 'border border-gray-900' : '',
                  (getTakerAnswer(question.hash, answer.hash)?.answer_hash === answer.hash) ? (answer.is_correct_answer ? 'bg-green-300' : 'bg-red-300') : ''
                ]">
                  <span class="mr-3 font-bold uppercase" v-if="!answer.is_correct_answer">{{
                      answerIndex[ansIndex]
                    }}</span>
                  <span class="mr-3" v-else><BadgeCheckIcon aria-hidden="true" class="-mx-1 h-5 w-5"
                                                            v-if="!question.show_answer"/></span>
                  <span v-html="answer.answer"></span>
                </button>
              </div>
              <div class="mt-2" v-else>
                <div class="bg-gray-100 rounded-lg w-full border-2 border-gray-100 p-3"
                     v-if="getTakerAnswer(question.hash) !== ''">
                  <span v-html="getTakerAnswer(question.hash)"></span>
                </div>
                <div class="text-sm text-gray-600" v-else>Not answered.</div>
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

@media print {
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
