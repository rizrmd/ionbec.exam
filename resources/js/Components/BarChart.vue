<script setup>
import VueApexCharts from "vue3-apexcharts";
import {ref, toRefs} from "vue";

const props = defineProps({
  series: {
    type: Array,
  },
  labels: {
    type: Array,
  },
})

const {series, labels} = toRefs(props);

const seriesRef = ref([{
  data: series.value
}])

const labelsRef = ref({
  chart: {
    type: 'bar',
    height: 350
  },
  plotOptions: {
    bar: {
      borderRadius: 4,
      horizontal: true,
    }
  },
  dataLabels: {
    enabled: false
  },
  xaxis: {
    categories: labels.value,
  },
  // tooltip: {
  //   custom: function() {
  //     return 'asd';
  //   }
  // }
})
</script>

<template>
  <VueApexCharts type="bar" height="430" :options="labelsRef" :series="seriesRef"></VueApexCharts>
</template>

<style scoped>

</style>
