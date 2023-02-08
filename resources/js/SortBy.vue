<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton class="group inline-flex justify-center text-sm font-medium text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                {{currentSort.label}}
                <ChevronDownIcon class="-mr-1 ml-1 h-5 w-5 flex-shrink-0 text-gray-400  group-hover:text-gray-500 dark:group-hover:text-gray-200" aria-hidden="true" />
            </MenuButton>
        </div>
        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white  shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="py-1">
                    <MenuItem v-for="option in sortOptions" :key="option.value" v-slot="{ active }">
                    <a :href="option.href" :class="[search.state.sortBy == option.value ? 'font-medium text-gray-900' : 'text-gray-500', active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm']" @click="search.sortBy(option.value).search()">{{ option.label }}</a>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>
<script setup>
import { defineProps, inject, computed } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'
import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
} from '@headlessui/vue'

const props = defineProps({
    instance: {
        required: false, 
        type: [String, Object],
        default: 'resourceSearch'
    },
    options: {
        required: false,
        type: Object,
        default : null,
    }
})


const search = computed(() => typeof props.instance === 'string' ? inject(props.instance) : props.instance)

const sortOptions = computed(() => props.options || search.value.results.sortOptions)

const currentSort = computed(() => sortOptions.value.find((o) => o.value ==search.value.state?.sortBy) || sortOptions.value.find((o) => o.value ==search.value.results?.defaultSort)|| {label: 'Sort'})

</script>
