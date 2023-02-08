<template>
    <div v-if="suggestData?.options?.length" class="dark:text-slate-200">
        <span>{{suggestData.label}}</span>
        <span class="ml-3 font-semibold cursor-pointer" @click="performSearch(suggestData.options[0])">{{suggestData.options[0]}}</span>
    </div>
</template>
<script setup>
import { inject, computed } from 'vue'

const props = defineProps({
    instance: {
        required: false,
        type: [String, Object],
        default: 'resourceSearch',
    },
    suggestion: {
        required: true,
        type: [String],
    }
})

const emit = defineEmits(['search'])



const search = computed(() => typeof props.instance === 'string' ? inject(props.instance) : props.instance)

const suggestData = computed(() => search.value.results.suggestions[props.suggestion])

const performSearch = function(query) {
    search.value.setQuery(query).search()
    emit('search', search.value.state.query);
}

</script>
