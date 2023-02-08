<template>
    <ul class="space-y-2">
        <li v-for="(option, optionIdx) in facetOptions" :key="option.value">
            <div class="flex items-center" @click="toggleFacet(facet, option.value, option.isActive)">   
            <!-- <span class="text-white">{{option.value}}</span> -->
            <!-- <input :id="`filter-${facet}-${optionIdx}`" :name="`${facet}[]`" :value="option.value" :checked="option.isActive" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" /> -->
            <label :for="`filter-${facet}-${optionIdx}`" :class="[option.isActive ? 'font-bold dark:text-slate-300 text-gray-700' : '', 'ml-3 text-sm text-gray-600 dark:text-slate-400']">{{ option.label || option.value }}</label>
            <span class="py-1 px-2  rounded text-xs ml-auto dark:text-slate-200 dark:bg-slate-600" v-if="option.count">{{option.count}}</span>
            </div>
            <HierarchyFilter class="ml-3 mt-2" v-if="option.options" :facet="facet" :options="option.options" :parent="option.value"/>
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
    }, 
    options: {
        type: Array, 
        required: false
    },
    parent: {
        type: String, 
        required: false,
    }
})

const facetOptions = computed(() => (props.options || search.results.facets[props.facet]?.options)?.map((o) => {
    o.isActive = search.hasFacetRefinement(props.facet, o.value)
    return o;
}))

const toggleFacet = (key, value, isActive) => {
    if (isActive) {
        value = props.parent
    }
    search
        .toggleFacetRefinement(key, value)
        .search()
}

</script>
