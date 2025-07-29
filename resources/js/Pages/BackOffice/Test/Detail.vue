<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {ChevronLeftIcon, TrashIcon, PencilAltIcon, ClipboardListIcon, SearchIcon, CheckIcon, XIcon, PrinterIcon, ExternalLinkIcon, CheckCircleIcon, XCircleIcon} from '@heroicons/vue/outline'
import {toRefs, reactive, onMounted, computed, watch} from "vue";
import route from "@/Libs/ziggy";
import {Link, useForm} from '@inertiajs/inertia-vue3'
import {ref} from "vue";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import {Inertia} from "@inertiajs/inertia";
import draggable from 'vuedraggable'
import Select from "@/Components/Select";
import urlParser from "@/Libs/urlParser";
import generatePrevUrl from "@/Libs/generatePrevUrl";
import CardCounter from "@/Components/CardCounter";
import Message from "@/Components/Message";

const props = defineProps({
  exam: {
    type: Object,
  },
  totalSets: {
    type: Number,
  },
  totalVignette: {
    type: Number,
  },
  totalMultiple: {
    type: Number,
  },
  totalQuestions: {
    type: Number,
  },
  items: {
    type: Object
  },
  allItems: {
    type: Object
  }
})

const {exam, items, allItems} = toRefs(props);

const filters = reactive({
  query: null,
})

const search = () => {
  Inertia.reload({
    data: {
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
  is_mcq: true,
})

const isModalFormOpen = ref(false)
const openModalForm = () => {
  isModalFormOpen.value = true
  form.code = exam.value.code
  form.name = exam.value.name
  form.description = exam.value.description
  form.is_random = exam.value.is_random
  form.is_interview = exam.value.is_interview
  form.is_mcq = exam.value.is_mcq
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
}

const createAction = () => {
  form.put(route('back-office.test.update', {exam_hash: exam.value.hash}), {
    onSuccess: closeModalForm
  })
}

const showModalDelete = ref(false)
const openModalDelete = () => showModalDelete.value = true;
const cancelDeleteAction = () => showModalDelete.value = false
const confirmDeleteAction = () => form.delete(route('back-office.test.destroy', {exam_hash: exam.value.hash}))

const dragging = ref(false);

const selectedItem = ref(null);
const itemsOption = computed(() => {
  let data = [];
  allItems.value.forEach((item) => {
    data.push({
      text: item.title,
      value: item.hash
    })
  })
  return data;
});

const prevUrl = ref(null);
onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  prevUrl.value = generatePrevUrl(urlParams.name, urlParams.params, urlParams.values)
})

const formItem = useForm({ hash: null })
watch(selectedItem, (item, prevItem) => {
  if (item) {
    formItem.hash = item.value
    formItem.post(route('back-office.test.add-question-set', {exam_hash: exam.value.hash}), {
      onSuccess: () => selectedItem.value = null
    })
  }
});
const removeQuestion = (hash) => {
  formItem.hash = hash
  formItem.post(route('back-office.test.remove-question-set', {exam_hash: exam.value.hash}))
}

const formOrder = useForm({
  hashes: []
})

