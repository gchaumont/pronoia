<template>
    <ul class="space-y-2">
        <RadioGroup as="li" v-model="radioval">
            <!-- <RadioGroupLabel>Plan</RadioGroupLabel> -->
            <div class="flex flex-col gap-2">
                <RadioGroupOption v-for="(option, optionIdx) in search.results.facets[props.facet]?.options" :key="option.value" v-slot="{ checked }" :value="option.value" @click="$emit('chosen', option.value)">
                    <div :class="[checked ? 'bg-white/80 dark:bg-indigo-500/70' : 'hover:bg-white/60 dark:hover:bg-indigo-500/40', 'flex items-between cursor-pointer text-sm rounded-lg  p-2 ']">
                        <p :class="[checked ? 'dark:text-slate-100':'dark:text-slate-200']">{{option.label || option.value}}</p>
                        <p class=" rounded  ml-auto font-semibold text-black dark:text-slate-200 " v-if="option.count">{{option.count}}</p>
                    </div>
                </RadioGroupOption>
            </div>
        </RadioGroup>
    </ul>
</template>
<script setup>
import { defineProps, inject, computed, ref, watch } from 'vue'

import {
    RadioGroup,
    RadioGroupLabel,
    RadioGroupOption,
} from '@headlessui/vue'

const search = inject('resourceSearch')

const props = defineProps({
    facet: {
        type: String,
        required: true
    }
})

const radioval = ref(window.App.locale.country)

// watch(() => search.state?.facetRefinements?.[props.facet],
//     (newVal) => radioval.value = newVal, {immediate: true})

watch(radioval,
    () => search.addFacetRefinement(props.facet, radioval)
    .search())

</script>
