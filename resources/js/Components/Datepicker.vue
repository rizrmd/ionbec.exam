<template>
  <label class="text-sm text-ls-black-1 font-bold whitespace-nowrap" v-if="showTitle">{{ title }} <span class="text-ls-red-1" v-if="required">*</span></label>
  <div class="relative">
    <input
      type="text"
      readonly
      v-model="datepicker"
      @focus="inputFocus = true" @blur="inputFocus = false"
      class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md pl-9"
      :placeholder="placeholder">

    <div class="absolute top-0 left-0 px-3 py-2">
      <CalendarIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
    </div>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0">
    <div
      :class="['bg-white mt-12 rounded-lg shadow p-4 absolute z-40', positionY === 'bottom' ? 'bottom-12' : 'top-0', positionX === 'left' ? 'left-0' : 'right-0']"
      style="width: 17rem"
      @mouseover="menuHover = true" @mouseleave="menuHover = false"
      v-show="inputFocus || menuHover">

      <button @click="jumpToday()" type="button" class="w-full px-2.5 py-1.5 mb-3 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        Today
      </button>
      <div class="flex justify-between items-center mb-2">
        <div>
          <span class="text-lg font-bold text-gray-800">{{ MONTH_NAMES[month] }}</span>
          <span class="ml-1 text-lg text-gray-600 font-normal">{{ year }}</span>
        </div>
        <div>
          <button
            type="button"
            class="transition ease-in-out duration-100 cursor-pointer hover:bg-gray-200 p-1 rounded-full"
            @click="actionPrevMonth()">
            <ChevronLeftIcon aria-hidden="true" class="h-6 w-6"/>
          </button>
          <button
            type="button"
            class="transition ease-in-out duration-100 cursor-pointer hover:bg-gray-200 p-1 rounded-full"
            @click="actionNextMonth()">
            <ChevronRightIcon aria-hidden="true" class="h-6 w-6"/>
          </button>
        </div>
      </div>

      <div class="flex flex-wrap mb-3 -mx-1">
        <template v-for="(day, index) in DAYS" :key="index">
          <div style="width: 14.26%" class="px-1">
            <div
              class="text-gray-800 font-medium text-center text-xs">{{ day }}</div>
          </div>
        </template>
      </div>

      <div class="flex flex-wrap -mx-1">
        <template v-for="blankday in blankdays">
          <div
            style="width: 14.28%"
            class="text-center border p-1 border-transparent text-sm"
          ></div>
        </template>
        <template v-for="(date, dateIndex) in no_of_days" :key="dateIndex">
          <div style="width: 14.28%" class="mb-1">
            <div
              @click="getDateValue(date)"
              class="cursor-pointer text-center text-sm leading-none rounded-full leading-loose transition ease-in-out duration-100 py-1 px-0.5"
              :class="[isToday(date) ? 'bg-primary-500 text-white' : (isSelectedDay(date) ? 'bg-primary-200 text-primary-700' : 'hover:bg-primary-200 text-gray-700')]"
            >{{ date }}</div>
          </div>
        </template>
      </div>
      <input type="time" class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" v-if="enableTime" v-model="time"/>
    </div>
    </transition>
  </div>
</template>

<script setup>
import {onMounted, ref, defineProps, toRefs, defineEmits, watch} from 'vue'
import {CalendarIcon, ChevronLeftIcon, ChevronRightIcon} from '@heroicons/vue/outline'

const props = defineProps({
  modelValue: {
    default: null,
    type: String,
  },
  title: {
    default: "Datepicker",
    type: String,
  },
  required: {
    default: false,
    type: Boolean,
  },
  showTitle: {
    default: false,
    type: Boolean
  },
  placeholder: {
    default: "Select date",
    type: String
  },
  enableTime: {
    default: false,
    type: Boolean
  },
  positionY: {
    default: "bottom",
    type: String
  },
  positionX: {
    default: "left",
    type: String
  }
})

const inputFocus = ref(false)
const menuHover = ref(false)

const emit = defineEmits(['update:modelValue'])
const {required, modelValue, enableTime} = toRefs(props)

