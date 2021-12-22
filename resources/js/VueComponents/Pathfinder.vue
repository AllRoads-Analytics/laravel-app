<template>
    <div>
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
        <div v-show="filters.previous_pages.length > 0" class="mb-3">
            <funnel
                :pixel_id="pixel_id"
                :host="host"
                :filters="filters"
                @removePage="removePreviousPage"
            ></funnel>
        </div>

        <!-- Next pages -->
        <div>
            <next-pages
                :pixel_id="pixel_id"
                :host="host"
                :filters="filters"
                @addPreviousPage="addPreviousPage"
            ></next-pages>
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
            },
        };
    },

    methods: {
        addPreviousPage(page) {
            this.filters.previous_pages.push(page);
            this.updateUrl();
        },

        removePreviousPage(page) {
            _.pull(this.filters.previous_pages, page);
            this.updateUrl();
        },

        updateUrl() {
            const url = new URL(window.location);

            url.searchParams.delete('previous_pages');

            this.filters.previous_pages.forEach(page => {
                url.searchParams.append('previous_pages', page);
            });

            window.history.pushState({}, '', url);
        },

        parseUrl() {
            const parsed = queryString.parse(location.search);
            const previous_pages = parsed ? (parsed.previous_pages ?? [] ) : [];
            this.filters.previous_pages = Array.isArray(previous_pages) ? previous_pages : [ previous_pages ];
        }
    },

    beforeMount() {
        this.parseUrl();

        const end = new Date();
        const start = date.addMonths(end, -1);

        this.filters.start_date = date.format(start, 'YYYY-MM-DD');
        this.filters.end_date = date.format(end, 'YYYY-MM-DD');

        window.onpopstate = this.parseUrl;
    },
}
</script>
