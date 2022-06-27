<template>
    <div>
        <div class="container home-container">
            <!-- Title, Subtitle, App Icons -->
            <div class="columns c-1">
                <div class="column is-half">
                        <h1 class="main-title variable-height">
                            Engage with your historic town centre
                        </h1>.
                    <h2 class="subtitle is-3 home-img-padding">
                        We have combined multiple datasets to create a digital engagement tool.
                        Go check it out!
                    </h2>

                    <!-- Download app icons -->
                    <div class="flex">
                        <img
                            src="/assets/icons/ios.png"
                            class="app-icon"
                            style="margin-right: 1em;"
                            @click="ios"
                        />
                        <img
                            src="/assets/icons/android.png"
                            class="app-icon"
                            @click="android"
                        />
                    </div>
                </div>

                <div class="column is-half">
                    <div class="top-image">
                        <img src="https://kilkennynow.ie/wp-content/uploads/Kilkenny-shop-front.jpg" />
                    </div>

                    <h3 class="subtitle is-4">
                        EngageKilkenny is brought to you by the Heritage Councils Collaborative Town Center Health Check
                        <a href="https://twitter.com/search?q=ctchc" target="_blank">#CTCHC</a>
                    </h3>
                </div>
            </div>

            <!-- What are we doing? -->
            <div class="why-container">
                <h1 class="main-title mb1">
                    What are we doing?
                </h1>

                <div class="columns welcome-mb">
                    <div class="column is-one-quarter icon-center">
                        <img
                            src="/assets/icons/home/world.png"
                            class="about-icon"
                        />
                    </div>

                    <div class="column ma">
                        <h2 class="main-subtitle">
                            1. Collect Data
                        </h2>
                        <h3 class="welcome-subtitle mb1em">
                            There is a lack of data about the quality and types of historic town centre buildings, their features and characteristics.
                            We need your help to collect and map this data.
                        </h3>
                    </div>
                </div>

                <div class="columns welcome-mb">
                    <div class="column is-one-quarter icon-center">
                        <img
                            src="/assets/icons/home/tree.png"
                            class="about-icon"
                        />
                    </div>

                    <div class="column ma">
                        <h2 class="main-subtitle">
                            2. Grow Engagement in historic town centers
                        </h2>
                        <h3 class="welcome-subtitle">
                            We want to use our digital platform to grow real-world engagement and awareness about our historic town centers.
                        </h3>
                    </div>
                </div>

                <div class="columns welcome-mb">
                    <div class="column is-one-quarter icon-center">
                        <img
                            src="/assets/icons/home/microscope.png"
                            class="about-icon"
                        />
                    </div>

                    <div class="column ma">
                        <h2 class="main-subtitle">
                            3. Discover Kilkenny
                        </h2>
                        <h3 class="welcome-subtitle mb1em">
                            Visit the map to explore the different cultural, service and administrative layers of Kilkenny.
                        </h3>
                    </div>
                </div>
            </div>

<!--            &lt;!&ndash; How does it work &ndash;&gt;-->
<!--            <div>-->
<!--                <h1 class="main-title mb1">-->
<!--                    {{ $t('home.welcome.how-does-it-work') }}?-->
<!--                </h1>-->

<!--                <div class="columns welcome-mb">-->
<!--                    <div class="column is-one-quarter icon-center">-->
<!--                        <img-->
<!--                            src="/assets/icons/home/camera.png"-->
<!--                            class="about-icon"-->
<!--                        />-->
<!--                    </div>-->

<!--                    <div class="column ma">-->
<!--                        <h2 class="main-subtitle">-->
<!--                            1. Take a photo-->
<!--                        </h2>-->
<!--                        <h3 class="welcome-subtitle mb1em">-->
<!--                            Your devices captures where you took the photo.-->
<!--                            We need your help to take geotagged photos of buildings and tell us what you think about them.-->
<!--                        </h3>-->
<!--                    </div>-->
<!--                </div>-->

<!--                <div class="columns welcome-mb">-->
<!--                    <div class="column is-one-quarter icon-center">-->
<!--                        <img-->
<!--                            src="/assets/icons/home/phone.png"-->
<!--                            class="about-icon"-->
<!--                        />-->
<!--                    </div>-->

<!--                    <div class="column ma">-->
<!--                        <h2 class="main-subtitle">-->
<!--                            2. Add Tags-->
<!--                        </h2>-->
<!--                        <h3 class="welcome-subtitle mb1em">-->
<!--                            After taking a photo, choose from our tags. Is it a beautiful old sign? A broken window?-->
<!--                            How does the image make you feel?-->
<!--                        </h3>-->
<!--                    </div>-->
<!--                </div>-->

<!--                <div class="columns welcome-mb">-->
<!--                    <div class="column is-one-quarter icon-center">-->
<!--                        <img-->
<!--                            src="/assets/icons/twitter2.png"-->
<!--                            class="about-icon"-->
<!--                        />-->
<!--                    </div>-->

