<template>
    <div>
        <div v-if="loading">
            Loading...
        </div>

        <div v-else>
            <div class="d-flex flex-wrap p-1">
                <div class="p-1" v-for="page, idx in page_views" :key="idx">
                    <div class="card">
                        <div class="card-body">
                            <p class="fw-bold">{{ idx + 1 }}</p>
                            <p>Page: {{ page.page }}</p>
                            <p>Views: {{ page.views }}</p>

                            <button type="button" class="btn btn-link" @click="$emit('removePage', page.page)">
                                Remove
                            </button>

                            <!-- <a href="{{ route('pathfinder.tracker.host', [
                                'tracker_pixel_id' => $Tracker->pixel_id,
                                'host' => $host,
                                'previous_pages' => array_values(
                                        Arr::where( $previous_pages, fn($_page) => ( $_page !== $page_views['page'] ) )
                                    ),
                            ]) }}">
                                Remove
                            </a> -->
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
        pages: Array,
    },

    data() {
        return {
            loading: true,
            page_views: [],
        };
    },

    methods: {
        update() {
            if (0 === this.pages.length) {
                this.page_views = [];
                this.loading = false;
                return;
            }

            this.loading = true;

            Axios.get(route('pathfinder.ajax.get_funnel', {
                tracker_pixel_id: this.pixel_id,
                host: this.host,
                pages: this.pages,
            })).then( (response) => {
                this.page_views = response.data.page_views;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
            });
        }
    },

    watch: {
        pages: {
            deep: true,
            handler() {
                this.update();
            }
        }
    },

    mounted() {
        this.update();
    },
}
</script>
