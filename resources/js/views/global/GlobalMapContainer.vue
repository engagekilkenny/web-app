<template>
    <div class="global-map-container" :style="{height: mapHeight}">

        <loading
            v-if="loading"
            :active.sync="loading"
            :is-full-page="true"
        />

        <BuildingsMap v-else />

    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'
import BuildingsMap from './BuildingsMap'

export default {
    name: 'GlobalMapContainer',
    components: {
        Loading,
        BuildingsMap
    },
    data () {
        return {
            mapHeight: (window.outerHeight - 72)
        }
    },
    async created ()
    {
        if (this.isMobile) this.addEventListenerIfMobile();

        // get all layers
        await this.$store.dispatch('GET_ALL_LAYERS');

        await this.$store.dispatch('GET_ALL_STORIES');

        this.$store.commit('globalLoading', false);
    },
    async destroyed ()
    {
        window.removeEventListener("resize", this.resizeHandler);
    },
    computed: {
        /**
         * Show loading when changing dates
         */
        loading ()
        {
            return this.$store.state.globalmap.loading;
        },

        /**
         * Return true if the device is mobile
         */
        isMobile ()
        {
            return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
        }
    },
    methods: {
        /**
         *
         */
        addEventListenerIfMobile ()
        {
            this.mapHeight = window.innerHeight - 72 + "px";

            window.addEventListener("resize", this.resizeHandler);
        },

        /**
         * Sets the display height for mobile devices
         */
        resizeHandler ()
        {
            this.mapHeight = window.innerHeight - 72 + "px";
        }
    }
}
</script>

<style scoped>
    @import '~leaflet/dist/leaflet.css';

    .global-map-container {
        height: calc(100% - 72px);
        margin: 0;
        position: relative;
        z-index: 1;
    }

    @media screen and (max-width: 768px)
    {
        .global-map-container {
            height: calc(100% - 65px) !important;
        }
    }
</style>