<!--                    <div class="column ma">-->
<!--                        <h2 class="main-subtitle">-->
<!--                            3. Explore the map-->
<!--                        </h2>-->
<!--                        <h3 class="welcome-subtitle">-->
<!--                            Visit the EngageKilkenny map and engage with the buildings online!-->
<!--                        </h3>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

            <!-- Partners -->
            <div class="partners-container container is-max-desktop">
                <p class="has-text-centered main-title mb1">
                    Brought to you by
                </p>

                <Partners />
            </div>
        </div>

        <Footer />
    </div>
</template>

<script>
import Footer from './Footer';
import Partners from './Partners';

export default {
    name: 'Welcome',
    components: {
        Partners,
        Footer
    },
    mounted () {
        this.startHeadingsAnimation();
    },
    data () {
        return {
            headings: [
                {
                    title: this.$t('home.welcome.plastic-pollution-out-of-control'),
                    img: '/assets/plastic_bottles.jpg'
                },
                {
                    title: this.$t('home.welcome.fires-out-of-control'),
                    img: '/assets/forest_fire.jpg'
                },
            ],
            activeHeadingIndex: 0
        };
    },
    computed: {
        /**
         * Show currently active header
         */
        activeHeading ()
        {
            return this.headings[this.activeHeadingIndex];
        },

        /**
         * Boolean to show or hide the modal
         */
        modal ()
        {
            return this.$store.state.modal.show;
        }
    },
    methods: {
        /**
         * Open Google Play store download page
         */
        android ()
        {
            window.open('https://play.google.com/store/apps/details?id=com.geotech.openlittermap', '_blank');
        },

        /**
         * Open App Store download page
         */
        ios ()
        {
            window.open('https://apps.apple.com/us/app/openlittermap/id1475982147', '_blank');
        },

        /**
         * Switches the heading and top image every 5 seconds
         * @see https://usefulangle.com/post/280/settimeout-setinterval-on-inactive-tab
         */
        startHeadingsAnimation ()
        {
            let vm = this;
            let interval = null;

            function setAnimation () {
                if (document.hidden) {
                    // tab is now inactive
                    if (interval) clearInterval(interval);
                    return;
                }

                // tab is active again
                interval = setInterval(() => {
                    vm.activeHeadingIndex = (vm.activeHeadingIndex + 1) % vm.headings.length;
                }, 5000);
            }

            setAnimation();

            document.addEventListener('visibilitychange', setAnimation);
        },
    }
}
</script>

<style scoped lang="scss">


    .about-icon {
        height: 10em;
        text-align: center;
    }

    .c-1 {
        margin-bottom: 3em;
    }

    .home-container {
        padding-top: 5em;
    }

    .home-img-padding {
        padding-right: 2em;
    }

    .main-title {
        font-size: 4rem;
        font-weight: 800;
        color: #363636;
        line-height: 1.125;
        margin-bottom: 0.25em;
    }

    .icon-center {
        margin: auto;
        text-align: center;
    }

    .welcome-mb {
        margin-bottom: 5em;
    }

    .main-subtitle {
        font-size: 2rem;
        color: #4a4a4a;
        font-weight: 700;
        line-height: 1.5;
        margin-bottom: 0.5em;
    }

    .welcome-subtitle {
        color: #4a4a4a;
        font-size: 2rem;
        font-weight: 400;
        line-height: 1.5;
    }

    .partners-container {
        padding-left: 72px;
        padding-right: 72px;
        margin-bottom: 36px;
    }

    .partners-action {
        margin-top: 36px;
    }

    /* Smaller screens */
    @media (max-width: 1024px) {

        .home-container {
            padding-left: 2em;
            padding-right: 2em;
        }

    }

    /* Mobile view */
    @media (max-width: 768px) {

        .home-container {
            padding-top: 3em !important;
        }

        .home-img-padding {
            padding: 0;
        }

        .main-title {
            font-size: 3rem;
        }

        .icon-center {
            text-align: center;
            margin-bottom: 2em;
        }

        .welcome-mb {
            margin-bottom: 1em;
        }

        .why-container {
            margin-bottom: 5em;
        }

        .top-image {
            height: 400px;
        }

        .partners-container {
            padding-left: 0;
            padding-right: 0;
        }
    }

    /* Extra small */
    @media (max-width: 576px) {
        .main-title {
            margin-bottom: 1rem;
        }
        .variable-height {
            min-height: 100px;
        }

        .top-image {
            height: 260px;
        }

    }

    /* Extra extra small */
    @media (max-width: 430px) {
        .variable-height {
            min-height: 175px;
        }
    }

    .slide-fade-left-enter-active {
        transition: all .5s ease;
    }
    .slide-fade-left-leave-active {
        transition: all .3s ease-out;
    }
    .slide-fade-left-enter, .slide-fade-left-leave-to {
        transform: translateX(-100px);
        opacity: 0;
    }

    .slide-fade-right-enter-active {
        transition: all .5s ease;
    }
    .slide-fade-right-leave-active {
        transition: all .3s ease-out;
    }
    .slide-fade-right-enter, .slide-fade-right-leave-to {
        transform: translateX(100px);
        opacity: 0;
    }

</style>
