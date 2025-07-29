<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {ChevronLeftIcon, TrashIcon, TrendingUpIcon, PlusIcon, XCircleIcon, CheckIcon, ChevronUpIcon, ChevronDownIcon, PhotographIcon} from '@heroicons/vue/outline'
import {toRefs, reactive, computed, ref, onMounted, watch} from "vue";
import route from "@/Libs/ziggy";
import {Link, useForm} from '@inertiajs/inertia-vue3'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import MultipleSelect from "@/Components/MultipleSelect";
import draggable from 'vuedraggable'
import Editor from "@/Components/Editor";
import BarChart from "@/Components/BarChart";
import urlParser from "@/Libs/urlParser";
import generatePrevUrl from "@/Libs/generatePrevUrl";
import Message from "@/Components/Message";

const props = defineProps({
  item: {
    type: Object,
    required: false,
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
    type: Object,
    required: false,
  },
  chartData: {
    type: Object,
    required: false,
  },
})

const {item, tests, categories, questions, chartData, typeOptions} = toRefs(props);

const showModalDelete = ref(false)
const openModalDelete = () => showModalDelete.value = true;
const cancelDeleteAction = () => showModalDelete.value = false
const confirmDeleteAction = () => formContent.delete(route('back-office.question-set.destroy', {item_hash: item.value.hash}))

const filteredTests = computed(() => {
  let exams = [];
  tests.value.forEach((test) => {
    exams.push({
      text: test.name,
      value: test.hash
    })
  })
  return exams;
})

const formContent = useForm({
  hash: null,
  type: Object.keys(typeOptions.value)[0],
  title: "",
  vignette_case: null,
  is_vignette: false,
  is_random: false,
  tests: [],
  questions: [],
  deleted_questions: [],
  deleted_answers: [],
})

if (item.value !== undefined) {
  formContent.hash = item.value.hash;
  formContent.type = item.value.type;
  formContent.title = item.value.title;
  formContent.vignette_case = item.value.content;
  formContent.is_vignette = item.value.is_vignette;
  formContent.is_random = item.value.is_random;
  formContent.tests = item.value.exams.map(val => val['hash']);
}

const questionIdCount = ref(0)
const itemQuestions = ref([]);
const dragging = ref(false);

const baseAnswer = {
  hash: null,
  answer:'',
  is_correct_answer: false,
}

const addQuestion = (question = null) => {
  const availableCategories = ['disease-group', 'region-group', 'specific-part', 'typical-group'];
  const setDefaultValCategory = (type) => categories.value[type]['options'].length >= 1 ? categories.value[type]['options'][0].hash : null;
  let hash = null, questValue = null, isRandom = false, answers = [], catHash = [];
  availableCategories.forEach((cat) => catHash[cat] = setDefaultValCategory(cat))

  if (question !== null) {
    question.answers.forEach((answer) => {
      answers.push({
        hash: answer.hash,
        answer: answer.answer,
        is_correct_answer: answer.is_correct_answer
      })
    })
    hash = question.hash
    isRandom = question.is_random
    questValue = question.question
    availableCategories.forEach((cat) => {
      let category = question.categories.find((category) => category.type === cat);
      catHash[cat] = (category !== undefined) ? category.hash : null;
    })
  } else {
    answers.push({...baseAnswer})
  }

  itemQuestions.value.push({
    id: questionIdCount.value,
    hash: hash,
    disease_group: question !== null ? catHash['disease-group'] : null,
    region_group: question !== null ? catHash['region-group'] : null,
    specific_part: question !== null ? catHash['specific-part'] : null,
    typical_group: question !== null ? catHash['typical-group'] : null,
    question: questValue,
    is_random: isRandom,
    show_answer: true,
    answers: answers,
  });
  questionIdCount.value = questionIdCount.value + 1;
}

const removeQuestion = (id) => {
  const question = itemQuestions.value.find((question) => question.id === id);
  if (question.hash !== null) formContent.deleted_questions.push(question.hash);
  itemQuestions.value = itemQuestions.value.filter((question) => question.id !== id)
}

const showAnswer = (index) => itemQuestions.value[index].show_answer = !itemQuestions.value[index].show_answer
const addAnswer = (index, answerIndex) => itemQuestions.value[index].answers.splice(answerIndex + 1, 0, {...baseAnswer})
const removeAnswer = (questionIndex, answerIndex) => {
  const answers = itemQuestions.value[questionIndex].answers
  const hash = answers[answerIndex].hash
  if (hash !== null) formContent.deleted_answers.push(hash)
  if (answers.length > 1) answers.splice(answerIndex, 1)
}
const makeCorrectAnswer = (questionIndex, answerIndex) => {
  const answers = itemQuestions.value[questionIndex].answers;
  answers.forEach((answer, index) => answers[index].is_correct_answer = answerIndex === index)
}

