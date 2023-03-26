import HttpClient from "@/HttpClient" // TODO remove this

export default class Search {

    constructor(endpoint, resultsCallback) {
        this.endpoint = endpoint;
        this.resultsCallback = resultsCallback;
        this.updateCallback = null;
        this.searchCallback = null;
        this.updateEvent = debounce(this.onUpdate, 1000);
        var initialState = {
            _language: window.App?.language ?? null,
            query: '',
            page: 1,
            hitsPerPage: 20,
            sortBy: null,
            facets: new Set,
            suggestions: new Set,
            filters: [],
            facetsRefinements: {}, // id's or values of desired facet
            facetFilters: [],
        };

        this.hasMore = false;
        this.state = initialState
        this.lastResponseTime = Date.now();
        this.isLoading = false;
        this.results = {
            queryString: '',
            hits: [],
            newHits: [],
            metrics: {},
            total: null,
            facets: new Set,
            suggestions: {},
            processingTime: null,
            suggest: {},
            hasMore: false,
            pages: null,
            meta: {},
            sortOptions: []
        }
        this.initialiseFromUrl();
    }

    onUpdate(state) {
        // var url = [location.protocol, '//', location.host, '/' + window.App.language, '/products'].join('');
        var queryString = '?' + this.toQueryString();

        // window.history.replaceState(state, '', url+queryString);
        // window.history.pushState(state, '', url + queryString);
        
        if (this.updateCallback) {
            this.updateCallback(this)
        }
    }

    //    // overridable
    // onUpdate() {}

    withUpdateCallback(callback) {
        this.updateCallback = callback;

        return this
    }

     search() {
        return new Promise((resolve, reject) => {
            this.isLoading = true
            var requestTime = Date.now();
            var queryString = this.toQueryString()
            if (this.state.page == 1) {
                this.results.hits = [];
            }

            if (this.searchCallback && queryString != this.results.queryString) {
                this.setPage(1)
                this.searchCallback(this)
            }

            this.results.queryString = queryString;

            HttpClient.get(this.endpoint, {
                params: Object.assign({
                    search: this.state.query,
                    page: this.state.page,
                    hitsPerPage: this.state.hitsPerPage,
                    sort: this.state.sortBy,
                    facets: [...this.state.facets].join(','),
                    suggest: [...this.state.suggestions].join(','),
                }, 
                this.state.facetsRefinements,
                this.state.filters
                ),
            }).then(response => {
                this.isLoading = false

                if (requestTime < this.lastResponseTime) {
                    return;
                } else {
                    this.lastResponseTime = requestTime;
                }

                if (this.state.page === 1) {
                    this.results.hits = response.data.data
                    this.results.metrics = response.data.meta?.aggregations?.metrics || []

                    // window.scrollTo(0, 0);
                } else {
                    this.results.hits.push(...response.data.data);
                }
                if (this.resultsCallback) {
                    this.resultsCallback(response.data.data)
                }
                this.results.suggest = response.data?.meta?.suggest
                this.results.sortOptions = response.data?.meta?.sortOptions
                this.results.defaultSort = response.data?.meta?.defaultSort
                this.results.newHits = response.data.data;
                this.results.facets = response.data.meta?.facets
                this.results.suggestions = response.data.meta?.suggestions
                this.results.total = response.data.meta?.total

                this.results.hasMore = this.hasMore = response.data.meta?.total > this.results.hits.length;
                this.results.pages = response.data.meta?.pages
                this.results.meta = response.data.meta
                resolve(this.results)
                return this.results;
            })
            // .catch(error => {
            //     console.log(error);
            //     this.isLoading = false;
            // })

            // return this.results;
        })
    }

    sortBy(sort) {
        this.state.sortBy = sort;
        this.state.page = 1;
        return this
    }

    setQuery(query) {
        this.state.query = query;
        this.state.page = 1;
        return this;
    }

    addFilter(name, value) {
        this.state.filters[name] = value;

        return this

    }

    addFacetRefinement(facet, value) {
        this.state.facetsRefinements[facet] = value;
        this.updateEvent(this.state);
        return this;
    }

    hasFacetRefinement(facet, value) {
        return this.state.facetsRefinements[facet] == value;
        // return this.state.facetsRefinements?.hasOwnProperty(facet) && this.state.facetsRefinements[facet] == value;
    }

    getFacetRefinement(facet) {
        return this.state.facetsRefinements[facet];
    }

    toggleFacetRefinement(facet, value) {
        if (this.state.facetsRefinements.hasOwnProperty(facet) && this.state.facetsRefinements[facet] == value) {

            delete this.state.facetsRefinements[facet];
        } else {
            this.state.facetsRefinements[facet] = value;
        }
        // if (this.state.facetsRefinements.hasOwnProperty(facet) && this.state.facetsRefinements[facet] !== null) {
        //     delete this.state.facetsRefinements[facet];
        // } else {
        //     this.state.facetsRefinements[facet] = value;
        // }
        this.updateEvent(this.state);
        return this;
    }

    addFacet(facet) {
        this.state.facets.add(facet)

        return this;
    }


    addFacets(facets) {
        facets.forEach(f => this.state.facets.add(f))

        return this
    }

    addSuggestions(facets) {
        facets.forEach(f => this.state.suggestions.add(f))

        return this   
    }

    setPerPage(count) {
        this.state.hitsPerPage = count
        this.state.page = 1
        return this
    }

    removeFacet(facet) {
        this.state.facets.delete(facet);

        return this;
    }

    toggleFacet(facet) {
        if (this.state.facets.has(facet)) {
            this.removeFacet(facet)
        } else {
            this.addFacet(facet)
        }

        return this;
    }

    removeFacetRefinement(facet) {
        delete this.state.facetsRefinements[facet];
        this.updateEvent(this.state);

        return this
    }

    addNumericRefinement(attribute, operator, value) {

    }
    removeNumericRefinement(attribute, operator, value) {

    }

    clearRefinements(facet) {
        this.state.facetsRefinements = {};

        return this
    }

    getFacetValues(facet) {
        return this.results.facets[facet]
    }

    getPage() {
        return this.state.page;
    }
    setPage(page) {
        this.state.page = page;
        return this;
    }
    nextPage() {
        this.state.page++;
        return this;
    }

    setLanguage(language) {
        this.state._language = language;
    }

    initialiseFromUrl() {
        var params = new URLSearchParams(window.location.search);

        if (params.has('search')) {
            this.state.query = params.get('search') || '';
            params.delete('search')
            // this.search();
        } else {
            this.state.query = null
        }

        if (params.has('sort')) {
            this.state.sortBy = params.get('sort');
            params.delete('sort')
        } else {
            this.state.sortBy = null
        }

        this.state.facetsRefinements = {}
        for (const [key, value] of params.entries()) {
            this.addFacetRefinement(key, value);
        }

        return this.setPage(1);
    }
    toQueryParams() {
        var urlParams = {};

        if (this.state.query) {
            urlParams.search = this.state.query;
        }

        // if (this.state.facets.length) {
        //     urlParams.set('facets', this.state.facets.join(','));
        // }

        if (this.state.sortBy) {
            urlParams.sort = this.state.sortBy;
        }

        for (const [key, value] of Object.entries(this.state.facetsRefinements)) {
            if (value) {
                urlParams[key] = value;
            }
        }
        return urlParams;
    }
    toQueryString() {
        var params = new URLSearchParams();

        for (const [key, value] of Object.entries(this.toQueryParams())) {
            params.set(key, value);
        }

        return '?' + params.toString();
    }
    onSearch(callback) {
        this.searchCallback = callback;

        return this
    }

}
