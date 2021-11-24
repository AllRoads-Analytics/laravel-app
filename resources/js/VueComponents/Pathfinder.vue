<template>
    <div>
        <div v-if="loading">
            Loading...
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Path</th>
                    <th scope="col">Views</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="path, idx in next_paths" :key="idx" >
                    <td>
                        <a href="#">
                            {{ path.path }}
                        </a>
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
    },

    data() {
        return {
            loading: true,
            next_paths: [],
        };
    },

    mounted() {
        Axios.get(route('pathfinder.ajax.get_next_paths', {
            tracker_pixel_id: this.pixel_id,
            host: this.host,
        })).then( (response) => {
            console.log(response.data);
            this.next_paths = response.data.paths;
            this.loading = false;
        }).catch( (error) => {
            console.log(error);
            window.alert('Something went wrong.');
        });
    },
}
</script>
