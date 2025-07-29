<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {PlusIcon, SearchIcon, CheckCircleIcon, XCircleIcon, PencilAltIcon} from '@heroicons/vue/outline'
import CardCounter from '@/Components/CardCounter.vue'
import Pagination from '@/Components/Pagination.vue'
import {computed, onMounted, reactive, toRefs} from 'vue'
import {Inertia} from '@inertiajs/inertia'
import {useForm, Link} from "@inertiajs/inertia-vue3"
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import urlParser from "@/Libs/urlParser";
import route from "@/Libs/ziggy";

const props = defineProps({
  types: {
    type: Array,
  },
  payload: {
    type: Object,
  },
  countVignette: {
    type: Number,
  },
  countNonVignette: {
    type: Number,
  },
  countMultipleChoice: {
    type: Number,
  },
  countEssay: {
    type: Number,
  },
  tests: {
    type: Object,
  },
  typeOptions: {
    type: Object,
  }
})

const {payload, types, tests} = toRefs(props)

const filters = reactive({
  query: null,
  type: null,
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const statusColors = (status) => {
  const map = {
    'Essay': {
      bg: 'bg-purple-100',
      text: 'text-purple-800'
    },
    'Multiple Choice': {
      bg: 'bg-blue-100',
      text: 'text-blue-800'
    },
    'Simple': {
      bg: 'bg-yellow-100',
      text: 'text-yellow-800'
    }
  }

  return map[status] ?? {
    bg: 'bg-gray-100',
    text: 'text-gray-800'
  }
}

const form = useForm({
  type: null,
  title: null,
  is_vignette: false,
  is_random: false,
  tests: [],
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  filters.type = urlParams.type || null
  filters.is_vignette = urlParams.is_vignette || null
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
</script>

<template>
  <DashboardLayout title="Manage Question Sets">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Question Sets</h1>
        <Link
          :href="route('back-office.question-set.create')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Question Set
        </Link>
      </div>
    </template>

    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
        <card-counter title="Total Multiple-Choice" color="blue-100" :count="countMultipleChoice"/>
        <card-counter title="Total Essay" color="purple-100" :count="countEssay"/>
        <card-counter title="Total Vignette" color="green-100" :count="countVignette"/>
        <card-counter title="Total Non-Vignette" color="red-100" :count="countNonVignette"/>
      </div>
      <div class="mt-8 flex flex-row gap-4">
        <input id="search-name" v-model="filters.query"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="name"
               placeholder="Search Title"
               type="text">
        <select id="type" v-model="filters.type"
                class="basis-4/12 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                name="type">
          <option :value="null">All</option>
          <option :value="option" v-for="(name, option) in typeOptions">
            {{ name }}
          </option>
        </select>
        <select id="type" v-model="filters.is_vignette"
                class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                name="type">
          <option :value="null">Show All</option>
          <option value="true">Vignette</option>
          <option value="false">Non Vignette</option>
        </select>
        <button
          class="flex-none inline-flex justify-center items-center px-4 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="button"
          @click="search">
          <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Search
        </button>
      </div>

      <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-20" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">TITLE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">QUESTIONS</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">TYPE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">VIGNETTE</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(item, index) in payload.data" :key="`item-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 truncate max-w-sm">{{ item.title }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ item.questions.length }} Questions
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-500">
                    <div class="flex items-center justify-center">
                      <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusColors(item.item_type.name).bg, statusColors(item.item_type.name).text]">
                        {{ item.item_type.name }}
                      </span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <div class="flex items-center justify-center">
                      <CheckCircleIcon v-if="item.is_vignette" aria-hidden="true" class="-mx-1 h-5 w-5 text-primary-600"/>
                      <XCircleIcon v-else aria-hidden="true" class="-mx-1 h-5 w-5 text-orange-600"/>
                    </div>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end">
                    <Link :href="route('back-office.question-set.detail', {item_hash: item.hash, name: 'back-office.question-set.index', params: 'page', values: payload.current_page})" class="text-blue-600 hover:text-blue-900">
                      <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5 cursor-pointer"/>
                    </Link>
                  </td>
                </tr>
                <tr v-if="(filters.query !== null || filters.type !== null || filters.is_vignette !== null) && payload.data.length === 0">
                  <td colspan="6">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="6">
                    <div class="text-center w-full my-4">Empty, please create Question Set.</div>
                  </td>
                </tr>
                </tbody>
              </table>
              <pagination :pagination-data="payload"/>
            </div>
          </div>
        </div>
      </div>
    </template>
  </DashboardLayout>
</template>

<style scoped>

</style>
