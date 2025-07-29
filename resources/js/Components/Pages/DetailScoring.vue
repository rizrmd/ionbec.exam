<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {computed, ref, toRefs, watch} from 'vue'
import Card from '@/Components/Card'
import {BadgeCheckIcon, ChevronLeftIcon, ChevronRightIcon} from '@heroicons/vue/outline'
import {notification} from "@/Store/notification";
import moment from 'moment';
import ExamNavigation from "@/Layouts/Partials/Exam/ExamNavigation.vue";
import {ExclamationCircleIcon, InformationCircleIcon} from '@heroicons/vue/solid'

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

const {items, attemptQuests, attempt, isInterview} = toRefs(props)

const answers = ref(attemptQuests.value);
const scoredAnswers = computed(() => {
  return answers.value.filter((a) => a.score > 0).map((a) => a.question.hash);
});

const selectedItem = ref(null);

const options = computed(() => {
  let data = [];
  items.value.forEach((item, index) => {
    data.push({
      text: `#${index + 1} ${item.title}`,
      value: item.hash,
      index: index,
    })
  })
  return data;
});

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
const questionData = ref(null);

const scores = ref([]);
const notes = ref([]);

watch(selectedItem, function (newVal, prevVal) {
  if (newVal !== null) {
    getItems(newVal)
  } else {
    questionData.value = null;
  }
})

const getItems = (newVal) => {
  const item = items.value.find((item) => item.hash === newVal.value)
  if (item) {
    questionData.value = item;
    scores.value = [];
    if (item.item_type.value !== 'multiple-choice') {
      item.questions.forEach((question) => {
        const answer = answers.value.find((answer) => answer.question.hash === question.hash)
        scores.value[question.hash] = answer?.score ?? 0
        notes.value[question.hash] = answer?.answer
      })
    }
  }
}

if (options.value.length >= 1) {
  selectedItem.value = options.value[0]
}

const getTakerAnswer = (questionHash, multiChoiceHash = null) => {
  const takerAnswer = answers.value.find((data) => data.question.hash === questionHash)
  if (takerAnswer) {
    if (multiChoiceHash === null) {
      return takerAnswer.answer;
    } else {
      return takerAnswer
    }
  }

  return '';
}

const showLiveInterviewQuestion = async (e, question_hash) => {
  const {data: responseData} = await axios.post(route('back-office.live-interview.show-question', {
    delivery_hash: attempt.value.delivery.hash,
    attempt_hash: attempt.value.hash,
  }), {
    question_hash: question_hash
  })

  if (responseData) {
    console.log("SUCCESS", responseData)
    notification.add('success', 'Success', 'Question shown to live screen!')
  } else {
    console.log("FAILS", responseData)
  }
}

const invalidateScore = async (e, question_hash) => {
  await submitQuestionScore(question_hash, scores.value[question_hash], notes.value[question_hash])
}

const submitQuestionScore = async (question_hash, score, note = null) => {
  // console.log("SUBMITTED NOTE", notes)
  const is_correct = score !== 0;
  const {data: responseData} = await axios.post(route('back-office.delivery.question-scoring-submit'), {
    attempt_hash: attempt.value.hash,
    question_hash: question_hash,
    score: score,
    note: note,
    correct: is_correct,
  })

  if (responseData) {
    const index = answers.value.findIndex((data) => data.question.hash === question_hash)
    if (index >= 0) {
      answers.value[index].score = score
      answers.value[index].is_correct = is_correct
    }
    notification.add('success', 'Success', 'Score ' + score + ' saved!')
  }
}

const prevItem = async () => {
  if (selectedItem.value !== null && selectedItem.value.index !== 0) {
    selectedItem.value = options.value[selectedItem.value.index - 1];
  }
}

const nextItem = async () => {
  if (selectedItem.value !== null && selectedItem.value.index < (options.value.length - 1)) {
    selectedItem.value = options.value[selectedItem.value.index + 1];
  }
}

const backToDelivery = async () => {
  window.close();
}

const navigationClicked = async (hash, index) => {
  selectedItem.value = options.value[index]
}

const finishScore = async () => {
  const {data: responseData} = await axios.post(route('back-office.delivery.finish-scoring'), {
    attempt_hash: attempt.value.hash,
  })

  if (responseData) {
    notification.add('success', 'Success', 'Scoring finished!')
    window.close()
  }
}
</script>

