export const actions = {
    /**
     * Get the art point data for the global map
     */
    async GET_ART_DATA (context)
    {
        await axios.get('/global/art-data')
            .then(response => {
                console.log('get_art_data', response);

                context.commit('globalArtData', response.data);
            })
            .catch(error => {
                console.error('get_art_data', error);
            });
    },

    /**
     * Return the geojson file as an object
     */
    async GET_BUILDINGS (context)
    {
        await axios.get('/global/buildings')
            .then(response => {
                console.log('get_buildings', response);

                if (response.data.success)
                {
                    const geojson = JSON.parse(response.data.geojson);

                    context.commit('setBuildings', geojson);
                }
            })
            .catch(error => {
                console.error('get_buildings', error);
            });
    },

    /**
     * Get all stories for all buildings
     */
    async GET_ALL_STORIES (context)
    {
        await axios.get('/buildings/all-stories')
            .then(response => {
                console.log('all_stories', response);

                context.commit('addStoriesToBuildings', response.data);
            })
            .catch(error => {
                console.log('all_stories', error);
            });
    },

    /**
     * Get clusters for the global map
     */
    async GET_CLUSTERS (context, payload)
    {
        await axios.get('/global/clusters', {
            params: {
                zoom: payload,
                bbox: null
            }
        })
        .then(response => {
            console.log('get_clusters', response);

            context.commit('updateGlobalData', response.data);
        })
        .catch(error => {
            console.error('get_clusters', error);
        });
    }
}
