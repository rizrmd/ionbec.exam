<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {CheckCircleIcon, PencilAltIcon, PlusIcon, PrinterIcon, SearchIcon, XCircleIcon} from '@heroicons/vue/outline'
import {onMounted, reactive, ref, toRefs} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import JetDialogModal from '@/Jetstream/DialogModal'
import {Link, useForm} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetBanner from '@/Jetstream/Banner'
import route from "@/Libs/ziggy";

const props = defineProps({
  payload: {
    type: Object,
  },
})

const {payload} = toRefs(props)

const filters = reactive({
  code: null,
  name: null
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const form = useForm({
  code: null,
  name: null,
  description: null,
  is_random: false,
  is_interview: false,
  is_mcq: true
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  if (urlParams.modal_create && Object.keys(urlParams).length === 1) openModalForm()
})

const isModalFormOpen = ref(false)

const openModalForm = () => {
  isModalFormOpen.value = true
  form.code = null
  form.name = null
  form.description = null
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
}

const createAction = () => {
  form.post(route('back-office.test.store'), {
    onSuccess: closeModalForm
  })
}

</script>

<template>
  <dashboard-layout title="Manage Exams">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Exams</h1>
        <button
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="openModalForm">
          <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
          Exam
        </button>
      </div>
    </template>

    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex flex-row gap-4">
        <input id="search-code" v-model="filters.code" autocomplete="code"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search code"
               type="text">
        <input id="search-name" v-model="filters.name" autocomplete="name"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search name"
               type="text">
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
            <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">EXAM
                    NAME
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-right" scope="col">
                    QUESTION
                  </th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-0" scope="col">
                    <span class="sr-only">Action</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(exam, index) in payload.data" :key="`exam-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6 w-20">
                    <span>[{{ exam.code }}] {{ exam.name }}</span><br>
                    <div class="flex flex-row gap-4 mt-2">
                      <span class="flex flex-row items-center text-xs" v-if="!exam.is_interview">
                        <span v-if="exam.is_mcq" class="px-2 py-0.5 text-green-600 border border-green-600 rounded-md">MCQ</span>
                        <span v-else class="px-2 py-0.5 text-sky-600 border border-sky-600 rounded-md">Essay</span>
                      </span>
                      <span class="flex flex-row items-center text-xs" v-if="exam.is_interview">
                        <CheckCircleIcon aria-hidden="true" class="h-4 w-4 text-primary-600 mr-1"/>
                        Interview
                      </span>
                      <span class="flex flex-row items-center text-xs" v-if="!exam.is_interview">
                        <CheckCircleIcon v-if="exam.is_random" aria-hidden="true"
                                         class="h-4 w-4 text-primary-600 mr-1"/>
                        <XCircleIcon v-else aria-hidden="true" class="h-4 w-4 text-orange-600 mr-1"/>
                        Randomized
                      </span>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ exam.items_count }}
                    Questions
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <div class="flex flex-row items-center space-x-4">
                      <a :href="route('back-office.test.pdf', {exam_hash: exam.hash})" target="_blank"
                         class="text-green-600 hover:text-green-900">
                        <PrinterIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                      </a>
                      <Link
                        :href="route('back-office.test.detail', {exam_hash: exam.hash, name: 'back-office.test.index', params: 'page', values: payload.current_page})"
                        class="text-blue-600 hover:text-blue-900">
                        <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                      </Link>
                    </div>
                  </td>
                </tr>
                <tr v-if="(filters.code !== null || filters.name !== null) && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty, please create Test.</div>
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
  </dashboard-layout>

  <JetDialogModal :show="isModalFormOpen" @close="closeModalForm" items="center">
    <template #title>
      <div class="flex justify-between">
        <h4>Create New Exam</h4>
      </div>
    </template>

    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="code">
            Code <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="code" v-model="form.code" autofocus
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   name="name"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name">
            Name <small class="text-red-500">*</small>
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="name" v-model="form.name" autofocus
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   name="name"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="description">
            Description
          </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="description" v-model="form.description"
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700">
            Is Interview?
          </label>
          <div class="mt-0.5 sm:col-span-2">
            <label class="flex items-center mb-2">
              <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500"
                     :value="false"
                     v-model="form.is_interview">
            </label>
            <span class="text-xs text-gray-600">Please <strong>check</strong> this checkbox if you want to create interview questions. This can not be changed later.</span>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5"
             v-if="!form.is_interview">
          <label class="block text-sm font-medium text-gray-700">
            Is MCQ?
          </label>
          <div class="mt-0.5 sm:col-span-2">
            <label class="flex items-center mb-2">
              <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true"
                     v-model="form.is_mcq">
            </label>
            <span class="text-xs text-gray-600">Please <strong>check</strong> this checkbox if you want to create Multiple Choice Questions (MCQ) or <strong>uncheck</strong> this for Essay. This can not be changed later.</span>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5"
             v-if="!form.is_interview">
          <label class="block text-sm font-medium text-gray-700">
            Randomize
          </label>
          <div class="mt-0.5 sm:col-span-2">
            <label class="flex items-center">
              <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true"
                     v-model="form.is_random">
            </label>
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="closeModalForm">
          Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="submit"
          :disabled="form.processing"
          @click="createAction">
          Save
        </button>
      </div>
    </template>
  </JetDialogModal>
</template>

<style scoped>

</style>
