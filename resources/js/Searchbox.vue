<template>
    <form method="get" @submit.prevent="performSearch">
        <div class=" w-full max-w-xs lg:max-w-md">
            <label for="search-inut" class="sr-only">{{ $filters.trans('general.search') }}</label>
            <div class="relative text-gray-400  focus-within:text-gray-700">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
                </div>
                <input id="search-inut"
                    class="block w-full transition-colors rounded-md border border-transparent bg-white bg-opacity-10 py-2 pl-10 pr-3 leading-5   placeholder-gray-400 focus:border-transparent focus:bg-opacity-100 focus:text-gray-900 focus:placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-sm"
                    placeholder="Search" type="search" name="search" v-model="search.state.query" autocomplete="off"
                    autocapitalize="off" autocorrect="off" spellcheck="false" enterKeyHint="search" autofocus />
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pl-3">
                    <LoadingSpinner v-if="search.isLoading" class="h-6 w-6" aria-hidden="true" />
                </div>
            </div>
        </div>
    </form>
</template>
<script setup>
import { inject, computed } from 'vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/solid'
import { logSearch } from '@/tracking'
import LoadingSpinner from '@/components/Support/LoadingSpinner.vue'


const emit = defineEmits(['search'])

const props = defineProps({
    instance: {
        required: false,
        type: [String, Object],
        default: 'resourceSearch',
    }
})

const search = computed(() => typeof props.instance === 'string' ? inject(props.instance) : props.instance)


function performSearch() {
    emit('search', search.value.state.query);
    search.value
        .clearRefinements()
        .search()
    logSearch();
}

</script>