const updateContent = () => {
  formContent.questions = itemQuestions.value;
  formContent.post(route('back-office.question-set.create-or-update'), {
    onSuccess: () => {
      formContent.deleted_answers = [];
      formContent.deleted_questions = [];
    }
  })
}

const prevUrl = ref(null);
onMounted(() => {
  const urlParams = urlParser()
  prevUrl.value = generatePrevUrl(urlParams.name, urlParams.params, urlParams.values)

  if (questions.value !== undefined) {
    questions.value.forEach((question) => addQuestion(question))
  }
})

const myFile = ref(null);
const formFile = useForm({
  image: null,
})
const fileChanged = () => {
  if (myFile.value.files.length >= 1) {
    formFile.image = myFile.value.files[0];
    formFile.post(route('back-office.question-set.attach', {item_hash: item.value.hash}));
  }
}

const formDeleteFile = useForm({
  id: null,
})
const deleteFile = (id) => {
  formDeleteFile.id = id
  formDeleteFile.post(route('back-office.question-set.delete-attach', {item_hash: item.value.hash}));
}

const showCharts = ref(false);

const toggleChart = () => {
  showCharts.value = !showCharts.value;
}

const getNameTest = (hash) => {
  const test = tests.value.find((test) => test.hash === hash);
  return (test) ? test.name : null;
}


const modalNonVignette = ref(false)
const cancelNonVignette = () => {
  formContent.is_vignette = true;
  modalNonVignette.value = false;
}
const confirmNonVignette = () => {
  const cloneQuests = [...itemQuestions.value]
  if (cloneQuests.length > 1) {
    cloneQuests.reverse().forEach((quest, index) => {
      if (cloneQuests.length -1 !== index) removeQuestion(quest.id)
    })
    modalNonVignette.value = false;
  }
}

const checkVignette = () => {
  if (!formContent.is_vignette && formContent.is_random) {
    // a vignette shouldn't randomized.
    formContent.is_random = false
  }

  if (formContent.is_vignette && itemQuestions.value.length > 1) {
    modalNonVignette.value = true;
  }
}

const typeChanged = (e) => {
  if (e.target.value === 'interview') {
    formContent.is_vignette = true;
  }
}
</script>

