<script setup>
import AuthoringDeliveryLayout from '@/Layouts/AuthoringDeliveryLayout.vue'
import {SearchIcon, PencilAltIcon, ArrowUpIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import {Link} from "@inertiajs/inertia-vue3";
import route from "@/Libs/ziggy";
import categoryTypesColor from "@/Libs/categoryTypesColor";

const props = defineProps({
  payload: {
    type: Object,
  },
  takerCount: {
    type: Number,
  },
  scoringCount: {
    type: Number,
  },
  questionCount: {
    type: Number,
  },
  delivery: {
    type: Object,
  },
  tests: {
    type: Object,
  },
  groups: {
    type: Object,
  },
  typeOptions: {
    type: Object,
  },
})

const {payload, group, takers, delivery} = toRefs(props)

const filters = reactive({
  query: null
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
})

const strippedContent = (text) => text.replace(/(<([^>]+)>)/ig, "");

const authoringDeliveryLayout = ref();
</script>

<template>
  <AuthoringDeliveryLayout :delivery="delivery" :taker-count="takerCount" :scoring-count="scoringCount" :question-count="questionCount" :tests="tests" :groups="groups" ref="authoringDeliveryLayout">
    <template #widget>
      <div class="flex gap-2 justify-end">
        <button class="text-sm text-blue-700 px-2.5 py-1.5" @click="authoringDeliveryLayout.openModalForm()">
          Edit Assigned Test
        </button>
        <Link v-if="delivery.exam.hash !== undefined" :href="route('back-office.test.detail', {exam_hash: delivery.exam.hash, name: 'back-office.delivery.question', params: 'delivery_hash', values: delivery.hash})" class="flex justify-center items-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Show Assigned Test <ArrowUpIcon aria-hidden="true" class="ml-1.5 h-4 w-4 rotate-45"/>
        </Link>
      </div>
    </template>
    <template #default>
      <div class="flex flex-row gap-4 mt-4">
        <input id="search-question" v-model="filters.query" autocomplete="name-or-email"
               class="flex-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               placeholder="Search question"
               type="text">
        <button
          class="inline-flex justify-center items-center px-4 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="button"
          @click="search">
          <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Search
        </button>
      </div>

      <div class="my-4 shadow ring-1 ring-black ring-opacity-5 md:rounded-lg overflow-hidden">
        <div class="bg-white">
          <div class="p-4 border-b" v-for="(question, index) in payload.data" :key="`question-${index}`">
            <div class="text-sm text-gray-500 mb-1 flex justify-between">
              <div class="flex flex-wrap gap-4">
                <div>
                  {{ typeOptions[question.item.type] }}
                </div>
                <div>
                  {{ question.item.is_vignette ? 'Vignette' : '' }}
                </div>
              </div>
              <div>
                Correctness: {{ question.correctness }}%
              </div>
            </div>
            <div class="flex justify-between gap-2">
              <div class="line-clamp-2">
                {{ strippedContent(question.question) }}
              </div>
              <div class="flex items-center justify-center">
                <Link :href="route('back-office.question-set.detail', {item_hash: question.item.hash, name: 'back-office.delivery.question', params: 'page,delivery_hash,query', values: `${payload.current_page},${delivery.hash},${filters.query}`})" class="text-blue-600 hover:text-blue-800">
                  <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                </Link>
              </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-1">
              <div :class="[`inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-${categoryTypesColor(category.type)}-100 text-${categoryTypesColor(category.type)}-800`]"
                   v-for="(category, index) in question.categories" :key="index">
                {{ category.name }}
              </div>
            </div>
          </div>
        </div>
        <pagination :pagination-data="payload"/>
      </div>
    </template>
  </AuthoringDeliveryLayout>
</template>

<style scoped>

</style>
