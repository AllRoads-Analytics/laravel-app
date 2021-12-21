<template>
    <div>
        <div>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">
                            Select {{ first ? 'starting' : 'next' }} page
                        </th>

                        <th scope="col">Views</th>
                    </tr>
                </thead>

                <tbody v-show=" ! loading">
                    <tr v-for="path, idx in next_pages" :key="idx" >
                        <td>
                            <button type="button" class="btn btn-link"
                            @click="$emit('addPreviousPage', path.path)">
                                {{ path.path }}
                            </button>
                        </td>

                        <td>
                            {{ path.views }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-show="loading" class="mx-3 text-secondary">
            <i>Loading...</i>
        </div>

        <div v-show=" ! loading && next_pages.length < 1" class="mx-3">
            <i>No subsequent pages.</i>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        pixel_id: String,
        host: String,
        filters: Object,
    },

    data() {
        return {
            loading: true,
            next_pages: [],
            first: false,
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
            })).then( (response) => {
                this.next_pages = response.data.paths;
            }).catch( (error) => {
                console.log(error);
                window.alert('Something went wrong.');
            }).then( () => {
                this.loading = false;
            });
        }
    },

    watch: {
        filters: {
            deep: true,
            immediate: true,
            handler() {
                this.first = this.filters.previous_pages.length === 0;
                this.update();
            }
        }
    },
}
</script>
