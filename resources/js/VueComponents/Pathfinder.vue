<template>
    <div>
        <!-- Filters -->
        <div class="mb-3 form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <div class="row">
                        <label for="start_date" class="col-2 col-md-12 col-form-label">Start</label>
                        <div class="col-10 col-md-12">
                            <input type="date" class="form-control" id="start_date"
                            :min="input_min_date" :max="input_end_date"
                            @change="setStartDate"
                            v-model="input_start_date">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="row">
                        <label for="start_date" class="col-2 col-md-12 col-form-label">End</label>

                        <div class="col-10 col-md-12">
                            <input type="date" class="form-control" id="end_date"
                            :min="input_min_date" :max="input_end_date"
                            @change="setEndDate"
                            v-model="input_end_date">
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white text-dark">
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

                                <div class="d-flex flex-wrap g-2 mt-2">
                                    <div class="" v-for="value, field in filters_secondary" :key="field+value">
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
        <div v-show="filters.previous_steps.length > 0" class="mb-3">
            <div class="row mb-3 mb-md-1">
                <div class="col">
                    <div class="row g-3 align-items-center justify-content-between">
                        <div class="col-md">
                            <h4 class="mb-1">
                                {{ funnel_id ? 'Saved Funnel' : 'New Funnel' }}
                            </h4>

                            <h5 class="m-0" v-if="!editing">
                                <div v-show="funnel_name">
                                    <i class="fas fa-save me-1 text-secondary"></i>
                                    <i>{{ funnel_name }}</i>
                                </div>
                            </h5>

                            <input type="text" class="form-control"
                            style="min-width: 40vw;"
                            v-model="input_funnel_name"
                            v-on:keyup.enter="saveFunnel"
                            v-if="editing">
                        </div>

                        <div class="col-md text-md-end">
                            <div class="d-flex align-items-center justify-content-md-end">
                                <div class="mr-2" v-if="funnel_id">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                    v-show="!editing"
                                    @click="editing = true">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <a class="btn btn-outline-success btn-sm ms-2"
                                    :href="window_location"
                                    v-show="!editing">
                                        <i class="fas fa-compass me-1"></i>
                                        New Funnel Explorer
                                    </a>

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
                @removeStep="removePreviousStep"
            ></funnel>
        </div>

        <!-- Next pages -->
        <div v-if="editing" class="row justify-content-center">
            <div class="col-lg-8">
                <next-pages
                    :pixel_id="pixel_id"
                    :filters="filters_all"
                    :options_hostname="options_hostname"
                    @addPreviousStep="addPreviousStep"
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
    components: [ NextPages, Funnel ],

    props: {
        pixel_id: String,
        view_days: String,
    },

    data() {
        return {
            filters: {
                previous_steps: [],
                start_date: null,
                end_date: null,
                ready: false,
            },

            filters_secondary: {},

            editing: false,
            funnel_id: null,
            funnel_name: null,

            input_start_date: null,
            input_end_date: null,
            input_min_date: null,
            input_funnel_name: '',
            filters_open: false,

            filter_options: [],
            selected_filter: '',
            selected_filter_option: '',

            window_location: window.location.pathname,
        };
    },

    methods: {
        addPreviousStep(step) {
            this.filters.previous_steps.push(step);
            // this.updateUrl();
        },

        removePreviousStep(step_idx) {
            this.filters.previous_steps.splice(step_idx, 1);
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

        //     url.searchParams.delete('previous_steps');

        //     this.filters.previous_steps.forEach(page => {
        //         url.searchParams.append('previous_steps', page);
        //     });

        //     window.history.pushState({}, '', url);
        // },

        updateFilterOptions() {
            if ( ! (this.filters.start_date && this.filters.end_date) ) {
                return;
            }

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

            // const previous_steps = parsed ? (parsed.previous_steps ?? [] ) : [];
            // this.filters.previous_steps = Array.isArray(previous_steps) ? previous_steps : [ previous_steps ];

            this.funnel_id = parsed ? (parsed.funnel ?? null) : null;

            if (this.funnel_id) {
                Axios.get( route('pathfinder.ajax.get_saved_funnel_steps', {
                    funnel: this.funnel_id
                })).then( (response) => {
                    this.filters.previous_steps = response.data.steps;
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
            if ( ! this.input_funnel_name) {
                return alert('Name required.');
            }

            Axios.post( route('pathfinder.ajax.post_funnel', {
                id: this.funnel_id,
                tracker: this.pixel_id,
                steps: this.filters.previous_steps,
                name: this.input_funnel_name,
            })).then( (response) => {
                this.funnel_id = response.data.Funnel.id;
                this.funnel_name = response.data.Funnel.name;
                this.organization_id = response.data.Funnel.organization_id;
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
                window.alert('Something went wrong. (delete funnel)');
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
        const view_days_int = parseInt(this.view_days);

        const endDate = new Date();
        const minDate = date.addDays(endDate, -1 * view_days_int);
        const startDate = view_days_int > 30 ? date.addDays(endDate, -1 * 30) : minDate;

        const startDateString = date.format(startDate, 'YYYY-MM-DD');
        const endDateString = date.format(endDate, 'YYYY-MM-DD');
        const minDateString = date.format(minDate, 'YYYY-MM-DD');

        this.input_start_date = startDateString;
        this.input_end_date = endDateString;
        this.input_min_date = minDateString;
        this.filters.start_date = startDateString;
        this.filters.end_date = endDateString;

        this.parseUrl();

        if ( ! this.funnel_id) {
            this.editing = true;
        }

        window.onpopstate = this.parseUrl;
    },
}
</script>
