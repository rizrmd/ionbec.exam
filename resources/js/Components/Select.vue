<template>

  <div class="grid w-full">

    <template v-if="position === 'bottom'">
      <label class="text-sm text-ls-black-1 font-bold whitespace-nowrap" v-if="showTitle">{{ title }} <span class="text-ls-red-1" v-if="required">*</span></label>
      <input type="text" :placeholder="title" :value="selectedData !== null && selectedData !== undefined ? selectedData.text : ''" :required="required"
             readonly
             autocomplete="off"
             @focus="inputFocus = true" @blur="inputFocus = false"
             class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md block disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none" :disabled="disabled"/>
    </template>

    <div class="relative inline-block text-left" style="z-index: 11" @mouseover="menuHover = true" @mouseleave="menuHover = false">
      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0">
        <div v-show="inputFocus || menuHover">
          <div
            :class="['absolute p-2 w-full origin-top-right bg-white rounded-md shadow-lg focus:outline-none', (position === 'bottom') ? 'mt-1' : 'mb-2 bottom-0']">
            <div class="mb-2 relative" v-if="position === 'bottom'">
              <input type="text" v-model="searchValue"
                     :placeholder="searchPlaceholder !== '' ? searchPlaceholder : 'Search ' + title"
                     autocomplete="off"
                     v-if="search"
                     class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md block text-sm text-black placeholder-gray-300 font-medium w-full pl-4 pr-8 py-2"/>
              <span v-if="searchValue.length >= 1"
                    class="cursor-pointer absolute inset-y-0 bottom-0 right-2 flex items-center focus:outline-none focus:ring-0 border-none"
                    @click="searchValue = ''">
                  <XIcon aria-hidden="true" class="h-4 w-4 text-red-700"/>
                </span>
            </div>

            <ul class="flex flex-col text-left max-h-40 w-full overflow-y-auto">
              <template v-for="(option, index) in filteredList" :key="index">
                <li class="my-0.5">
                  <div :class="['flex rounded-md items-center w-full px-3 py-2 text-sm text-left focus:outline-none cursor-pointer', selectedData !== null && selectedData !== undefined && selectedData.value === option.value ? 'hover:bg-primary-700 bg-primary-600 text-white' : 'hover:bg-gray-300 text-black']" @click="actionSelect(option)">
                    {{ option.text }}
                  </div>
                </li>
              </template>
              <li v-if="filteredList.length === 0" class="text-center py-2">
                {{ placeholder }}
              </li>
            </ul>

            <div class="mt-2 relative" v-if="position === 'top'">
              <input type="text" v-model="searchValue"
                     :placeholder="searchPlaceholder !== '' ? searchPlaceholder : 'Search ' + title"
                     autocomplete="off"
                     v-if="search"
                     class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md block text-sm text-black placeholder-gray-300 font-medium w-full pl-4 pr-8 py-2"/>
              <span v-if="searchValue.length >= 1"
                    class="cursor-pointer absolute inset-y-0 bottom-0 right-2 flex items-center focus:outline-none focus:ring-0 border-none"
                    @click="searchValue = ''">
                  <XIcon aria-hidden="true" class="h-4 w-4 text-red-700"/>
                </span>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <template v-if="position === 'top'">
      <label class="text-sm text-ls-black-1 font-bold whitespace-nowrap" v-if="showTitle">{{ title }} <span class="text-ls-red-1" v-if="required">*</span></label>
      <input type="text" :placeholder="title" :value="selectedData !== null && selectedData !== undefined ? selectedData.text : ''" :required="required"
             readonly
             autocomplete="off"
             @focus="inputFocus = true" @blur="inputFocus = false"
             class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md block"/>
    </template>

  </div>
</template>

<script>
import {snakeCase} from 'lodash'
import {XIcon} from '@heroicons/vue/solid'

export default {
  props: {
    modelValue: Object, // previously was `value: String`
    placeholder: {
      default: "Data not found.",
      type: String
    },
    title: {
      default: "Select",
      type: String,
    },
    required: {
      default: false,
      type: Boolean,
    },
    options: {
      default: [],
      type: Array,
    },
    search: {
      default: true,
      type: Boolean,
    },
    searchPlaceholder: {
      default: "",
      type: String
    },
    pills: {
      default: true,
      type: Boolean
    },
    pillsSize: {
      default: 'normal',
      type: String
    },
    showTitle: {
      default: false,
      type: Boolean
    },
    position: {
      default: "bottom",
      type: String
    },
    disabled: {
      default: false,
      type: Boolean
    }
  },
  setup() {
    return {
      snakeCase
    }
  },
  components: {
    XIcon
  },
  data() {
    return {
      selectedData: null,
      inputFocus: false,
      menuHover: false,
      searchValue: ""
    }
  },
  mounted() {
    if (this.options.length >= 1) {
      this.makeSelectedDataOptions(this.options)
    }
    this.selectedData = this.modelValue;
  },
  watch: {
    modelValue: {
      handler(value) {
        this.selectedData = value
      }
    },
    selectedData(val) {
      this.$emit('update:modelValue', val)
    },
    options(val) {
      if (val.length >= 1) {
        this.makeSelectedDataOptions(val)
      }
    },
  },
  computed: {
    filteredList() {
      return this.options.filter(item => {
        return item.text.toLowerCase().includes(this.searchValue.toLowerCase());
      });
    },
    filteredPills() {
      let pills = []
      this.options.forEach((item) => {
        if (this.selectedData.includes(item.value)) pills.push({...item});
      });
      return pills.sort((a, b) => (a.text > b.text) ? 1 : ((b.text > a.text) ? -1 : 0));
    },
  },
  methods: {
    makeSelectedDataOptions(val) {
      val.forEach((item) => {
        const checked = (item.checked !== undefined) ? item.checked : false;
        if (checked) this.selectedData.push(item.value)
      })
    },
    deleteChosenData(key) {
      this.selectedData = this.selectedData.filter(v => v !== key)
    },
    actionSelect(data) {
      if (this.selectedData === null || this.selectedData === undefined) {
        this.selectedData = data;
      } else if (data.value === this.selectedData.value) {
        this.selectedData = null;
      } else {
        this.selectedData = data;
      }
    }
  },
  name: "Select"
}
</script>
