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

const props = defineProps({
  payload: {
    type: Object,
  },
  group: {
    type: Object,
  }
})

const {payload, group} = toRefs(props)

onMounted(() => {
  window.print();
})
</script>

<template>
  <div class="container">
    <div class="mb-7">
      <h1 class="text-2xl font-bold">{{ group.name ?? '-' }}</h1>
      <h3>{{ group.description ?? '-' }}</h3>
    </div>

    <table>
      <tr>
        <th>Name</th>
        <th>Email</th>
      </tr>
      <tr v-for="taker in payload">
        <td>{{ taker.name }}</td>
        <td>{{ taker.email }}</td>
      </tr>
    </table>
  </div>
</template>

<style scoped>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}

.container {
  padding-top: 24px;
  padding-bottom: 24px;
  background: white;
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
