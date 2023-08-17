<template>
    <div v-intersect="intersect" v-if="searchInstance.results.hasMore">
        <button
            class="mt-10 m-auto border px-3 py-2 rounded-lg border-gray-500 border-px flex items-center gap-2 hover:bg-slate-200 dark:hover:bg-slate-800 dark:text-slate-100"
            @click="requestMore">
            <LoadingSpinner v-if="searchInstance.isLoading" class="h-6 w-6" aria-hidden="true" />
            <PlusIcon v-else class="h-6 w-6" aria-hidden="true" />

            {{ $filters.trans('general.more') }}
        </button>
    </div>
</template>
<script setup>
import { inject, ref } from 'vue';
import { PlusIcon } from '@heroicons/vue/20/solid'
import LoadingSpinner from '@/components/Support/LoadingSpinner.vue'

const autoloaded = ref(0);

const props = defineProps({
    instance: {
        required: false,
        type: [Object, String],
        default: 'resourceSearch',
    },
    autoload: {
        required: false,
        type: Boolean,
        default: false,
    }
})

const searchInstance = typeof props.instance === 'string' ? inject(props.instance) : props.instance;

const intersect = function (inter) {
    if (props.autoload && inter.isIntersecting && autoloaded.value < 5) {
        autoloaded.value++;
        loadMore()
    }
}

const requestMore = function () {
    autoloaded.value = 0;
    loadMore();
}


const loadMore = function () {
    if (!searchInstance.isLoading) {
        searchInstance.nextPage().search()
    }
}

</script>
