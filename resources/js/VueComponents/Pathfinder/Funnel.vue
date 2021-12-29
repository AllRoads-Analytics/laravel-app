<template>
    <div ref="loader" class="vld-parent">
        <div class="row mt-2">
            <div class="col">
                <div class="d-flex flex-wrap">
                    <div class="p-1">
                        <span class="badge bg-warning text-dark fs-6">
                            {{ visitors_count }} Visitors
                        </span>
                    </div>

                    <div class="p-1">
                        <span class="badge bg-warning text-dark fs-6">
                            {{ convertors_count }} Convertors
                        </span>
                    </div>

                    <div class="p-1">
                        <span class="badge bg-warning text-dark fs-6">
                            {{ conversion_percentage }}% Conversion Rate
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div style="min-height: 100px;">
                <div class="d-flex flex-wrap">
                    <div class="col-12 col-md-6 col-lg-3" v-for="page, idx in page_views" :key="idx">
                        <div class="p-1 w-100">
                            <div class="card">
                                <div class="card-body">
                                    <div class="fw-bold d-flex align-items-center">
                                        <div class="badge bg-dark me-3 fs-6">{{ idx + 1 }}</div>
                                        <div>{{ page.page }}</div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="progress" style="height: 40px;">
                                            <div class="progress-bar progress-bar-striped bg-success"
                                            :style="'width: ' + page.percentage + '%'"
                                            aria-valuenow="page.percentage" aria-valuemin="0" aria-valuemax="100">
                                                <!-- <span class="fs-6 fw-bold">
                                                    {{ page.percentage }}%
                                                </span> -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-1 text-center">
                                        <span class="fs-3 fw-bold">
                                            {{ page.percentage }}%
                                        </span>
                                    </div>

                                    <div class="mt-2">
                                        {{ page.views }} visitor{{ page.views > 1 ? 's' : '' }}.
                                    </div>

                                    <div v-if="page.proceeded !== null">
                                        <div class="mt-1 text-danger">
                                            {{ page.dropped }}
                                            <span class="">
                                                ({{ page.step_dropped_percentage }}%)
                                            </span>
                                            dropped.
                                        </div>

                                        <div class="mt-1 text-success">
                                            {{ page.proceeded }}
                                            <span class="">
                                                ({{ page.step_proceeded_percentage }}%)
                                            </span>
                                            proceeded.
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-danger mt-3"
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
