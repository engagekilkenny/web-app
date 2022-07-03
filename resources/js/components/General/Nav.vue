<template>
    <div>
        <nav class="navbar main-nav">
            <div class="container">
                <div class="w100 flex">

                    <router-link to="/" class="navbar-item flex-1">
                        <h1 class="nav-title flex-1">#EngageKilkenny</h1>

                        <div
                            class="ctchc-info"
                            @click="openCTCHCPage"
                        >
                            <p>
                                Brought to you by the Collaborative Town Centre Health Check (CTCHC)
                            </p>

                            <p class="has-text-centered">and the Heritage Council.</p>
                        </div>

                    </router-link>

                    <!-- Mobile -->
                    <div :class="burger" @click="toggleOpen">
                        <span class="is-white" />
                        <span class="is-white" />
                        <span class="is-white" />
                    </div>
                </div>

                <div :class="nav">
                    <div class="navbar-end">

<!--                        &lt;!&ndash; About &ndash;&gt;-->
<!--                        <router-link to="/about" class="navbar-item" @click.native="close">-->
<!--                            {{ $t('nav.about')}}-->
<!--                        </router-link>-->

                        <!-- Kilkenny Map -->
<!--                        <router-link to="/map" class="navbar-item" @click.native="close">-->
<!--                            Map-->
<!--                        </router-link>-->

                        <!-- if auth -->
                        <div v-if="auth" class="flex-not-mobile">

                            <router-link to="/upload" class="navbar-item">
                                 {{ $t('nav.upload')}}
                            </router-link>

                            <!-- Dropdown toggle -->
                            <div :key="$route.fullPath" class="navbar-item has-dropdown is-hoverable">

                                <!-- "More" -->
                                <a id="more" class="navbar-item"> {{ $t('nav.more')}}</a>
                                <!-- Dropdown menu -->
                                <div class="navbar-dropdown" style="z-index: 2;">

                                    <!-- Admin - Review Stories -->
                                    <!-- v-if="can('review data')"-->
                                    <router-link to="/admin/review" class="navbar-item drop-item" @click.native="close">
                                        ADMIN - Review Data
                                    </router-link>

                                    <!-- Admin -->
                                    <router-link v-if="can('update tags')" to="/admin/photos" class="navbar-item drop-item" @click.native="close">
                                        {{ $t('nav.admin-verify-photos')}}
                                    </router-link>

                                    <a v-if="is('superadmin')" href="/horizon" class="navbar-item drop-item" target="_blank">
                                        {{ $t('nav.admin-horizon')}}
                                    </a>

                                    <a v-if="can('verify boxes')" href="/bbox/verify" class="navbar-item drop-item">
                                        {{ $t('nav.admin-verify-boxes')}}
                                    </a>

                                    <!-- Tag Litter -->
                                    <router-link to="/tag" class="navbar-item drop-item">
                                        Add Tags
                                    </router-link>

                                    <!-- Profile -->
                                    <router-link to="/profile" class="navbar-item drop-item">
                                        {{ $t('nav.profile')}}
                                    </router-link>

<!--                                    &lt;!&ndash; Teams &ndash;&gt;-->
<!--                                    <router-link to="/teams" class="navbar-item drop-item">-->
<!--                                        {{ $t('nav.teams') }}-->
<!--                                    </router-link>-->

                                    <!-- Settings -->
                                    <router-link to="/settings/password" class="navbar-item drop-item">
                                         {{ $t('nav.settings')}}
                                    </router-link>

                                    <!-- Bounding boxes -->
<!--                                    <router-link v-if="can('create boxes')" to="/bbox" class="navbar-item drop-item" @click.native="close">-->
<!--                                       {{ $t('nav.bounding-boxes')}}-->
<!--                                    </router-link>-->

                                    <!-- Logout -->
                                    <a
                                        class="navbar-item drop-item"
                                        @click="logout"
                                    >{{ $t('nav.logout')}}</a>

                                    <!-- Menu for available languages -->
<!--                                    <Languages />-->
                                </div>
                            </div>
                        </div>

<!--                        &lt;!&ndash; The user is not authenticated &ndash;&gt;-->
<!--                        <div v-else class="flex-not-mobile">-->
<!--                            &lt;!&ndash; Login &ndash;&gt;-->
<!--                            <a class="navbar-item" @click="login">{{ $t('nav.login')}}</a>-->

<!--                            &lt;!&ndash; Signup &ndash;&gt;-->
<!--                            <router-link to="/signup" class="navbar-item">-->
<!--                                 {{ $t('nav.signup')}}-->
<!--                            </router-link>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>

<script>
import Languages from '../global/Languages'

export default {
    name: 'Nav',
    components: { Languages },
    data ()
    {
        return {
            open: false
        };
    },
    computed: {

        /**
         * Return true if the user is logged in
         */
        auth ()
        {
            return this.$store.state.user.auth;
        },

        /**
         *
         */
        burger ()
        {
            return this.open ? 'navbar-burger burger is-active' : 'navbar-burger burger'
        },

        /**
         * Some users are able to add bounding boxes to images
         */
        can_bbox ()
        {
            return this.$store.state.user.user.can_bbox;
        },

        /**
         *
         */
        nav ()
        {
            return this.open ? 'navbar-menu is-active' : 'navbar-menu';
        }
    },
    methods: {
        /**
         * Mobile - Close the nav
         */
        close ()
        {
            this.open = false;
        },

        /**
         * Open the CTCHC website in another page
         */
        openCTCHCPage ()
        {
            window.open("https://www.heritagecouncil.ie/projects/town-centre-health-check-programme", "_blank");
        },

        /**
         * Show modal to log the user in
         */
        login ()
        {
            this.$store.commit('showModal', {
                type: 'Login',
                title: 'Login',
                action: 'LOGIN'
            });
        },

        /**
         * Log the user out
         */
        async logout ()
        {
            await this.$store.dispatch('LOGOUT');
        },

        /**
         * Mobile - toggle the nav
         */
        toggleOpen ()
        {
            this.open = ! this.open;
        }
    }
}
</script>

<style scoped>

    .burger {
        align-self: center;
    }

    .ctchc-info {
        color: white;
    }

    .ctchc-info:hover {
        color: #ffc21e;
    }

    .drop-item {
        color: black;
        font-weight: 500;
    }

    .flex-not-mobile {
        display: flex;
    }

    .main-nav {
        background-color: black;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .nav-title {
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
        line-height: 1.125;
    }

    .nav-title:hover {
        color: #ffc21e;
    }

    .is-white {
        color: white;
    }

    @media (max-width: 768px)
    {

        .flex-not-mobile {
            display: block;
        }

        .nav-title {
            font-size: 2rem;
            padding-left: 0.25em;
        }

    }

</style>
