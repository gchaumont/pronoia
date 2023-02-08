<template>
    <ul>
        <slot v-for="(hit, idx) in search.results.hits" :hit="hit" :idx="idx" /> 
        <LoadingSpinner v-if="search.isLoading" class="h-6 w-6" aria-hidden="true" />
    </ul>
</template>
<script setup>
import { inject, computed } from 'vue';
import LoadingSpinner from '@/components/Support/LoadingSpinner.vue'

const props = defineProps({
    instance: {
        required: false, 
        type: [String, Object],
        default: 'resourceSearch'
    }

})

const search = computed(() => typeof props.instance === 'string' ? inject(props.instance) : props.instance)

</script>
