<template>
  <label class="text-sm text-ls-black-1 font-bold whitespace-nowrap" v-if="showTitle">{{ title }} <span class="text-ls-red-1" v-if="required">*</span></label>
  <div class="relative">
    <input
      type="text"
      readonly
      v-model="datepicker"
      @focus="inputFocus = true"
      @blur="inputFocus = false"
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
      <div class="flex mt-3 gap-2">
        <button @click="jumpToday()" class="w-full px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          Today
        </button>
        <button @click="clearValue()" class="w-full px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          Clear
        </button>
      </div>
    </div>
    </transition>
  </div>
</template>

<script setup>
import {onMounted, ref, defineProps, toRefs, defineEmits, watch} from 'vue'
import {CalendarIcon, ChevronLeftIcon, ChevronRightIcon} from '@heroicons/vue/outline'
import moment from 'moment'

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
const {required, modelValue} = toRefs(props)

const today = new Date();
const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const datepicker = ref('')

const tempDates = ref([])

const month = ref(0)
const year = ref(2022)
const no_of_days = ref([])
const blankdays = ref([])

watch(modelValue, (value, prevValue) => {
  if (value !== null) {
    const data = value.split(' - ');
    const date = data[0].split('-')
    const dateSec = data[1].split('-')
    datepicker.value = `${formatDate(new Date(date[0], parseInt(date[1]) - 1, date[2]))} - ${formatDate(new Date(dateSec[0], parseInt(dateSec[1]) - 1, dateSec[2]))}`
  } else {
    datepicker.value = ''
  }
})

onMounted(() => {
  month.value = today.getMonth();
  year.value = today.getFullYear();

  if (modelValue.value !== null && modelValue.value !== '') {
    const data = modelValue.value.split(' - ');
    const date = data[0].split('-')
    const dateSec = data[1].split('-')
    const dateFirst = new Date(date[0], parseInt(date[1]) - 1, date[2]);

    month.value = dateFirst.getMonth();
    year.value = dateFirst.getFullYear();

    tempDates.value = []
    tempDates.value.push(data[0])
    tempDates.value.push(data[1])
    emit('update:modelValue', formatTwoDate(data[0], data[1]));
    datepicker.value = `${formatDate(new Date(date[0], parseInt(date[1]) - 1, date[2]))} - ${formatDate(new Date(dateSec[0], parseInt(dateSec[1]) - 1, dateSec[2]))}`
  }

  getNoOfDays()
})

const isSelectedDay = (dateParam) => {
  if (tempDates.value.length >= 1) {
    return tempDates.value.indexOf(formatValueDate(new Date(year.value, month.value, dateParam))) > -1;
  }
  return false;
}
const isToday = (date) => today.toDateString() === new Date(year.value, month.value, date).toDateString()
const formatDate = (date) => date.getDate() +' / ' + (date.getMonth() + 1) + ' / ' + date.getFullYear();
const formatValueDate = (date) => date.getFullYear() + "-" + ('0' + String(date.getMonth() + 1)).slice(-2) + "-" + ('0' + String(date.getDate())).slice(-2);
const formatTwoDate = (date, dateSec) => (!moment(date).isAfter(moment(dateSec))) ? `${date} - ${dateSec}` : `${dateSec} - ${date}`;

const getDateValue = (date) => {
  let selectedDate = formatValueDate(new Date(year.value, month.value, date));

  if (isSelectedDay(date)) {
    let index = tempDates.value.indexOf(selectedDate);
    if (index !== -1) tempDates.value.splice(index, 1)
  } else {
    tempDates.value.push(selectedDate);

  }

  if (tempDates.value.length === 2) {
    emit('update:modelValue', formatTwoDate(tempDates.value[0], tempDates.value[1]));
  } else if (tempDates.value.length > 2) {
    tempDates.value = []
    tempDates.value.push(selectedDate);
    emit('update:modelValue', null);
  } else {
    emit('update:modelValue', null);
  }
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

const clearValue = () => {
  tempDates.value = [];
  emit('update:modelValue', null);
};
</script>
