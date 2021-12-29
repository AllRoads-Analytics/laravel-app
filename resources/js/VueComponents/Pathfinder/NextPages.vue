<template>
    <div>
        <div class="row justify-content-center mt-2">
            <h5 class="col-lg-8 m-0">
                Select {{ first ? 'starting' : 'next' }} page
            </h5>
        </div>

        <div class="row justify-content-center my-1">
            <div class="col-lg-8">
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

        <div class="row justify-content-center my-1">
            <div class="col-lg-8">
                <div>
                    <table class="table">
                        <!-- <thead>
                            <tr>
                                <th scope="col">
                                    Select {{ first ? 'starting' : 'next' }} page
                                </th>

                                <th scope="col">Views</th>
                            </tr>
                        </thead> -->

                        <tbody v-show=" ! loading">
                            <tr v-for="path, idx in next_pages" :key="idx" >
                                <td>
                                    <button type="button" class="btn btn-link text-start"
                                    @click="$emit('addPreviousPage', path.path)">
                                        {{ path.path }}
                                    </button>
                                </td>

                                <td class="text-end">
                                    {{ path.views }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-show="loading" class="mx-3 text-secondary">
                    <i>Loading...</i>
                </div>

                <div v-show=" ! loading && next_pages.length < 1 && this.page === 0" class="mx-3 mb-2">
                    <i>No subsequent pages.</i>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-1" v-show=" ! loading">
            <div class="col-lg-8">
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
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
</template>

<script>
export default {
    props: {
        pixel_id: String,
        host: String,
        filters: Object,
        ready: Boolean,
    },

    data() {
        return {
            loading: true,
            next_pages: [],
            first: false,
            page: 0,
            page_size: 0,
            search_term: '',
        };
    },

    methods: {
        update() {
            this.loading = true;

            Axios.get( route('pathfinder.ajax.get_next_pages', {
                tracker: this.pixel_id,
                host: this.host,
                previous_pages: this.filters.previous_pages,
                start_date: this.filters.start_date,
                end_date: this.filters.end_date,
                page: this.page,
                search: this.search_term,
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
