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
     * Return the geojson files
     */
    async GET_ALL_LAYERS (context)
    {
        await axios.get('/map/get-layers')
            .then(response => {
                console.log('get_all_layers', response);

                if (response.data.success)
                {
                    context.commit('setBuildings', response.data.buildings);
                    context.commit('setStreets', JSON.parse(response.data.streets));
                    context.commit('setWalls', JSON.parse(response.data.walls));
                    context.commit('setPoints', JSON.parse(response.data.points));
                    context.commit('setMonuments', JSON.parse(response.data.monuments));
                    context.commit('setBridges', JSON.parse(response.data.bridges));
                    context.commit('setTalbotsTower', JSON.parse(response.data.talbotsTower));
                    context.commit('setACAs', JSON.parse(response.data.acas));
                    context.commit('setCityBoundary', JSON.parse(response.data.cityBoundary));
                    context.commit('setParishes', JSON.parse(response.data.parishes));
                    context.commit('setFloodZone', JSON.parse(response.data.floodzone));
                    context.commit('setLandUseTypes', JSON.parse(response.data.landUseTypes));
                    context.commit('setOsmBoundaries', JSON.parse(response.data.osmBoundaries));
                    context.commit('setNaturalOsm', JSON.parse(response.data.natural));
                    context.commit('setLeisureOsm', JSON.parse(response.data.leisure));
                    context.commit('setAmenitiesOsm', JSON.parse(response.data.amenities));
                }
            })
            .catch(error => {
                console.error('get_all_layers', error);
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
