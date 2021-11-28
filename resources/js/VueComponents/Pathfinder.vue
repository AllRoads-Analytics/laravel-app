<template>
    <div>
        <div v-show="previous_pages.length > 0" class="mb-3">
            <funnel
                :pixel_id="pixel_id"
                :host="host"
                :pages="previous_pages"
                @removePage="removePreviousPage"
            ></funnel>
        </div>

        <div>
            <next-pages
                :pixel_id="pixel_id"
                :host="host"
                :previous_pages="previous_pages"
                @addPreviousPage="addPreviousPage"
            ></next-pages>
        </div>
    </div>
</template>

<script>
import Funnel from './Pathfinder/Funnel.vue';
import NextPages from './Pathfinder/NextPages.vue';

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
            previous_pages: [],
        };
    },

    methods: {
        addPreviousPage(page) {
            this.previous_pages.push(page);
            this.updateUrl();
        },

        removePreviousPage(page) {
            _.pull(this.previous_pages, page);
            this.updateUrl();
        },

        updateUrl() {
            const url = new URL(window.location);

            url.searchParams.delete('previous_pages');

            this.previous_pages.forEach(page => {
                url.searchParams.append('previous_pages', page);
            });

            window.history.pushState({}, '', url);
        }
    },

    beforeMount() {
        const parsed = queryString.parse(location.search);
        const previous_pages = parsed ? (parsed.previous_pages ?? [] ) : [];
        this.previous_pages = Array.isArray(previous_pages) ? previous_pages : [ previous_pages ];
    },
}
</script>
