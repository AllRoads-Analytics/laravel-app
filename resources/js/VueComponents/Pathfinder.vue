<template>
    <div>
        <!-- Filters -->
        <div class="mb-3 form">
            <div class="row g-3">
                <div class="col">
                    <label for="start_date">Start</label>
                    <input type="date" class="form-control" id="start_date"
                    v-model="filters.start_date">
                </div>

                <div class="col">
                    <label for="start_date">End</label>
                    <input type="date" class="form-control" id="end_date"
                    v-model="filters.end_date">
                </div>
            </div>
        </div>

        <hr>

        <!-- Funnel -->
        <div v-show="filters.previous_pages.length > 0" class="mb-5">
            <div class="row mb-2">
                <div class="col">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1">
                                {{ funnel_id ? '' : 'New' }}
                                Funnel
                            </h4>

                            <h5><i>{{ funnel_name ? funnel_name : '' }}</i></h5>
                        </div>

                        <div>
                            <div class="row gx-2">
                                <div class="col" v-if="funnel_id">
                                    <button type="button" class="btn btn-primary"
                                    v-show="!editing"
                                    @click="editing = true">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-link"
                                    v-show="editing"
                                    @click="editing = false">
                                        Cancel
                                    </button>
                                </div>

                                <div class="col">
                                    <button type="button" class="btn btn-success"
                                    v-show="editing"
                                    @click="saveFunnel">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-2" v-show="editing && funnel_id">
                                <div class="col">
                                    <button type="button" class="btn btn-sm btn-danger"
                                    @click="deleteFunnel">
                                        <i class="fas fa-trash"></i>
                                        Delete this Funnel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <funnel
                :pixel_id="pixel_id"
                :host="host"
                :filters="filters"
                :init_funnel_id="funnel_id"
                :editing="editing"
                @removePage="removePreviousPage"
            ></funnel>
        </div>

        <!-- Next pages -->
        <div v-if="editing" class="row justify-content-center">
            <div class="col-lg-8">
                <next-pages
                    :pixel_id="pixel_id"
                    :host="host"
                    :filters="filters"
                    @addPreviousPage="addPreviousPage"
                ></next-pages>
            </div>
        </div>
    </div>
</template>

<script>
import Funnel from './Pathfinder/Funnel.vue';
import NextPages from './Pathfinder/NextPages.vue';

import date from 'date-and-time';

const queryString = require('query-string');

export default {
    components: { Funnel },
    components: [ NextPages, Funnel ],

    props: {
        pixel_id: String,
        host: String,
    },

    data() {
        return {
            filters: {
                previous_pages: [],
                start_date: '',
                end_date: '',
                ready: false,
            },

            editing: false,
            funnel_id: null,
            funnel_name: null,
        };
    },

    methods: {
        addPreviousPage(page) {
            this.filters.previous_pages.push(page);
            // this.updateUrl();
        },

        removePreviousPage(page) {
            _.pull(this.filters.previous_pages, page);
            // this.updateUrl();
        },

        // updateUrl() {
        //     const url = new URL(window.location);

        //     url.searchParams.delete('previous_pages');

        //     this.filters.previous_pages.forEach(page => {
        //         url.searchParams.append('previous_pages', page);
        //     });

        //     window.history.pushState({}, '', url);
        // },

        parseUrl() {
            const parsed = queryString.parse(location.search);

            // const previous_pages = parsed ? (parsed.previous_pages ?? [] ) : [];
            // this.filters.previous_pages = Array.isArray(previous_pages) ? previous_pages : [ previous_pages ];

            this.funnel_id = parsed ? (parsed.funnel ?? null) : null;

            if (this.funnel_id) {
                Axios.get( route('pathfinder.ajax.get_saved_funnel_pages', {
                    funnel: this.funnel_id
                })).then( (response) => {
                    this.filters.previous_pages = response.data.pages;
                    this.funnel_name = response.data.name;
                    this.filters.ready = true;
                    this.organization_id = response.data.organization_id;
                }).catch( (error) => {
                    console.log(error);
                    window.alert('Something went wrong (saved funnel).');
                }).then( () => {
                    //
                });
            } else {
                this.filters.ready = true;
            }
        },

        saveFunnel() {
            // let loader = this.$loading.show({
            //     container: this.$refs.loader,
            //     backgroundColor: '#f8fafc',
            // });

            Axios.post( route('pathfinder.ajax.post_funnel', {
                id: this.funnel_id,
                tracker: this.pixel_id,
                host: this.host,
                pages: this.filters.previous_pages,
                name: 'foo', // todo
            })).then( (response) => {
                this.funnel_id = response.data.Funnel.id;
                this.funnel_name = response.data.Funnel.name;
                this.editing = false;

                const searchParams = new URLSearchParams(window.location.search);
                searchParams.set('funnel', this.funnel_id);
                const newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
                window.history.pushState({}, '', newRelativePathQuery);
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
                // loader.hide();
            });
        },

        deleteFunnel() {
            if ( ! window.confirm('Are you sure you would like to PERMANENTLY delete this Funnel?')) {
                return;
            }

            Axios.post( route('pathfinder.ajax.post_funnel_delete', {
                id: this.funnel_id,
            })).then( (response) => {
                window.location.replace(route('funnels.index', {organization: this.organization_id}));
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
                // loader.hide();
            });
        },
    },

    beforeMount() {
        this.parseUrl();

        const end = new Date();
        const start = date.addMonths(end, -1);

        this.filters.start_date = date.format(start, 'YYYY-MM-DD');
        this.filters.end_date = date.format(end, 'YYYY-MM-DD');

        if ( ! this.funnel_id) {
            this.editing = true;
        }

        window.onpopstate = this.parseUrl;
    },
}
</script>
