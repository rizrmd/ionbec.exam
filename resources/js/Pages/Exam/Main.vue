<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {XIcon} from '@heroicons/vue/outline'
import {ref, toRefs, onMounted, computed} from "vue";
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
})

const {exam, examItems, attempt, delivery, attemptQuestions, admin} = toRefs(props);

const loadingQuestion = ref(false);
const vignetteData = ref(null);
const questionData = ref(null);
const items = computed(() => {
  examItems.value.map((item) => {
    const questions = item.is_random ? shuffleArray(item.questions) : item.questions

    questions.map((question) => {
      return {
        ...question,
        answers: question.is_random ? shuffleArray(question.answers) : question.answers
      }
    })

    return {
      ...item,
      questions: questions,
    }
  })

  return exam.value.is_random ? shuffleArray(examItems.value) : examItems.value
})

const answerVal = ref([]);
const laters = ref([]);

const submitAnswer = async (partial = false) => {
  if (Object.keys(answerVal.value).length >= 1) {
    let newAnswers = {
      ...answerVal.value
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
      if (!checkDoneQuest(key)) doneQuests.value.push(key)
    })

    if (Object.keys(newAnswers).length >= 1) {
      if (attempt.value) {
        const {data: responseData} = await axios.post(route('exam.answer'), {
          attempt_hash: attempt.value.hash,
          answers_value: newAnswers
        })

        if (responseData) {
          notification.add('success', 'Success', 'Answer saved.')
        }
        if (!partial) answerVal.value = []
      }
    }
  }
}

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
const pageNumber = ref(1);
const skippedQuests = ref([]);
const laterQuests = ref([]);
const doneQuests = ref([]);

const getQuestions = async (index) => {
  const item = items.value[index];
  if (questionData.value !== null && questionData.value.item_hash === item.hash) return null;
  await submitAnswer()

  answerVal.value = [];

  vignetteData.value = item.is_vignette ? item.content : null
  questionData.value = {
    index: index,
    item_hash: item.hash,
    type:  item.item_type,
    is_random: item.is_random,
    questions: item.questions,
    attachments: item.attachments,
  }

  item.questions.forEach((question) => {
    if (!checkSkippedQuest(question.hash)) skippedQuests.value.push(question.hash)
  })

  loadingQuestion.value = true;
  axios.get(route('exam.get-taker-answer', { item_hash: item.hash }))
    .then((res) => {
      if (res.data) {
        loadingQuestion.value = false;
        const attemptAnswer = res.data.questions;
        attemptAnswer.forEach((question) => answerVal.value[question.hash] = (item.item_type.value === 'multiple-choice') ? question.pivot.answer_hash : question.pivot.answer);
      } else {
        loadingQuestion.value = false;
        pageNumber.value = Math.ceil((index + 1) / 20)
      }
    })
    .catch((err) => {
      loadingQuestion.value = false;
      console.log(err)
    })

  localStorage.setItem('exam-state', JSON.stringify({
    skipped: skippedQuests.value,
    later: laterQuests.value,
    done: doneQuests.value,
  }))
}
const checkSkippedQuest = (hash) => skippedQuests.value.indexOf(hash) !== -1;
const checkDoneQuest = (hash) => doneQuests.value.indexOf(hash) !== -1;
const selectAnswer = function (answerHash, questionHash) {
  answerVal.value[questionHash] = answerHash
  submitAnswer(true)
};

const timerCount = ref("00:00");
const startTimer = (duration) => {
  let timer = duration, minutes, seconds;
  if (!admin.value) {
    setInterval(function () {
      minutes = parseInt(timer / 60, 10);
      seconds = parseInt(timer % 60, 10);

      minutes = minutes < 10 ? "0" + minutes : minutes;
      seconds = seconds < 10 ? "0" + seconds : seconds;

      timerCount.value = minutes + ":" + seconds;

      if (--timer < 0) {
        timer = duration;
        localStorage.removeItem('exam-state')
        Inertia.visit(route('exam.finished'))
      }

    }, 1000);
  }
}

onMounted(() => {
  let todayDatetime = moment(new Date()).tz(moment.tz.guess());
  if (delivery.value.automatic_start) {
    if (moment(delivery.value.scheduled_at).isAfter(new Date())) {
      window.location.href = '/exam/waiting-room';
      return
    }
    let endedAt = moment(delivery.value.scheduled_at+"+07:00").add(delivery.value.duration, 'minutes')
    let duration = moment.duration(endedAt.diff(todayDatetime));
    startTimer(Math.round(duration.asSeconds()))
  } else {
    if (attempt.value) {
      let endedAt = moment(attempt.value.created_at+"+07:00").add(delivery.value.duration, 'minutes')
      let duration = moment.duration(endedAt.diff(todayDatetime));
      startTimer(Math.round(duration.asSeconds()))
    } else {
      window.location.href = '/exam/waiting-room';
      return
    }
  }
  const examState = JSON.parse(localStorage.getItem('exam-state'));
  skippedQuests.value = examState?.skipped ?? []
  laterQuests.value = examState?.later ?? []
  doneQuests.value = examState?.done ?? []
  getQuestions(0)
})

items.value.forEach((item) => {
  item.questions.forEach((question) => {
    if (attemptQuestions.value && attemptQuestions.value.find((data) => data.question.hash === question.hash)) {
      doneQuests.value.push(question.hash)
    }
  })
})

const modalFinish = ref(false)

const navigationClicked = (hash, index) => getQuestions(index)

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
const zoomImage = (url, alt) => {
  modalImage.value = true;
  modalImageContent.value = {url, alt};
}

const markAsLater = (e, hash) => {
  if (e.target.checked) {
    if (laterQuests.value.indexOf(hash) === -1) {
      laterQuests.value.push(hash)
    }
  } else {
    const index = laterQuests.value.findIndex((q) => q === hash);
    if (index >= 0) {
      laterQuests.value.splice(index, 1);
    }
  }
}
</script>

<template>
  <ExamLayout :title="delivery.name" :taker="taker" :timer="timerCount">
    <div class="flex gap-10">
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
                {{ questionData.index + 1 }}.{{ questionIndex + 1 }}
              </div>
              <div class="flex-1"></div>
              <div class="sm:col-span-2 flex items-center gap-2">
                <input :id="'mark-as-later-checkbox'+question.hash" type="checkbox" class="form-checkbox rounded text-red-500 focus:ring-red-500" v-model="laters[question.hash]" @change="markAsLater($event, question.hash)">
                <label class="flex flex-col justify-start" :for="'mark-as-later-checkbox'+question.hash">
                  <span>Mark As Later</span>
                </label>
              </div>
            </div>
            <Card>
              <div v-html="question.question"></div>
              <div v-if="!loadingQuestion">
                <div class="flex flex-col gap-2 mt-6 w-auto" v-if="question.type !== null && question.type?.name === 'multiple-choice'">
                  <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" @click="selectAnswer(answer.hash, question.hash)" :class="['flex text-left px-3 py-2 bg-gray-100 rounded-md', answerVal[question.hash] === answer.hash ? 'bg-green-600 text-white' : 'hover:bg-green-200 hover:text-green-600']">
                    <span class="mr-3 font-bold uppercase">{{ answerIndex[ansIndex] }}</span> <span v-html="answer.answer"></span>
                  </button>
                </div>
                <div class="mt-4" v-else>
                  <Editor class="my-2" v-model="answerVal[question.hash]" @blur="submitAnswer(true)" />
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
</template>

<style scoped>

</style>
