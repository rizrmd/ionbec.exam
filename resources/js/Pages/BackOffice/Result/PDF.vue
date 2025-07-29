<script setup>
import {onMounted, toRefs} from 'vue'

const props = defineProps({
  payload: {
    type: Object,
  },
  deliveries: {
    type: Object,
  },
  group: {
    type: Object,
  }
})

const {payload, deliveries} = toRefs(props)

onMounted(() => {
  window.print();
})

const getSummary = (deliveries, attempts) => {
  let mapScore = deliveries.map((delivery) => getScore(delivery, attempts));
  let sumScore = mapScore.reduce((v, acc) => parseFloat(v) + parseFloat(acc));
  return Math.round(sumScore / deliveries.length * 100) / 100;
}

const getScore = (delivery, attempts) => {
  let attempt = Object.values(attempts).find((obj) => obj.delivery.hash === delivery.hash);
  return (attempt !== undefined) ? attempt.score : 0;
}

const getTakerCode = (taker) => {
  return taker.name;
}
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
        <th v-for="delivery in deliveries">{{ delivery.name ?? '-' }} ({{ delivery.exam.name }})</th>
        <th>Summary</th>
      </tr>
      <tr v-for="taker in payload">
        <td>{{ getTakerCode(taker) }}</td>
        <td v-for="delivery in deliveries">{{ getScore(delivery, taker.attempts) }}</td>
        <td>{{ getSummary(deliveries, taker.attempts) }}</td>
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