const today = new Date();
const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const datepicker = ref('')
const showDatepicker = ref(false)

const month = ref(0)
const year = ref(2022)
const no_of_days = ref([])
const blankdays = ref([])
const days = ref(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'])

const time = ref(null);

watch(time, (value, prevValue) => {
  if (enableTime.value && value !== prevValue && modelValue.value !== null) {
    const date = modelValue.value.split(' ')[0].split('-')
    emit('update:modelValue', formatValueDate(new Date(date[0], parseInt(date[1]) - 1, date[2])));
  }
})

watch(modelValue, (value, prevValue) => {
  if (value !== null) {
    if (enableTime.value) {
      const timeVal = value.split(' ')[1];
      if (timeVal !== undefined && timeVal !== '') time.value = timeVal
    }
    const date = enableTime.value ? value.split(' ')[0].split('-') : value.split('-')
    datepicker.value = formatDate(new Date(date[0], parseInt(date[1]) - 1, date[2]));
  } else {
    datepicker.value = ''
  }
})

onMounted(() => {
  month.value = today.getMonth();
  year.value = today.getFullYear();
  if (required.value) {
    const dateToday = new Date(year.value, month.value, today.getDate());
    datepicker.value = formatDate(dateToday);
    emit('update:modelValue', formatValueDate(dateToday));
  }

  if (modelValue.value !== null) {
    const mv = enableTime.value ? modelValue.value.split(' ')[0].split('-') : modelValue.value.split('-')
    const date = new Date(mv[0], parseInt(mv[1]) - 1, mv[2]);

    const splitValue = modelValue.value.split(' ');
    if (enableTime.value && splitValue.length >= 2) time.value = splitValue[1];

    month.value = date.getMonth();
    year.value = date.getFullYear();

    emit('update:modelValue', formatValueDate(date));
    datepicker.value = formatDate(date);
  }

  getNoOfDays()
})

const isSelectedDay = (dateParam) => {
  if (modelValue.value !== null) {
    const date = enableTime.value ? modelValue.value.split(' ')[0].split('-') : modelValue.value.split('-')
    return parseInt(date[0]) === year.value && parseInt(date[1]) === month.value + 1 && parseInt(date[2]) === parseInt(dateParam);
  }
  return false;
}
const isToday = (date) => today.toDateString() === new Date(year.value, month.value, date).toDateString()
const formatDate = (date) => {
  let result = date.getDate() +' / ' + (date.getMonth() + 1) + ' / ' + date.getFullYear();
  if (time.value !== null) result = result + ' ' + time.value;
  return result
}
const formatValueDate = (date) => {
  let result = date.getFullYear() + "-" + ('0' + String(date.getMonth() + 1)).slice(-2) + "-" + ('0' + String(date.getDate())).slice(-2);
  if (time.value !== null) result = result + ' ' + time.value;
  return result;
}

const getDateValue = (date) => {
  let selectedDate = new Date(year.value, month.value, date);
  if (!isSelectedDay(date) || required.value) {
    emit('update:modelValue', formatValueDate(selectedDate));
  } else {
    emit('update:modelValue', null);
  }


  if (!enableTime) showDatepicker.value = false;
}

const getNoOfDays = () => {
  let daysInMonth = new Date(year.value, month.value + 1, 0).getDate();

  // find where to start calendar day of week
  let dayOfWeek = new Date(year.value, month.value).getDay();
  let blankdaysArray = [];
  for (let i = 1; i <= dayOfWeek; i++) {
    blankdaysArray.push(i);
  }

  let daysArray = [];
  for (let i = 1; i <= daysInMonth; i++) {
    daysArray.push(i);
  }

  blankdays.value = blankdaysArray;
  no_of_days.value = daysArray;
}

const actionNextMonth = () => {
  month.value++
  if (month.value >= 12) {
    year.value++
    month.value = 0
  }
  getNoOfDays()
}

const actionPrevMonth = () => {
  month.value--
  if (month.value <= -1) {
    year.value--
    month.value = 11
  }
  getNoOfDays()
}

const jumpToday = () => {
  month.value = today.getMonth();
  year.value = today.getFullYear();
}
</script>
