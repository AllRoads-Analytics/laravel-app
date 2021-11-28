<template>
    <div>
        <div ref="loader" class="vld-parent" style="min-height: 100px;">
            <div class="d-flex flex-wrap p-1">
                <div class="p-1" v-for="page, idx in page_views" :key="idx">
                    <div class="card">
                        <div class="card-body">
                            <p class="fw-bold">{{ idx + 1 }}</p>
                            <p>Page: {{ page.page }}</p>
                            <p>Views: {{ page.views }}</p>

                            <button type="button" class="btn btn-sm btn-outline-danger" @click="$emit('removePage', page.page)">
                                X
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
        host: String,
        pages: Array,
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
            if (0 === this.pages.length) {
                this.page_views = [];
                this.loading = false;
                return;
            }

            let loader = this.$loading.show({
                container: this.$refs.loader,
            });

            Axios.get( route('pathfinder.ajax.get_funnel', {
                tracker: this.pixel_id,
                host: this.host,
                pages: this.pages,
            })).then( (response) => {
                this.page_views = response.data.page_views;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                loader.hide();
            });
        }
    },

    watch: {
        pages: {
            deep: true,
            immediate: true,
            handler(_pages) {
                // this.show = _pages.length > 0;
                this.update();
            }
        }
    },
}
</script>
