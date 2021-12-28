<template>
    <div>
        <div class="row mt-2">
            <div class="col">
                <div class="d-flex">
                    <div class="pe-3">
                        Visitors: {{ visitors_count }}
                    </div>

                    <div class="pe-3">
                        Convertors: {{ convertors_count }}
                    </div>

                    <div class="">
                        {{ conversion_percentage }}%
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div ref="loader" class="vld-parent" style="min-height: 100px;">
                <div class="d-flex flex-wrap p-1">
                    <div class="p-1" v-for="page, idx in page_views" :key="idx">
                        <div class="card">
                            <div class="card-body">
                                <p class="fw-bold">{{ idx + 1 }}</p>
                                <p>Page: {{ page.page }}</p>
                                <p>Views: {{ page.views }}</p>

                                <button type="button" class="btn btn-sm btn-outline-danger"
                                v-show="editing"
                                @click="$emit('removePage', page.page)">
                                    <i class="fas fa-minus-circle"></i>
                                </button>
                            </div>
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
        host: String,
        filters: Object,
        ready: Boolean,
        editing: Boolean,
    },

    data() {
        return {
            loading: true,
            page_views: [],
            // show: false,
        };
    },

    methods: {
        update() {
            if (0 === this.filters.previous_pages.length) {
                this.page_views = [];
                this.loading = false;
                return;
            }

            let loader = this.$loading.show({
                container: this.$refs.loader,
                backgroundColor: '#f8fafc',
            });

            Axios.get( route('pathfinder.ajax.get_funnel', {
                tracker: this.pixel_id,
                host: this.host,
                pages: this.filters.previous_pages,
                start_date: this.filters.start_date,
                end_date: this.filters.end_date,
            })).then( (response) => {
                this.page_views = response.data.page_views;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
                loader.hide();
            });
        },
    },

    computed: {
        visitors_count() {
            return this.page_views.length > 0 ?
                this.page_views[0].views : null;
        },

        convertors_count() {
            return this.page_views.length > 0 ?
                this.page_views[this.page_views.length - 1].views : null;
        },

        conversion_percentage() {
            return this.page_views.length > 0 ?
                Math.round((this.page_views[this.page_views.length - 1].views / this.page_views[0].views) * 100)
                : null;
        },
    },

    watch: {
        filters: {
            deep: true,
            immediate: true,
            handler(_pages) {
                // this.show = _pages.length > 0;
                if (this.filters.ready) {
                    this.update();
                }
            }
        }
    },
}
</script>
