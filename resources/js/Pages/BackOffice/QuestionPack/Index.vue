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
})

const {payload, types, tests, categoryType} = toRefs(props)

const filters = reactive({
  question_type: null,
  query: null,
  type: null,
  categories: [],
})

Object.keys(categoryType.value).forEach(key => {
  filters.categories[key] = null
});

const excorcistFilter = () => {
  return {
    query: filters.query,
    type: filters.type,
    question_type: filters.question_type,
    disease_group: filters.categories['disease-group'],
    region_group: filters.categories['region-group'],
    specific_part: filters.categories['specific-part'],
    typical_group: filters.categories['typical-group'],
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
  filters.categories = {
    'disease-group': urlParams.disease_group === '' || urlParams.disease_group === undefined ? null : urlParams.disease_group,
    'region-group': urlParams.region_group === '' || urlParams.region_group === undefined ? null : urlParams.region_group,
    'specific-part': urlParams.specific_part === '' || urlParams.specific_part === undefined ? null : urlParams.specific_part,
    'typical-group': urlParams.typical_group === '' || urlParams.typical_group === undefined ? null : urlParams.typical_group,
  }
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

      <div class="mt-8 mb-2 flex flex-row gap-4">
        <template v-for="(category, key) in categoryType" :key="`type-${key}`">
          <select v-model="filters.categories[key]"
                  class="basis-4/12 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
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
                <div>
                  {{ typeOptions[question.item.type] }}
                </div>
                <div v-if="question.item.is_vignette">
                  {{ question.item.is_vignette ? 'Vignette' : '' }}
                </div>
                <div class="text-black">
                  {{ question.item.title }}
                </div>
              </div>
              <div>
                <span v-if="question.correctness > 0" class="bg-green-50 text-green-600 rounded-md px-2 py-1">
                  Correctness: {{ question.correctness }}%
                </span>
                <span v-else-if="question.is_attempted" class="bg-yellow-50 text-yellow-600 rounded-md px-2 py-1">
                  Correctness: {{ question.correctness }}%
                </span>
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
                  <div :class="[`inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-${categoryTypesColor(category.type)}-100 text-${categoryTypesColor(category.type)}-800`]"
                       v-for="(category, index) in question.categories" :key="index">
                    {{ category.name }}
                  </div>
                </div>
              </div>
              <div class="flex items-center justify-center">
                <Link :href="route('back-office.question-set.detail', {item_hash: question.item.hash, name: 'back-office.question-pack.index', params: 'page,query', values: `${payload.current_page},${filters.query}`})"
                      class="text-blue-600 hover:text-blue-900">
                  <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                </Link>
              </div>
            </div>
          </div>
        </div>
        <pagination :pagination-data="payload"/>
      </div>
    </template>
  </dashboard-layout>
</template>

<style scoped>

</style>
