import {createApp, h, reactive} from 'vue'
import ExamNavigation from '../../resources/js/Layouts/Partials/Exam/ExamNavigation.vue'
const state = reactive({index: 0, count: 120, extra: null, extraClicks: 0})
const items = Array.from({length: 120}, (_, i) => ({hash: `item-${i}`, questions: [{hash: `question-${i}`}]}))
window.navigationTest = state
createApp({render: () => h('div', {style: {transform: 'translateZ(0)', overflow: 'hidden'}}, [
  h('main', {style: {minHeight: '2000px'}}, [h('p', {id: 'current-question'}, `${state.index + 1}.1`), h('textarea', {'aria-label': 'QA editor'})]),
  h(ExamNavigation, {items: items.slice(0, state.count), currentItem: {item_hash: items[state.index].hash, questions: items[state.index].questions}, done: [], skipped: [], later: [], showPrevious: state.index > 0, showNext: state.index < state.count - 1, extraButton: state.extra,
    onPrevious: () => state.index--, onNext: () => state.index++, onClick: (_, index) => state.index = index, onExtraButtonAction: () => state.extraClicks++})
])}).mount('#app')