<template>
  <dashboard-layout title="Manage Deliveries">
    <template #header>
      <div class="flex justify-between items-center sm:px-6 lg:px-0">
        <div>
          <span class="text-xs">Delivery Name</span>
          <h1 class="text-2xl font-semibold text-gray-900">{{ attempt.delivery.name }}</h1>
          <div class="flex gap-2">
            <span class="text-gray-500">{{ takerCode ?? '-' }}</span>
            <span v-if="attempt.taker !== undefined">{{ attempt.taker?.name }}</span>
          </div>
          <div class="text-sm">
            <span class="text-gray-500">Attempted at&nbsp;</span>
            <span>
              {{ moment(attempt.created_at).format('DD MMM YYYY') }} at
              {{ moment(attempt.created_at).format('H:mm') }}
            </span>
          </div>
        </div>
        <div class="max-w-lg block w-full sm:max-w-xs">
          <div class="flex justify-end gap-2">
            <button @click="() => backToDelivery()"
                    class="text-sm mr-8 flex justify-center items-center text-gray-600 bg-white rounded-md py-1.5 px-3 hover:bg-gray-50 border border-slate-200 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none">
              Close Scoring
            </button>
            <button @click="prevItem()" :disabled="selectedItem === null || selectedItem.index === 0"
                    class="flex justify-center items-center text-white bg-blue-600 rounded-md py-1.5 px-3 hover:bg-blue-700 border border-blue-600 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none">
              <ChevronLeftIcon aria-hidden="true" class="h-4 w-4"/>
            </button>
            <button @click="nextItem()"
                    :disabled="selectedItem === null || selectedItem.index == (options.length - 1)"
                    class="flex justify-center items-center text-white bg-blue-600 rounded-md py-1.5 px-3 hover:bg-blue-700 border border-blue-600 disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none">
              <ChevronRightIcon aria-hidden="true" class="h-4 w-4"/>
            </button>
          </div>
        </div>
      </div>
      <hr class="mt-8">
    </template>
    <template #default>
      <div v-if="questionData !== null" class="mb-10">
        <div class="flex flex-col items-center gap-3 my-5">
          <div v-for="file in questionData.attachments" v-if="questionData.attachments.length >= 1"
               class="w-full flex gap-2 max-w-xl">
            <div class="flex-auto flex justify-center items-center">
              <img :src="file.url" :alt="file.description" class="rounded-lg">
            </div>
          </div>
        </div>

        <div v-for="(question, questIndex) in questionData.questions" class="mt-5">
          <div class="pt-5">
            <Card>
              <div class="flex gap-4 mb-4 items-center">
                <div class="text-lg font-bold">
                  {{ selectedItem.index + 1 }}.{{ questIndex + 1 }}
                </div>
                <div>
                  <button class="text-sm underline" @click="(e) => showLiveInterviewQuestion(e, question.hash)">Show Live Question</button>
                </div>
                <div class="rounded-md bg-yellow-50 p-2" v-if="!isInterview && getTakerAnswer(question.hash) === ''">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <ExclamationCircleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true"/>
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-yellow-800">Candidate not answer.</h3>
                    </div>
                  </div>
                </div>
                <div class="rounded-md bg-yellow-50 p-2" v-if="isInterview && scores[question.hash] <= 0">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <ExclamationCircleIcon class="h-5 w-5 text-yellow-400" aria-hidden="true"/>
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-yellow-800">Not scored.</h3>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center mb-4 gap-3" v-if="questionData.item_type.value !== 'multiple-choice'">
                <input v-model="scores[question.hash]"
                       :disabled="((getTakerAnswer(question.hash) === '') && !isInterview)"
                       @blur="(e) => invalidateScore(e, question.hash)"
                       placeholder="Enter Score here"
                       class="max-w-full w-20 block shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md disabled:bg-gray-100 disabled:cursor-not-allowed"
                       type="number" min="0"/>

                <div class="rounded-md bg-blue-50 px-3 py-2.5" v-if="!isInterview">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <InformationCircleIcon class="h-5 w-5 text-blue-400" aria-hidden="true"/>
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-blue-800">Max Score: <span
                        class="font-bold">{{ Math.round((100 / questionData.questions.length) * 100) / 100 }}</span>
                      </h3>
                    </div>
                  </div>
                </div>

                <div class="rounded-md bg-orange-50 px-3 py-2.5">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <InformationCircleIcon class="h-5 w-5 text-orange-400" aria-hidden="true"/>
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-orange-800">Click anywhere to save.</h3>
                    </div>
                  </div>
                </div>
              </div>


              <div v-html="question.question"></div>
              <!-- Multiple Choice's answer -->
              <div class="flex flex-col gap-2 mt-6 w-auto"
                   v-if="question.type !== null && question.type.name === 'multiple-choice'">
                <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex"
                        :class="[
                          'cursor-default flex text-left px-3 py-2 rounded-md items-center',
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
                <!-- Interview's answer -->
                <div class="text-sm" v-if="isInterview">
                  <textarea
                    name="note"
                    class="rounded-lg w-full border-2 border-gray-100 p-3"
                    placeholder="Write interview notes here"
                    @blur="(e) => invalidateScore(e, question.hash)"
                    v-model="notes[question.hash]"></textarea>
                </div>
                <!-- Essay's answer -->
                <div class="bg-gray-100 rounded-lg w-full border-2 border-gray-100 p-3"
                     v-else-if="getTakerAnswer(question.hash) !== ''">
                  <div v-html="getTakerAnswer(question.hash)"></div>
                </div>
                <div class="text-sm" v-else>
                  Not answered.
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
      <ExamNavigation :items="items"
                      @click="(hash, index) => navigationClicked(hash, index)"
                      :done="scoredAnswers"
                      :enable-info="false"
                      :show-previous="selectedItem !== null && selectedItem.index > 0"
                      :show-next="selectedItem !== null && selectedItem.index < (options.length - 1)"
                      @next="nextItem"
                      @previous="prevItem"
                      extra-button="Finish & Recalculate Score"
                      @extraButtonAction="finishScore"/>
      <div class="py-8"></div>
    </template>
  </dashboard-layout>
</template>
