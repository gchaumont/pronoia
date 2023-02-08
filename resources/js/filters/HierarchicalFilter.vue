<template>
	<ul>	
		<li v-for="value in values" class="mb-2 ">
			<span class="dark:text-white ">{{value.label}}</span>
			{{value.children}}
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
    level: {
    	type: Number, 
    	required: false, 
    	default: 0
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