const saveOrder = () => {
  formOrder.hashes = items.value.map((item) => item.hash)
  formOrder.post(route('back-office.test.reorder', {exam_hash: exam.value.hash}))
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
</script>

<template>
  <DashboardLayout title="Authoring Exam">
    <template #default>
      <JetActionMessage :on="formItem.recentlySuccessful || formOrder.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex justify-between sm:px-6 lg:px-0 mb-5">
        <div class="flex">
          <div class="flex justify-center items-center mr-4">
            <Link :href="prevUrl ?? route('back-office.test.index')" class="hover:bg-gray-200 px-3 py-2 rounded-sm">
              <ChevronLeftIcon aria-hidden="true" class="h-5 w-5"/>
            </Link>
          </div>
          <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ exam.code }}</h1>
            <h2 class="text-base text-gray-700">{{ exam.name ?? '-' }}</h2>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <a
            :href="route('back-office.test.pdf', {exam_hash: exam.hash})"
            target="_blank"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
            <PrinterIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </a>
          <button
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
            type="button"
            @click="openModalForm">
            <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
          <button
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            type="button"
            @click="openModalDelete">
            <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 sm:gap-5 mt-4">
        <div class="w-full sm:w-5/12 xl:w-4/12">
          <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white p-3">
            <div class="text-gray-500">
              <span v-if="items.length > 0">{{ items.length }} question sets added.</span>
              <span v-else>No question sets added, yet.</span>
            </div>
            <Message v-if="items.length === 0" class="mt-2" message="Add question sets from option selection below."/>
            <Select :options="itemsOption" title="Add Sets" v-model="selectedItem" search-placeholder="Search Sets" class="mt-2 mb-1" />
            <div class="flex flex-col gap-2">
              <draggable
                :list="items"
                :disabled="urlParser().query"
                item-key="name"
                class="list-group"
                ghost-class="ghost"
                @start="dragging = true"
                @end="dragging = false"
              >
                <template #item="{ element }">
                  <div class="flex my-1.5 w-full">
                    <div :class="['flex-auto truncate max-w-full bg-gray-200 py-2 px-4 rounded-md', urlParser().query ? '' : 'hover:cursor-move']">
                      #{{ element.title }}
                    </div>
                    <div class="flex-none flex items-center">
                      <button class="text-red-500 ml-2 hover:scale-125" @click="removeQuestion(element.hash)">
                        <TrashIcon aria-hidden="true" class="h-5 w-5"/>
                      </button>
                    </div>
                  </div>
                </template>
              </draggable>
            </div>
            <Message v-if="dragging" message="Click save below if item order is changed."/>
          </div>
          <button class="w-full flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 mt-3" @click="saveOrder()">
            Save
          </button>
        </div>
        <div class="w-full sm:w-7/12 xl:w-8/12">
          <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
            <card-counter title="Total Sets" :count="totalSets"/>
            <card-counter title="Vignette" :count="totalVignette"/>
            <card-counter title="Multiple" :count="totalMultiple"/>
            <card-counter title="Questions" :count="totalQuestions"/>
          </div>

          <div class="flex flex-row gap-4 my-5">
            <input id="search-name" v-model="filters.query" autocomplete="name"
                   class="w-full mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                   name="query"
                   placeholder="Search question sets title or questions"
                   type="text">
            <button
              class="basis-2/12 inline-flex justify-center items-center px-2.5 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              type="button"
              @click="search">
              <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
              Search
            </button>
          </div>

          <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">TITLE</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">QUESTION</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">TYPE</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">VIGNETTE</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                  <span class="sr-only">Delete</span>
                </th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(item, index) in items" :key="`item-${index}`">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 truncate max-w-sm">
                  {{ item.title }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ item.questions.length }} Questions</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
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
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                  <div class="flex items-center justify-center gap-3">
                    <a :href="route('back-office.question-set.detail', {item_hash: item.hash})" class="text-blue-600 hover:text-blue-800" target="_blank">
                      <ExternalLinkIcon aria-hidden="true" class="h-5 w-5"/>
                    </a>
                  </div>
                </td>
              </tr>
              <tr v-if="filters.query !== null && items.length === 0">
                <td colspan="5">
                  <div class="text-center w-full my-4">Data Not Found.</div>
                </td>
              </tr>
              <tr v-else-if="items.length === 0">
                <td colspan="5">
                  <div class="text-center w-full my-4">Empty, please add Question Set.</div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <JetDialogModal :show="isModalFormOpen" @close="closeModalForm">
        <template #title>
          <div class="flex justify-between">
            <h4>Edit Exam</h4>
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
                <input id="code" v-model="form.code"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-2">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name">
                Name <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="name" v-model="form.name"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-2">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="description">
                Description
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="description" v-model="form.description"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>
            </div>
            <div v-if="form.is_interview" class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-2">
              <label class="block text-sm font-medium text-gray-700">
                Is Interview?
              </label>
              <div class="mt-0.5 sm:col-span-2">
                <label class="flex items-center text-xs text-green-600">
                  <span>Yes, this is an interview questions.</span>
                </label>
              </div>
            </div>
            <div v-if="!form.is_interview" class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-2">
              <label class="block text-sm font-medium text-gray-700">
                Is MCQ?
              </label>
              <div class="mt-0.5 sm:col-span-2">
                <label class="flex items-center text-xs text-gray-600">
                  <span v-if="form.is_mcq">Yes, this is Multiple Choices.</span>
                  <span v-else>No, this is Essay</span>
                </label>
              </div>
            </div>
            <div v-if="!form.is_interview" class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-2">
              <label class="block text-sm font-medium text-gray-700">
                Randomize
              </label>
              <div class="mt-0.5 sm:col-span-2">
                <label class="flex items-center">
                  <input type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" :value="true" v-model="form.is_random">
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
              @click="closeModalForm">Cancel
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

      <JetConfirmationModal :show="showModalDelete" items="center">
        <template #title>
          Delete Test {{ exam?.name }}
        </template>

        <template #content>
          Test cannot be recovered after deletion.
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
              :disabled="form.processing"
              @click="confirmDeleteAction">
              Yes, Delete Test
            </button>
          </div>
        </template>
      </JetConfirmationModal>

    </template>
  </DashboardLayout>
</template>
