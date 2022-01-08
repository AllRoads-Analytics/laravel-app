<template>
    <div class="card">
        <div class="card-header bg-info text-black p-3">
            <h5 class=" m-0">
                Select {{ first ? 'starting' : 'next' }} page:
            </h5>

            <div class="row mt-2 mb-1 g-2">
                <div class="col-lg-6">
                    <select name="host" id="host" class="form-select"
                    v-model="selected_hostname"
                    @change="update">
                        <option value="">-- Filter Hostname --</option>

                        <option v-for="option, idx in options_hostname" :key="idx"
                        :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search"
                        ref="search_input"
                        v-debounce:500="searchMe">

                        <button class="btn btn-outline-secondary" type="button"
                        @click="clearSearch">
                            <i class="fas fa-eraser"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pb-3 pt-1 px-0">
            <div class="row justify-content-center my-1">
                <div class="col">
                    <div v-show=" ! loading">
                        <div v-for="path, idx in next_pages" :key="idx">
                            <div class="p-2 px-md-4 border-bottom">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="flex-grow-1 text-break">
                                        <button type="button" class="btn btn-link text-start px-0"
                                        @click="$emit('addPreviousPage', path.host_path)"
                                        v-html="path.host_path">
                                        </button>
                                    </div>

                                    <div class="">
                                        {{ path.views }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-show="loading" class="mx-3 text-secondary mt-3">
                        <i>Loading...</i>
                    </div>

                    <div v-show=" ! loading && next_pages.length < 1 && this.page === 0" class="mx-3 mt-3 mb-2">
                        <i>No subsequent pages.</i>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-3" v-show=" ! loading">
                <div class="col">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-cente">
                            <li class="page-item" :class="page === 0 ? 'disabled' : ''">
                                <button class="page-link"
                                @click="decrementPage()">
                                    <span aria-hidden="true">&laquo;</span> Previous
                                </button>
                            </li>

                            <li class="page-item"
                            :class="next_pages.length < page_size ? 'disabled' : ''">
                                <button class="page-link"
                                @click="incrementPage()">
                                    Next <span aria-hidden="true">&raquo;</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        pixel_id: String,
        filters: Object,
        ready: Boolean,
        options_hostname: Array,
    },

    data() {
        return {
            loading: true,
            next_pages: [],
            first: false,
            page: 0,
            page_size: 0,
            search_term: '',
            selected_hostname: '',
        };
    },

    methods: {
        update() {
            this.loading = true;

            Axios.get( route('pathfinder.ajax.get_next_pages', {
                tracker: this.pixel_id,
                page: this.page,
                search: this.search_term,
                host: this.selected_hostname,
                ...this.filters,
            })).then( (response) => {
                this.next_pages = response.data.paths;
                this.page_size = response.data.page_size;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
            });
        },

        searchMe(search_term) {
            this.search_term = search_term;
            this.update();
        },

        clearSearch() {
            if (this.search_term !== '') {
                this.$refs.search_input.value = '';
                this.search_term = '';
                this.update();
            }
        },

        incrementPage() {
            this.page = this.page + 1;
            this.update();
        },

        decrementPage() {
            this.page = this.page - 1;
            this.update();
        },
    },

    watch: {
        filters: {
            deep: true,
            immediate: true,
            handler() {
                this.page = 0;
                if ('undefined' !== typeof this.$refs.search_input) {
                    this.$refs.search_input.value = '';
                }
                this.search_term = '';
                this.first = this.filters.previous_pages.length === 0;

                if (this.filters.ready) {
                    this.update();
                }
            }
        },
    },
}
</script>
