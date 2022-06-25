import { init } from './init'

export const mutations = {

    /**
     * Loop over all buildings
     */
    addStoriesToBuildings (state, payload)
    {
        state.buildings.features.forEach(building => {

            if (payload.includes(parseInt(building.properties.osm_id)))
            {
                building.properties.hasStory = true;
            }
            else
            {
                building.properties.hasStory = false;
            }
        });
    },

    /**
     *
     */
	closeDatesButton (state)
    {
		state.datesOpen = false;
	},

    /**
     *
     */
	closeLangsButton (state)
    {
		state.langsOpen = false;
	},

    /**
     * Initialise the global art data
     */
    globalArtData (state, payload)
    {
        state.artData = payload;
    },

    /**
     * When changing dates
     */
	globalLoading (state, payload)
    {
		state.loading = payload;
	},

    /**
     * Reset the state, when the user logs out.
     */
    resetState (state)
    {
        Object.assign(state, init);
    },

    /**
     * Buildings.geojson is fetched from the backend
     */
    setBuildings (state, payload)
    {
        state.buildings = payload;
    },

    /**
     * points.geojson is fetched from the backend
     */
    setPoints (state, payload)
    {
        payload.features = payload.features.map(feature => {
            Object.keys(feature.properties).map(key => {
                if (!feature.properties[key]) delete feature.properties[key];
            });

            return feature;
        });

        state.points = payload;
    },

    setMonuments (state, payload)
    {
        payload.features = payload.features.map(feature => {
            Object.keys(feature.properties).map(key => {
                if (!feature.properties[key]) delete feature.properties[key];
            });

            return feature;
        });

        state.monuments = payload;
    },

    setBridges (state, payload)
    {
        payload.features = payload.features.map(feature => {
            Object.keys(feature.properties).map(key => {
                if (!feature.properties[key]) delete feature.properties[key];
            });

            return feature;
        });

        state.bridges = payload;
    },

    setTalbotsTower (state, payload)
    {
        payload.features = payload.features.map(feature => {
            Object.keys(feature.properties).map(key => {
                if (!feature.properties[key]) delete feature.properties[key];
            });

            return feature;
        });

        state.talbotsTower = payload;
    },

    setACAs (state, payload)
    {
        state.acas = payload;
    },

    /**
     * cityboundary.geojson
     */
    setCityBoundary (state, payload)
    {
        state.cityBoundary = payload;
    },

    /**
     * osmBoundaries.geojson
     */
    setOsmBoundaries (state, payload)
    {
        state.osmBoundaries = payload;
    },

    /**
     * leisureOsm.geojson
     */
    setLeisureOsm (state, payload)
    {
        state.leisure = payload;
    },

    /**
     * buildingsWithAmenities.geojson
     */
    setAmenitiesOsm (state, payload)
    {
        state.amenities = payload;
    },

    /**
     * naturalOSM.geojson
     */
    setNaturalOsm (state, payload)
    {
        state.naturalOsm = payload;
    },

    /**
     * landUseTypes.geojson
     */
    setLandUseTypes (state, payload)
    {
        state.landUseTypes = payload;
    },

    /**
     * parishes.geojson
     */
    setParishes (state, payload)
    {
        state.parishes = payload;
    },

    /**
     * floodzone.geojson
     */
    setFloodZone (state, payload)
    {
        state.floodzone = payload;
    },

    /**
     * Streets.geojson is fetched from the backend
     */
    setStreets (state, payload)
    {
        // Delete missing data. Should be preprocessed before loading the file but we will just do it here
        payload.features = payload.features.map(feature => {
            Object.keys(feature.properties).map(key => {
               if (!feature.properties[key]) delete feature.properties[key];
            });

            return feature;
        });

        state.streets = payload;
    },

    /**
     * Walls.geojson is fetched from the backend
     */
    setWalls (state, payload)
    {
        state.walls = payload;
    },

    /**
     *
     */
	toggleLangsButton (state)
    {
		state.langsOpen = ! state.langsOpen;
	},

    /**
     *
     */
	toggleGlobalDates (state)
    {
		state.datesOpen = ! state.datesOpen;
	},

	// updateCurrentGlobalDate (state, payload)
    // {
	// 	state.currentDate = payload;
	// },

    /**
     *
     */
	updateGlobalData (state, payload)
    {
		state.geojson = payload;
	}

};
