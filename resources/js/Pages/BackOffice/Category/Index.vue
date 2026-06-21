<template>
  <DashboardLayout title="Manage Categories">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Manage Categories</h1>
        <div class="flex gap-2">
          <button
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="button"
            @click="openManageTypesModal">
            <CogIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
            Manage Types
          </button>
          <button
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="button"
            @click="openModalForm">
            <PlusIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
            Category
          </button>
        </div>
      </div>
    </template>

    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
        <card-counter v-for="(type, index) in types" :key="`type-card-${index}`" :title="type.label" :count="type.count"
                      @click="filterWithCard(type.value)" :color="categoryTypesColor(type.value, categoryColors)+'-600'"/>
      </div>
      <div class="mt-8 flex flex-row gap-4">
        <input id="search-name" v-model="filters.query" autocomplete="category-name"
               class="flex-1 mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="name"
               placeholder="Search Name"
               type="text">
        <select id="type" v-model="filters.type"
                class="flex-1 mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                name="type">
          <option :value="null">All</option>
          <option v-for="(type, index) in types" :key="`type-option-${index}`" :value="type.value">{{
              type.label
            }}
          </option>
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
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">TYPE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">QUESTION</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(category, index) in payload.data" :key="`category-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ category.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span :class="[`inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-${categoryTypesColor(category.type, categoryColors)}-100 text-${categoryTypesColor(category.type, categoryColors)}-800`]">
                      {{ categoryTypes[category.type] }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ category.question_total }}
                    Questions
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end">
                    <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5 cursor-pointer text-blue-600" @click="editCategory(category.hash)"/>
                  </td>
                </tr>
                <tr v-if="(filters.query !== null || filters.role !== null) && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty, please create Category.</div>
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

  <JetDialogModal :show="isModalFormOpen" @close="closeModalForm" items="center">
    <template #title>
      <div class="flex justify-between items-center">
        <h4>{{ modalFormTitle }}</h4>
        <button
          v-if="isEdit"
          class="inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="button"
          :disabled="form.processing"
          @click="deleteCategory">
          <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5 text-white" @click="editCategory(category.hash)"/>
        </button>
      </div>
    </template>
    <template #content>
      <JetValidationErrors class="my-4"/>
      <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name"> Type <small
            class="text-red-500">*</small> </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <select v-model="form.type"
                    class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                    name="type">
              <option v-for="(option, index) in types" :key="`option-${index}`" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name"> Name <small
            class="text-red-500">*</small> </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <input id="name" v-model="form.name" autocomplete="name" autofocus
                   class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                   name="name"
                   type="text"/>
          </div>
        </div>
        <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="description">
            Description </label>
          <div class="mt-1 sm:mt-0 sm:col-span-2">
            <textarea id="description" v-model="form.description"
                      class="max-w-lg shadow-sm block w-full focus:ring-primary-500 focus:border-primary-500 sm:text-sm border border-gray-300 rounded-md"
                      name="description" rows="3"/>
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
          @click="performUpdateOrCreate">
          Save
        </button>
      </div>
    </template>
  </JetDialogModal>

  <JetConfirmationModal :show="isDeleting" items="center">
    <template #title>
      Delete Category {{ deletingCategory?.name }}
    </template>
    <template #content>
      Category cannot be recovered after deletion.
    </template>
    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="cancelDeleteCategory">Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          type="submit"
          :disabled="form.processing"
          @click="confirmDeleteCategory">
          Yes, Delete Category
        </button>
      </div>
    </template>
  </JetConfirmationModal>

  <JetDialogModal :show="isManageTypesOpen" @close="closeManageTypesModal" items="center">
    <template #title>
      Manage Category Types
    </template>
    <template #content>
      <div class="space-y-4">
        <JetValidationErrors/>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500">Label</th>
                <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500">Color</th>
                <th class="px-2 py-2 text-left text-xs font-semibold text-gray-500">Usage</th>
                <th class="px-2 py-2 text-right text-xs font-semibold text-gray-500"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(type, index) in types" :key="`manage-type-${index}`">
                <td class="px-2 py-2">
                  <input v-model="typeRows[type.hash].label" type="text"
                         class="block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md"/>
                </td>
                <td class="px-2 py-2">
                  <div class="flex items-center gap-2">
                    <span :class="`inline-block h-4 w-4 rounded-full bg-${(typeRows[type.hash]?.color) || categoryTypesColor(type.value, categoryColors)}-500`"></span>
                    <select v-model="typeRows[type.hash].color"
                            class="block shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md">
                      <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
                    </select>
                  </div>
                </td>
                <td class="px-2 py-2 text-sm text-gray-500 whitespace-nowrap">{{ type.count }} used</td>
                <td class="px-2 py-2 text-right whitespace-nowrap">
                  <button
                    class="inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                    type="button"
                    :disabled="typeForm.processing"
                    @click="saveType(type)">
                    Save
                  </button>
                  <button
                    class="ml-2 inline-flex justify-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    type="button"
                    :disabled="typeForm.processing || type.count > 0"
                    :title="type.count > 0 ? 'In use by categories, cannot delete' : 'Delete type'"
                    @click="deleteType(type)">
                    Delete
                  </button>
                  <p v-if="type.count > 0" class="text-xs text-gray-400 mt-1">In use</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Add Type</h4>
          <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input v-model="newType.label" type="text" placeholder="Label"
                   class="flex-1 block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md"/>
            <select v-model="newType.color"
                    class="block shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm border-gray-300 rounded-md">
              <option v-for="color in colorOptions" :key="color" :value="color">{{ color }}</option>
            </select>
            <button
              class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
              type="button"
              :disabled="typeForm.processing || !newType.label"
              @click="addType">
              Add
            </button>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button"
          @click="closeManageTypesModal">Close
        </button>
      </div>
    </template>
  </JetDialogModal>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {PlusIcon, SearchIcon, PencilAltIcon, TrashIcon, CogIcon} from '@heroicons/vue/outline'
import CardCounter from '@/Components/CardCounter.vue'
import Pagination from '@/Components/Pagination.vue'
import {computed, onMounted, reactive, ref, toRaw, toRefs} from 'vue'
import {Inertia} from '@inertiajs/inertia'
import JetDialogModal from '@/Jetstream/DialogModal'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import {useForm} from "@inertiajs/inertia-vue3"
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import urlParser from "@/Libs/urlParser";
import categoryTypesColor from "@/Libs/categoryTypesColor";

const props = defineProps({
  types: {
    type: Array,
  },
  payload: {
    type: Object,
  },
  categoryTypes: {
    type: Object,
  },
  categoryColors: {
    type: Object,
    default: () => ({}),
  },
})

const {payload, types} = toRefs(props)

const colorOptions = ['red', 'green', 'blue', 'yellow', 'gray', 'purple', 'pink', 'indigo', 'orange', 'teal']

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

const form = useForm({
  type: null,
  name: null,
  description: null,
})

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  filters.type = urlParams.type || null
  if (urlParams.modal_create && Object.keys(urlParams).length === 1) openModalForm()
})

const filterWithCard = (type) => {
  filters.type = type
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

const isModalFormOpen = ref(false)

const editingCategoryHash = ref(null)
const isEdit = computed(() => editingCategoryHash.value !== null)
const editingCategory = computed(() => toRaw(payload.value.data.find(category => category.hash === editingCategoryHash.value)))

const modalFormTitle = computed(() => isEdit.value ? `Edit Category ${editingCategory.value?.name}` : 'Create Category')

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
  editingCategoryHash.value = null
}

const openModalForm = () => {
  isModalFormOpen.value = true

  if (isEdit.value) {
    form.type = toRaw(editingCategory.value.type)
    form.name = toRaw(editingCategory.value.name)
    form.description = toRaw(editingCategory.value.description)
    form.isDirty = false
  } else {
    form.type = null
    form.name = null
    form.description = null
    form.isDirty = false
  }
}

const editCategory = (categoryHash) => {
  editingCategoryHash.value = toRaw(categoryHash)
  openModalForm()
}

const performUpdateOrCreate = () => {
  if (isEdit.value) {
    form.put(route('back-office.category.update', {category_hash: editingCategory.value.hash}), {
      onSuccess: closeModalForm
    })
  } else {
    form.post(route('back-office.category.store'), {
      onSuccess: closeModalForm
    })
  }
}

const deletingCategoryHash = ref(null)
const isDeleting = computed(() => deletingCategoryHash.value !== null)
const deletingCategory = computed(() => toRaw(payload.value.data.find(category => category.hash === deletingCategoryHash.value)))

const deleteCategory = () => {
  deletingCategoryHash.value = toRaw(editingCategoryHash.value)
  closeModalForm()
}

const cancelDeleteCategory = () => {
  deletingCategoryHash.value = null
}

const confirmDeleteCategory = () => {
  form.delete(route('back-office.category.destroy', {category_hash: deletingCategory.value?.hash}), {
    onSuccess: () => deletingCategoryHash.value = null
  })
}

const isManageTypesOpen = ref(false)
const typeRows = reactive({})
const newType = reactive({label: null, color: 'gray'})

const buildTypeRows = () => {
  Object.keys(typeRows).forEach(key => delete typeRows[key])
  types.value.forEach((type, index) => {
    typeRows[type.hash] = {
      label: type.label,
      color: categoryTypesColor(type.value, props.categoryColors),
      order: index,
    }
  })
}

const openManageTypesModal = () => {
  buildTypeRows()
  newType.label = null
  newType.color = 'gray'
  isManageTypesOpen.value = true
}

const closeManageTypesModal = () => {
  form.clearErrors()
  isManageTypesOpen.value = false
}

const typeForm = useForm({
  label: null,
  color: null,
  order: null,
})

const saveType = (type) => {
  const row = typeRows[type.hash]
  typeForm.label = row.label
  typeForm.color = row.color
  typeForm.order = row.order
  typeForm.put(route('back-office.category-type.update', {category_type_hash: type.hash}), {
    preserveScroll: true,
    onSuccess: () => buildTypeRows(),
  })
}

const deleteType = (type) => {
  if (type.count > 0) return
  typeForm.delete(route('back-office.category-type.destroy', {category_type_hash: type.hash}), {
    preserveScroll: true,
    onSuccess: () => buildTypeRows(),
  })
}

const addType = () => {
  typeForm.label = newType.label
  typeForm.color = newType.color
  typeForm.order = null
  typeForm.post(route('back-office.category-type.store'), {
    preserveScroll: true,
    onSuccess: () => {
      newType.label = null
      newType.color = 'gray'
      buildTypeRows()
    },
  })
}
</script>

<style scoped>

</style>
