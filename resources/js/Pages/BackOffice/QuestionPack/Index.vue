<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {PencilAltIcon, SearchIcon} from '@heroicons/vue/outline'
import CardCounter from '@/Components/CardCounter.vue'
import Pagination from '@/Components/Pagination.vue'
import {computed, onMounted, reactive, toRefs} from 'vue'
import {Inertia} from '@inertiajs/inertia'
import {Link} from "@inertiajs/inertia-vue3"
import urlParser from "@/Libs/urlParser";
import categoryTypesColor from "@/Libs/categoryTypesColor";
import route from "@/Libs/ziggy";

const props = defineProps({
  types: {
    type: Array,
  },
  payload: {
    type: Object,
  },
  tests: {
    type: Object,
  },
  stats: {
    type: Object
  },
  typeOptions: {
    type: Object,
  },
  countType: {
    type: Object,
  },
  categoryType: {
    type: Object,
  },
  categoriesValue: {
    type: Object,
  },
  categoryColors: {
    type: Object,
    default: () => ({}),
  },
})

const {payload, types, tests, categoryType} = toRefs(props)

const filters = reactive({
  question_type: null,
  query: null,
  type: null,
  categories: {},
})

const showAttemptModal = reactive({
  visible: false,
  questionHash: null,
  attempts: []
})

Object.keys(categoryType.value || {}).forEach(key => {
  filters.categories[key] = null
});

const excorcistFilter = () => {
  const category_filters = {}
  Object.keys(categoryType.value || {}).forEach(key => {
    category_filters[key] = filters.categories[key] ?? null
  })
  return {
    query: filters.query,
    type: filters.type,
    question_type: filters.question_type,
    category_filters,
  };
}

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...excorcistFilter(),
    },
  })
}

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  const categories = {}
  Object.keys(categoryType.value || {}).forEach(key => {
    const value = urlParams[`category_filters[${key}]`]
    categories[key] = value === '' || value === undefined ? null : value
  })
  filters.categories = categories
  filters.type = urlParams.type
})

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

const strippedContent = (text) => text !== null ? text.replace(/(<([^>]+)>)/ig, "") : text;

const filterType = (type) => {
  filters.type = (filters.type === type) ? null : type;
  Inertia.reload({
    data: {
      page: 1,
      ...excorcistFilter(),
    },
  })
}

const showAttemptDetails = async (questionHash) => {
  try {
    const response = await fetch(`/api/questions/${questionHash}/attempts`)
    if (response.ok) {
      const data = await response.json()
      showAttemptModal.attempts = data.attempts
      showAttemptModal.questionHash = questionHash
      showAttemptModal.visible = true
    } else {
      console.error('Failed to fetch attempt details')
    }
  } catch (error) {
    console.error('Error fetching attempt details:', error)
  }
}

const closeAttemptModal = () => {
  showAttemptModal.visible = false
  showAttemptModal.questionHash = null
  showAttemptModal.attempts = []
}
</script>

