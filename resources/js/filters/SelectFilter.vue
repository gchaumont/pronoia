<template>
    <ul class="space-y-2">
        <li v-for="(option, optionIdx) in values" :key="option.value" class="flex items-center">
            <input :id="`filter-${facet}-${optionIdx}`" :name="`${facet}[]`" :value="option.value" :checked="option.isActive" type="checkbox" @click="toggleFacet(facet, option.value)" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <label :for="`filter-${facet}-${optionIdx}`" :class="[option.isActive ? 'font-bold' : '', 'ml-3 text-sm text-gray-600 dark:text-slate-400']">{{ option.label || option.value }}</label>
            <span class="py-1 px-2  rounded text-xs ml-auto dark:text-slate-200 dark:bg-slate-600" v-if="option.count">{{option.count}}</span>
        </li>
    </ul>
</template>
<script setup>
import { defineProps, inject, computed } from 'vue'

const search = inject('resourceSearch')

const props = defineProps({
    facet: {
        type: String,
        required: true
    }
})

const values = computed(() => search.results.facets[props.facet]?.options.map((o) => {
    o.isActive = search.hasFacetRefinement(props.facet, o.value)
    return o;
}))


const toggleFacet = function(key, value) {
    search.toggleFacetRefinement(key, value)
        .search()
}

</script>
