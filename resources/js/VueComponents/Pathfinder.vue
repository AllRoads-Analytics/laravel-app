<template>
    <div>
        <!-- Filters -->
        <div class="mb-3 form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="start_date">Start</label>
                    <input type="date" class="form-control" id="start_date"
                    @change="setStartDate"
                    v-model="input_start_date">
                </div>

                <div class="col-md-3">
                    <label for="start_date">End</label>
                    <input type="date" class="form-control" id="end_date"
                    @change="setEndDate"
                    v-model="input_end_date">
                </div>

                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-header">
                            <button @click="filters_open = ! filters_open" class="btn-plain w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-filter"></i>
                                        Filters
                                    </div>

                                    <div>
                                        <span v-show="!filters_open" v-cloak><i class="fas fa-chevron-down"></i></span>
                                        <span v-show="filters_open"><i class="fas fa-chevron-up"></i></span>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <div class="card-body"
                        v-cloak v-show="filters_open">
                            <div class="form">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="filter_field">Field</label>

                                        <select name="filter_field" id="filter_field" class="form-select"
                                        v-model="selected_filter">
                                            <option value="">-- Select --</option>

                                            <option v-for="filter_option, idx in filter_options" :key="idx"
                                            :value="filter_option.key">
                                                {{ filter_option.label }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6" v-if="selected_filter">
                                        <label for="filter_option">Value</label>

                                        <select name="filter_option" id="filter_option" class="form-select"
                                        v-model="selected_filter_option"
                                        @change="addActiveFilter">
                                            <option value="">-- Select --</option>

                                            <option v-for="option, idx in active_filter_option.options" :key="idx"
                                            :value="option">
                                                {{ option }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid g-2 mt-2">
                                    <div class="g-col" v-for="value, field in filters_secondary" :key="field+value">
                                        <button class="btn-plain" @click="removeFilter(field)">
                                            <span class="badge bg-secondary">
                                                <b>{{ field }}:</b> {{ value }}
                                                &nbsp;
                                                <i class="fas fa-times"></i>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Funnel -->
        <div v-show="filters.previous_pages.length > 0" class="mb-5">
            <div class="row mb-3">
                <div class="col">
                    <div class="row g-2 align-items-center justify-content-between">
                        <div class="col-md">
                            <h4 class="mb-1">
                                {{ funnel_id ? '' : 'New' }}
                                Funnel
                            </h4>

                            <h5 class="m-0" v-if="!editing"><i>{{ funnel_name ? funnel_name : '' }}</i></h5>

                            <input type="text" class="form-control"
                            style="min-width: 40vw;"
                            v-model="input_funnel_name"
                            v-if="editing">
                        </div>

                        <div class="col-md text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <div class="mr-2" v-if="funnel_id">
                                    <button type="button" class="btn btn-primary"
                                    v-show="!editing"
                                    @click="editing = true">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <button type="button" class="btn btn-link"
                                    v-show="editing"
                                    @click="editing = false">
                                        Cancel
                                    </button>
                                </div>

                                <div class="">
                                    <button type="button" class="btn btn-success"
                                    v-show="editing"
                                    @click="saveFunnel">
                                        <i class="fas fa-save"></i>
                                        Save
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-2" v-show="editing && funnel_id">
                                <div class="col">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
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
                :filters="filters_all"
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
                    :filters="filters_all"
                    :options_hostname="options_hostname"
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

// import { reactive } from "vue";

const queryString = require('query-string');

const endDate = new Date();
const startDate = date.addMonths(endDate, -1);

const startDateString = date.format(startDate, 'YYYY-MM-DD');
const endDateString = date.format(endDate, 'YYYY-MM-DD');

export default {
    components: [ NextPages, Funnel ],

    props: {
        pixel_id: String,
    },

    // setup() {
    //     let filters_secondary = reactive({});

    //     return {
    //         filters_secondary
    //     };
    // },

    data() {
        return {
            filters: {
                previous_pages: [],
                start_date: startDateString,
                end_date: endDateString,
                ready: false,
            },

            filters_secondary: {},

            editing: false,
            funnel_id: null,
            funnel_name: null,

            input_start_date: startDateString,
            input_end_date: endDateString,
            input_funnel_name: '',
            filters_open: false,

            filter_options: [],
            selected_filter: '',
            selected_filter_option: '',
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

        addActiveFilter() {
            this.filters_secondary[this.selected_filter] = this.selected_filter_option;

            this.selected_filter = this.selected_filter_option = '';
        },

        removeFilter(key) {
            delete this.filters_secondary[key];
        },

        setStartDate() {
            if (HELPER.isValidDate(this.input_start_date)) {
                this.filters.start_date = this.input_start_date;
            }
        },

        setEndDate() {
            if (HELPER.isValidDate(this.input_end_date)) {
                this.filters.end_date = this.input_end_date;
            }
        },

        // updateUrl() {
        //     const url = new URL(window.location);

        //     url.searchParams.delete('previous_pages');

        //     this.filters.previous_pages.forEach(page => {
        //         url.searchParams.append('previous_pages', page);
        //     });

        //     window.history.pushState({}, '', url);
        // },

        updateFilterOptions() {
            Axios.get( route('pathfinder.ajax.get_filter_options', {
                tracker: this.pixel_id,
                start_date: this.filters.start_date,
                end_date: this.filters.end_date,
            })).then( (response) => {
                this.filter_options = response.data.filter_options;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong (filter options).');
            }).then( () => {
                //
            });
        },

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
                    this.input_funnel_name = this.funnel_name;
                    this.organization_id = response.data.organization_id;
                    this.filters.ready = true;
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
                pages: this.filters.previous_pages,
                name: this.input_funnel_name,
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

    computed: {
        from_date: function() {
            return this.filters.start_date;
        },
        to_date: function() {
            return this.filters.end_date;
        },

        active_filter_option: function() {
            if (this.selected_filter) {
                return _.find(this.filter_options, (filter_option) => {
                    return filter_option.key === this.selected_filter;
                });
            } else {
                return null;
            }
        },

        filters_all: function() {
            return {
                ...this.filters,
                ...this.filters_secondary,
            };
        },

        options_hostname: function() {
            const FilterOption = _.find(this.filter_options, (_option) => {
                return 'host' === _option.key;
            });

            return FilterOption ? _.toArray(FilterOption.options) : [];
        },
    },


    watch: {
        from_date: function() {
            this.updateFilterOptions();
        },
        to_date: function() {
            this.updateFilterOptions();
        },
    },

    beforeMount() {
        this.parseUrl();
        this.updateFilterOptions();

        // const end = new Date();
        // const start = date.addMonths(end, -1);

        // this.filters.start_date = date.format(start, 'YYYY-MM-DD');
        // this.filters.end_date = date.format(end, 'YYYY-MM-DD');

        if ( ! this.funnel_id) {
            this.editing = true;
        }

        window.onpopstate = this.parseUrl;
    },
}
</script>
