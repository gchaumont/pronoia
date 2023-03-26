<template>
    <div class="flex">
        <SwitchGroup >
            <Switch v-model="enabled" :class="enabled ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-slate-600'" class="relative inline-flex h-6 w-11 items-center rounded-full ">
                <span class="sr-only">Toggle</span>
                <span :class="enabled ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition" />
            </Switch>
            <SwitchLabel v-if="text" class="ml-5 mt-1 dark:text-white text-sm">{{text}}</SwitchLabel>
        </SwitchGroup>
    </div>
</template>
<script setup>
import { ref, defineEmits } from 'vue'
import { Switch, SwitchGroup, SwitchLabel } from '@headlessui/vue'
import { defineProps, inject, computed, watch } from 'vue'

const emit = defineEmits(['toggled'])

const search = inject('resourceSearch')

const props = defineProps({
    facet: {
        type: String,
        required: true
    },
    text: {
        type: String,
        required: false,
    }
})

const enabled = ref(search.hasFacetRefinement(props.facet, true))

watch(enabled, () => {
    search.addFacetRefinement(props.facet, enabled)
        .search()

    emit('toggled');
    })

// const toggleFacet = function(key, value) {
//     search.addFacetRefinement(key, value)
//         .search()
// }

</script>
