import { provide, reactive } from 'vue'
import Search from './instantsearch.js'

export function useSearch(target, callback = null, name = "resourceSearch") {

    const searchInstance =  reactive(new Search(target, callback));

    provideSearch(searchInstance, name || 'resourceSearch')

    return { searchInstance };
}

export function provideSearch(instance, name = 'resourceSearch') {
    provide(name, instance);
}

