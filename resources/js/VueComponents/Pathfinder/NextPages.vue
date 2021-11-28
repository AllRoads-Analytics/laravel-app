<template>
    <div>
        <div v-if="loading">
            Loading...
        </div>

        <table v-else class="table">
            <thead>
                <tr>
                    <th scope="col">
                        Select {{ first ? 'starting' : 'next' }} page
                    </th>

                    <th scope="col">Views</th>
                </tr>
            </thead>

            <tbody>
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
</template>

<script>
export default {
    props: {
        pixel_id: String,
        host: String,
        previous_pages: Array,
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

            Axios.get(route('pathfinder.ajax.get_next_pages', {
                tracker: this.pixel_id,
                host: this.host,
                previous_pages: this.previous_pages,
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
        previous_pages: {
            deep: true,
            immediate: true,
            handler() {
                this.first = this.previous_pages.length === 0;
                this.update();
            }
        }
    },
}
</script>