<template>
  <dashboard-layout title="Manage Question Pack">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Search Questions</h1>
        <div>

        </div>
      </div>
    </template>

    <template #default>
      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
        <card-counter :title="'Total Available'"
                      :count="stats.totalData"
                      :color="'gray-600'" />
        <card-counter :title="'Total Multiple Choices'"
                      :count="stats.totalMultipleChoices"
                      :color="'green-600'" />
        <card-counter :title="'Total Essay'"
                      :count="stats.totalEssay"
                      :color="'blue-600'" />
        <card-counter :title="'Total Shown Data'"
                      :count="stats.totalFiltered"
                      :color="'red-600'" />
      </div>

      <div class="mt-8 mb-2 flex flex-row flex-wrap gap-4">
        <template v-for="(category, key) in categoryType" :key="`type-${key}`">
          <select v-model="filters.categories[key]"
                  class="basis-4/12 grow mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            <option :value="null">Select {{ category }}</option>
            <option :value="category.hash" v-for="(category, key) in categoriesValue[key]">{{ category.name }}</option>
          </select>
        </template>
      </div>

      <div class="flex flex-row gap-4">
        <select v-model="filters.question_type"
                class="basis-4/12 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
          <option :value="null">Show all</option>
          <option :value="'multiple-choice'">Multiple Choice</option>
          <option :value="'essay'">Essay</option>
        </select>
        <input id="search-name" v-model="filters.query"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               placeholder="Search question"
               type="text">
        <button
          class="flex-none inline-flex justify-center items-center px-4 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="button"
          @click="search">
          <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Search
        </button>
      </div>

      <div class="my-4 shadow ring-1 ring-black ring-opacity-5 md:rounded-lg overflow-hidden">
        <div class="bg-white">
          <div class="p-4 border-b hover:bg-gray-50" v-for="(question, index) in payload.data" :key="`question-${index}`">
            <div class="text-sm text-gray-500 mb-1 flex justify-between">
              <div class="flex flex-wrap gap-4">
                <div v-if="question.item">
                  {{ typeOptions[question.item.type] }}
                </div>
                <div v-if="question.item && question.item.is_vignette">
                  {{ question.item.is_vignette ? 'Vignette' : '' }}
                </div>
                <div class="text-black" v-if="question.item">
                  {{ question.item.title }}
                </div>
                <div class="text-red-500" v-else>
                  No item associated
                </div>
              </div>
              <div>
                <button v-if="question.correctness > 0" @click="showAttemptDetails(question.hash)" class="bg-green-50 text-green-600 rounded-md px-2 py-1 hover:bg-green-100 transition-colors cursor-pointer">
                  Correctness: {{ question.correctness }}%
                </button>
                <button v-else-if="question.is_attempted" @click="showAttemptDetails(question.hash)" class="bg-yellow-50 text-yellow-600 rounded-md px-2 py-1 hover:bg-yellow-100 transition-colors cursor-pointer">
                  Correctness: {{ question.correctness }}%
                </button>
                <span v-else class="bg-red-50 text-red-600 rounded-md px-2 py-1">
                  No Data
                </span>
              </div>
            </div>
            <div class="flex justify-between gap-2">
              <div>
                <div class="line-clamp-2">
                  {{ strippedContent(question.question) }}
                </div>
                <div class="flex flex-wrap gap-2 mt-1">
                  <div :class="[`inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-${categoryTypesColor(category.type, categoryColors)}-100 text-${categoryTypesColor(category.type, categoryColors)}-800`]"
                       v-for="(category, index) in question.categories" :key="index">
                    {{ category.name }}
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-center">
                <Link v-if="question.item" :href="route('back-office.question-set.detail', {item_hash: question.item.hash, name: 'back-office.question-pack.index', params: 'page,query', values: `${payload.current_page},${filters.query}`})"
                      class="text-blue-600 hover:text-blue-900">
                  <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                </Link>
                <span v-else class="text-gray-400">
                  <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                </span>
              </div>
            </div>
          </div>
        </div>
        <pagination :pagination-data="payload"/>
      </div>

      <!-- Attempt Details Modal -->
      <div v-if="showAttemptModal.visible" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Attempt Details</h3>
            <button @click="closeAttemptModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="px-6 py-4">
            <div v-if="showAttemptModal.attempts.length > 0" class="space-y-4">
              <div class="grid grid-cols-4 gap-4 text-sm font-medium text-gray-500 border-b pb-2">
                <div>Date</div>
                <div>Answer</div>
                <div>Correct</div>
                <div>Score</div>
              </div>
              <div v-for="(attempt, index) in showAttemptModal.attempts" :key="index" class="grid grid-cols-4 gap-4 text-sm py-2 border-b border-gray-100">
                <div class="text-gray-900">{{ attempt.created_at ? new Date(attempt.created_at).toLocaleDateString() : 'N/A' }}</div>
                <div class="text-gray-600 truncate" :title="attempt.answer">{{ attempt.answer || 'N/A' }}</div>
                <div>
                  <span v-if="attempt.is_correct" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Correct
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Incorrect
                  </span>
                </div>
                <div class="text-gray-900">{{ attempt.score }}</div>
              </div>
              <div class="mt-4 text-sm text-gray-500">
                Total attempts: {{ showAttemptModal.attempts.length }} | 
                Correct: {{ showAttemptModal.attempts.filter(a => a.is_correct).length }} |
                Correctness: {{ Math.round((showAttemptModal.attempts.filter(a => a.is_correct).length / showAttemptModal.attempts.length) * 100) }}%
              </div>
            </div>
            <div v-else class="text-center text-gray-500 py-8">
              No attempt data available
            </div>
          </div>
        </div>
      </div>
    </template>
  </dashboard-layout>
</template>

<style scoped>

</style>