<template>
  <DashboardLayout title="Authoring Question Set">
    <template #default>
      <JetActionMessage :on="formContent.recentlySuccessful || formFile.recentlySuccessful || formDeleteFile.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>

      <div class="flex justify-between sm:px-6 lg:px-0 mb-5 gap-2">
        <div class="flex">
          <div class="flex justify-center items-center mr-4">
            <Link :href="prevUrl ?? route('back-office.question-set.index')" class="hover:bg-gray-200 px-3 py-2 rounded-sm">
              <ChevronLeftIcon aria-hidden="true" class="h-5 w-5"/>
            </Link>
          </div>
          <div>
            <h1 class="text-xl font-semibold text-gray-900 line-clamp-1 mt-1">{{ formContent.title }}</h1>
            <h2 class="text-md text-gray-700">{{ typeOptions[formContent.type] }}{{ formContent.is_vignette ? ', Vignette' : '' }} </h2>
            <div class="flex flex-wrap gap-2 mt-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-indigo-100 text-indigo-800" v-for="(hash, index) in formContent.tests" :key="index">
                {{ getNameTest(hash) }}
              </span>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button
            v-if="chartData !== undefined && chartData.length > 0"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            type="button"
            @click="toggleChart()">
            Show Stats
          </button>
          <span
            v-if="chartData === undefined || chartData.length === 0"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-yellow-600 bg-yellow-50">
            No data on this question sets, yet.
          </span>
          <button
            v-if="item !== undefined"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            type="button"
            @click="openModalDelete">
            <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
        </div>
      </div>

      <div class="bg-white pt-4 pl-1 pr-3 pb-0 rounded-lg" v-if="showCharts && chartData !== undefined">
        <div v-for="chart in chartData">
          <div class="text-center text-xl font-bold">{{ chart.exam.name }}</div>
          <template v-if="chart.countDelivery >= 1">
            <BarChart :labels="chart.data.labels" :series="chart.data.correctness"/>
          </template>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 sm:gap-5 mt-4">
        <div class="w-full sm:w-5/12 xl:w-4/12">
          <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white p-3">

            <div class="pb-4 mb-4 border-b border-gray-200 space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Type <small class="text-red-500">*</small>
                </label>
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                  <select v-model="formContent.type"
                          @change="typeChanged"
                          class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none" :disabled="item !== undefined">
                    <option :value="option" v-for="(name, option) in typeOptions">
                      {{ name }}
                    </option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="title"> Code / Title <small class="text-red-500">*</small></label>
                <input id="title" v-model="formContent.title" autocomplete="title" autofocus
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>

              <div>
                <label class="inline-flex items-center">
                  <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true" v-model="formContent.is_vignette" @click="checkVignette">
                  <span class="ml-2">
                    Vignette
                  </span>
                </label>
              </div>

              <div v-if="!formContent.is_vignette">
                <label class="inline-flex items-center">
                  <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true" v-model="formContent.is_random">
                  <span class="ml-2">
                    Randomize
                  </span>
                </label>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="group">
                  Choose exam to attach
                </label>
                <div class="mt-1 sm:mt-0 sm:col-span-2">
                  <div class="w-full">
                    <MultipleSelect title="Tests" :required="false" :options="filteredTests" pills-size="small" v-model="formContent.tests" position="top" />
                  </div>
                </div>
              </div>
            </div>

            <Message v-if="item != null && item.attachments.length === 0" class="my-2" message="Click button below to add an Image."/>
            <button class="w-full items-center px-4 py-2 border border-transparent border-primary-600 shadow-sm text-sm font-medium rounded-md text-primary-600 bg-white hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mb-2" @click="myFile.click()" v-if="item !== undefined">
              Upload an Image
            </button>
            <input type="file" accept="image/*" :ref="el => { myFile = el }" style="display: none;" @change="fileChanged" />

            <div class="text-gray-500 mt-2" v-if="item != null">
              <span v-if="itemQuestions.length > 0">{{ itemQuestions.length }} questions added.</span>
              <span v-else>No questions added, yet.</span>
            </div>
            <div class="flex flex-col gap-2" v-if="item != null">
              <draggable
                :list="itemQuestions"
                :disabled="!formContent.is_vignette"
                item-key="name"
                class="list-group"
                ghost-class="ghost"
                @start="dragging = true"
                @end="dragging = false"
              >
                <template #item="{ element }">
                  <div class="flex my-1.5">
                    <div class="flex-auto w-full bg-gray-200 py-2 px-4 rounded-md hover:cursor-move" :class="{ 'not-draggable': false }">
                      {{ typeOptions[formContent.type] }} #{{ element.id + 1 }}
                    </div>
                    <div class="flex-none flex items-center">
                      <button class="text-red-500 ml-2 hover:scale-125" @click="removeQuestion(element.id)">
                        <TrashIcon aria-hidden="true" class="h-5 w-5"/>
                      </button>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>

            <Message v-if="item != null && itemQuestions.length === 0" class="my-2" message="Click button below to add questions."/>
            <button class="w-full flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 my-2" @click="addQuestion(null)"
                    v-if="item != null && (itemQuestions.length === 0 || formContent.is_vignette)">
              Add a Question
            </button>

            <Message v-if="item == null" message="Save first before adding image & questions."/>
          </div>
          <div class="h-1 bg-gray-200 my-4"></div>
          <button class="w-full flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 my-2" @click="updateContent">
            Save Changes
          </button>
        </div>

        <div class="w-full sm:w-7/12 xl:w-8/12" v-if="item != null">
          <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white p-3 flex flex-col gap-4 mb-4" v-if="item.attachments.length > 0">
            <div v-for="file in item.attachments" v-if="item !== undefined && item.attachments.length >= 1" class="w-full flex gap-2">
              <div class="relative">
                <img :src="file.url" :alt="file.description" class="rounded-lg border-2 border-gray-100">
                <button class="bg-red-50 text-red-500 hover:bg-red-100 px-2 py-2 rounded-md absolute bottom-4 right-4" @click="deleteFile(file.id)">
                  <TrashIcon aria-hidden="true" class="h-7 w-7"/>
                </button>
              </div>
            </div>
            <div v-else class="w-full flex">
              <div class="flex-auto flex justify-center items-center">
                <span class="h-36 flex justify-center items-center">
                  <PhotographIcon aria-hidden="true" class="h-10 w-10"/>
                </span>
              </div>
            </div>
          </div>

          <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white p-3 mb-4" v-if="formContent.is_vignette">
            <div class="font-bold flex-auto flex items-center text-xl">
              Vignette Case
            </div>
            <Message class="mt-2" message="Vignette question required to have a vignette case."/>
            <Editor class="my-4" v-model="formContent.vignette_case" />
          </div>

          <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white p-3 my-4" v-for="(question, index) in itemQuestions">
            <div class="flex mb-4">
              <div class="font-bold flex-auto flex items-center text-xl">
                {{ typeOptions[formContent.type] }} #{{ question.id + 1 }}
              </div>
              <button
                class="flex-none items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                type="button"
                @click="removeQuestion(question.id)">
                <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
              </button>
            </div>

            <div class="flex gap-2">
              <div class="w-4/12">
                <label class="block text-sm font-medium text-gray-700 mb-1" :for="`disease-group-${index}`">{{ categories['disease-group'].name }}</label>
                <select
                  v-model="question.disease_group"
                  :id="`disease-group-${index}`"
                  class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-text-sm border-gray-300 rounded-md">
                  <option :value="null">Select {{ categories['disease-group'].name }}</option>
                  <option :value="option.hash" v-for="(option, index) in categories['disease-group'].options" :key="index">{{ option.name }}</option>
                </select>
              </div>
              <div class="w-4/12">
                <label class="block text-sm font-medium text-gray-700 mb-1" :for="`region-group-${index}`">{{ categories['region-group'].name }}</label>
                <select
                  v-model="question.region_group"
                  :id="`region-group-${index}`"
                  class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-text-sm border-gray-300 rounded-md">
                  <option :value="null">Select {{ categories['region-group'].name }}</option>
                  <option :value="option.hash" v-for="(option, index) in categories['region-group'].options" :key="index">{{ option.name }}</option>
                </select>
              </div>
              <div class="w-4/12">
                <label class="block text-sm font-medium text-gray-700 mb-1" :for="`typical-group-${index}`">{{ categories['typical-group'].name }}</label>
                <select
                  v-model="question.typical_group"
                  :id="`typical-group-${index}`"
                  class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md">
                  <option :value="null">Select {{ categories['typical-group'].name }}</option>
                  <option :value="option.hash" v-for="(option, index) in categories['typical-group'].options" :key="index">{{ option.name }}</option>
                </select>
              </div>
              <div class="w-4/12">
                <label class="block text-sm font-medium text-gray-700 mb-1" :for="`specific-part-${index}`">{{ categories['specific-part'].name }}</label>
                <select
                  v-model="question.specific_part"
                  :id="`specific-part-${index}`"
                  class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md">
                  <option :value="null">Select {{ categories['specific-part'].name }}</option>
                  <option :value="option.hash" v-for="(option, index) in categories['specific-part'].options" :key="index">{{ option.name }}</option>
                </select>
              </div>
            </div>

            <Editor class="my-2" v-model="question.question"/>

            <template v-if="formContent.type === 'multiple-choice'">
              <div class="flex justify-center">
                <label class="flex items-center flex-auto">
                  <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true" v-model="question.is_random">
                  <span class="ml-2">Randomize Answers</span>
                </label>
                <button :class="['px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 my-2 flex gap-3', question.show_answer ? 'text-black bg-gray-200 hover:bg-gray-300 focus:ring-gray-500' : 'text-white bg-primary-600 hover:bg-primary-700 focus:ring-primary-500']" @click="showAnswer(index)">
                  <span>{{ question.show_answer ? 'Hide' : 'Show' }} Answers</span>
                  <ChevronUpIcon aria-hidden="true" class="-mx-1 h-5 w-5" v-if="question.show_answer"/>
                  <ChevronDownIcon aria-hidden="true" class="-mx-1 h-5 w-5" v-if="!question.show_answer"/>
                </button>
              </div>

              <div v-if="question.show_answer">
                <div class="pl-5 border-l-4 border-l-primary-500 py-2 my-2 flex flex-row" v-for="(answer, answerIndex) in question.answers">
                  <Editor v-model="answer.answer" mode="simple" class="flex-1"/>
                  <button
                    :class="['px-3 py-2 mx-2 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2', answer.is_correct_answer ? 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'text-black bg-gray-200 hover:bg-gray-300 focus:ring-gray-500']"
                    type="button"
                    @click="makeCorrectAnswer(index, answerIndex)">
                    Mark Correct
                  </button>
                  <button
                    class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    type="button"
                    @click="removeAnswer(index, answerIndex)">
                    <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                  </button>
                </div>
                <button
                  class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  type="button"
                  @click="addAnswer(index, question.answers.length - 1)"
                >
                  <PlusIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>

      <JetConfirmationModal :show="showModalDelete" items="center">
        <template #title>
          Delete Question Set: <b>{{ formContent.title }}</b>
        </template>

        <template #content>
          Question Set cannot be recovered after deletion.
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              type="button"
              @click="cancelDeleteAction">
              Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              type="submit"
              :disabled="formContent.processing"
              @click="confirmDeleteAction">
              Yes, Question Set
            </button>
          </div>
        </template>
      </JetConfirmationModal>

      <JetConfirmationModal :show="modalNonVignette" items="center">
        <template #title>
          Change Item to Non Vignette?
        </template>

        <template #content>
          This action will delete some questions and leave only one question
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              type="button"
              @click="cancelNonVignette">
              Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              type="submit"
              @click="confirmNonVignette">
              Yes, Change to Non Vignette
            </button>
          </div>
        </template>
      </JetConfirmationModal>
    </template>
  </DashboardLayout>
</template>
