<script setup>
import AuthoringGroupLayout from '@/Layouts/AuthoringGroupLayout.vue'
import {ChevronLeftIcon, SearchIcon, TrashIcon, PencilAltIcon, ClipboardListIcon, XIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import JetDialogModal from '@/Jetstream/DialogModal'
import {useForm, Link} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetBanner from '@/Jetstream/Banner'
import Card from "@/Components/Card";
import Editor from "@/Components/Editor";

const props = defineProps({
  exam: {
    type: Object,
  },
  itemTypes: {
    type: Object,
  }
})

onMounted(() => {
  window.print();
})

const answerIndex = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
</script>

<template>
  <div class="container">
    <div class="mb-7">
      <h1 class="text-2xl">
        <span class="font-bold">{{ exam.name }}</span><br>
        <span class="text-xs">({{ exam.code }})</span>
      </h1>
      <h3 v-if="exam.description">{{ exam.description }}</h3>
    </div>

    <div>
      <div class="min-h-full flex-1 mb-2" v-for="(item, key) in exam.items">

        <div class="flex flex-col items-center gap-3 my-5">
          <div v-for="file in item.attachments" v-if="item.attachments.length >= 1" class="w-full flex gap-2 max-w-xl">
            <div class="flex-auto flex justify-center items-center">
              <img :src="file.url" :alt="file.description" class="rounded-lg">
            </div>
          </div>
        </div>

        <div v-if="item.is_vignette != null" class="my-5 px-6">
          <p class="font-bold">Scenario</p>
          <div v-html="item.content"></div>
        </div>

        <div v-for="(question, questKey) in item.questions" class="question mb-4">
          <div class="flex justify-between items-center space-x-4">
            <div class="relative">
              <div class="text-md font-bold">#{{ key + 1 }}.{{ questKey + 1 }}</div>
            </div>
            <div v-html="question.question" class="flex-1 text-md"></div>
            <div>
              <span class="px-2 py-1 border-2 border-gray-300 rounded-md text-xs" v-if="item.type">{{ itemTypes[item.type] }}</span>
            </div>
          </div>
          <div class="ml-10">
            <div class="rounded-lg">
              <div class="flex flex-col gap-2 mt-4 w-auto" v-if="question.type !== null && question.type.name === 'multiple-choice'">
                <button v-for="(answer, ansIndex) in question.answers" :key="ansIndex" :class="['flex text-left px-3 bg-gray-100 rounded-md']">
                  <span class="mr-3 font-bold uppercase">{{ answerIndex[ansIndex] }}</span> <span v-html="answer.answer" class="text-xs"></span>
                </button>
              </div>
              <div class="mt-2" v-else>
                <div class="bg-gray-100 rounded-lg w-full h-36 border-2 border-gray-100">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
* {
  font-family: Calibri, sans-serif, "Times New Roman";
}
.container {
  padding-top: 24px;
  padding-bottom: 24px;
  background: white;
}

.question {
  /*page-break-after: always;*/
  break-inside: avoid;
}

@media print
{
  .container {
    padding-top: 0;
  }
}
</style>

<style>
body, #app {
  background: white !important;
}
</style>
