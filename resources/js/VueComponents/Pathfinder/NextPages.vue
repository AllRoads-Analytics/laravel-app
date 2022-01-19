<template>
    <div class="card">
        <div class="card-header bg-info text-black p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fs-5">
                    Select {{ first ? 'starting' : 'next' }} step
                </div>

                <div>
                    <button class="btn btn-outline-secondary btn-sm"
                    @click="pages_mode = !pages_mode">
                        <div v-show="pages_mode">
                            <i class="fas fa-asterisk"></i>
                            Wildcard Step
                        </div>

                        <div v-show=" ! pages_mode">
                            <i class="fas fa-list-ol"></i>
                            URL List
                        </div>
                    </button>
                </div>
            </div>

            <div class="row mt-2 mb-1 g-2" v-show="pages_mode">
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
            <!-- Page list mode -->
            <div v-show="pages_mode">
                <div class="row justify-content-center my-1">
                    <div class="col">
                        <div v-show=" ! loading">
                            <div class="p-2 px-md-4 border-bottom fw-bold">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="flex-grow-1 text-break">
                                        <div class="text-start px-0">
                                            Page
                                        </div>
                                    </div>

                                    <div class="">
                                        Unique Visitors
                                    </div>
                                </div>
                            </div>

                            <div v-for="path, idx in next_pages" :key="idx">
                                <div class="p-2 px-md-4 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="flex-grow-1 text-break">
                                            <button type="button" class="btn btn-link text-start px-0"
                                            @click="addPreviousPage(path.host_path)"
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

            <!-- Custom URL mode -->
            <div v-show=" ! pages_mode">
                <div class="container">
                    <div class="my-1">
                        <div>
                            <label for="input_label" class="form-label">
                                Label
                            </label>

                            <input type="text" class="form-control" id="input_label" name="input_label"
                            v-model="input_label"
                            aria-describedby="labelHelp"
                            v-on:keyup.enter="addUrlLike">

                            <div id="labelHelp" class="form-text mt-1">
                                Name for step on funnel.
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="input_url_like" class="form-label">
                                Url Like
                            </label>

                            <input type="text" class="form-control" id="input_url_like" name="input_url_like"
                            aria-describedby="urlLikeHelp"
                            v-model="input_url_like"
                            v-on:keyup.enter="addUrlLike">

                            <div id="urlLikeHelp" class="form-text mt-1">
                                <div>
                                    Do not include protocol prefix (https://).
                                </div>

                                <div class="mt-1">Wildcards:</div>

                                <div>
                                    <ul>
                                        <li>A percent sign "%" matches any number of characters or bytes.</li>
                                        <li>An underscore "_" matches a single character or byte.</li>
                                        <li>You can escape "\", "_", or "%" using two backslashes. For example, "\\%".</li>
                                    </ul>
                                </div>

                                <div class="mt-1">
                                    Example:
                                </div>

                                <div>
                                    <b>www.example.com/shop/%</b> would match
                                    <b>www.example.com/shop/anything-here</b>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button class="btn btn-primary"
                            @click="addUrlLike">
                                Add Step to Funnel
                            </button>
                        </div>
                    </div>
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
        options_hostname: Object,
    },

    data() {
        return {
            loading: true,
            next_pages: [],
            first: false,
            page: 0,
            page_size: 0,
            search_term: '',
            input_url_like: '',
            input_label: '',
            selected_hostname: '',
            pages_mode: true,
        };
    },

    methods: {
        update() {
            this.loading = true;

            Axios.get( route('pathfinder.ajax.get_next_pages', {
                organization: this.pixel_id,
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
            this.page = 0;
            this.update();
        },

        clearSearch() {
            if (this.search_term !== '' || this.page !== 0) {
                this.$refs.search_input.value = '';
                this.search_term = '';
                this.page = 0;
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

        addPreviousPage(page) {
            this.$emit('addPreviousStep', {
                type: 'pageload_host_path' ,
                label: page,
                match_data: page,
            });
        },

        addUrlLike() {
            this.$emit('addPreviousStep', {
                type: 'pageload_host_path_like',
                label: this.input_label,
                match_data: this.input_url_like,
            });

            this.input_label = this.input_url_like = '';
            this.pages_mode = true;
        }
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
                this.first = this.filters.previous_steps.length === 0;

                if (this.filters.ready) {
                    this.update();
                }
            }
        },
    },
}
</script>
