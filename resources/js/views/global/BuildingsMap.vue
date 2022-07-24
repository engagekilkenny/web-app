<template>
    <div class="h100 flex">
        <!-- The map & data -->
        <div
            id="buildings-map"
            ref="buildings-map"
        />

        <!-- Right-hand form container -->
        <SideMapContainer
            :key="buildingsKey"
            class="is-hidden-mobile"
        />

    </div>
</template>

<script>
import L from 'leaflet';
import {
    MAX_ZOOM,
    MIN_ZOOM,
} from '../../constants';
import './SmoothWheelZoom.js';

import SideMapContainer from "../../components/global/SideMapContainer";

import { mapHelper } from '../../maps/mapHelpers';
import { buildingsHelper } from "../../maps/buildingsHelper";
import { streetsHelper } from "../../maps/streetsHelper";
import { wallsHelper } from "../../maps/wallsHelper";
import { monumentsHelper } from "../../maps/monumentsHelper";
import { bridgesHelper } from "../../maps/bridgesHelper";
import { acasHelper } from "../../maps/acasHelper";
import { landUseHelper } from "../../maps/landUseHelper";

var map;

var buildings, buildingsWithoutYear,
    buildings_1200_1300,
    buildings_1301_1605,
    buildings_1651_1765,
    buildings_1766_1815,
    buildings_1816_1916,
    buildings_1917_1950,
    buildings_1951_2000,
    buildings_2001_2022;
var vacant, abandoned;
var wheelchairYes, wheelchairLimited, wheelchairNo;
var roofs, roofDoubleSaltbox, roofFlat, roofGabled, roofHalfHipped, roofHipped, roofPitched, roofPyramidal, roofRound,
    roofSaw, roofSkillon, roofSloped;
var historicChurch, historicRuins, historicTower, historicWatermill, historicYes, historicArcheologicalSite,
    historicHouse, historicCastle;
var rps;

var apartmentsPoly, bankPoly, bridgePoly, chapelPoly, churchPoly, cinemaPoly, civicPoly, clinicPoly, collegePoly,
    commercialPoly, constructionPoly, dovecotePoly, Detached, fireStationPoly, garagePoly, governmentPoly,
    hospitalPoly, hotelPoly, housePoly, industrialPoly, officePoly, publicPoly, residentialPoly, retailPoly,
    roofPoly, ruinsPoly, schoolPoly, servicePoly, shedPoly, stadiumPoly, teahousePoly, terracePoly, towerPoly,
    trainStationPoly, warehousePoly;

var bankAmenity, barAmenity, bicyclingParkingAmenity, cafeAmenity, carWashAmenity, cinemaAmenity, clinicAmenity,
    collegeAmenity, communityCentreAmenity, courtHouseAmenity, coworkingSpaceAmenity, fastFoodAmenity, fireStationAmenity,
    fuelAmenity, graveyardAmenity, libraryAmenity, kindergartenAmenity, parkingAmenity, policeAmenity, postOfficeAmenity, pharmacyAmenity,
    placeOfWorshipAmenity, pubAmenity, restaurantAmenity, schoolAmenity, shelterAmenity, socialFacilityAmenity,
    taxiAmenity, theatreAmenity, townhallAmenity, vetinaryAmenity;

var streets, streetTypes;
var corridor, footway, path, pedestrian, residential, secondary, service, steps, tertiary, unclassified;
var streetsMaterial;

var walls, historic, tomb, streetLamp, oghamStone, memorial, cityGate, archaeological, uncategorised;

var art, statue, stainedGlassWindow, sculpture, relief, mural, graffiti;

var points, wateringPlace, wasteBasket, vendingMachines, training, toilets, theatre, telephone, studio,
    socialCentre, restaurant, recycling, publicOffice, pub, postOffice, postBox, placeOfWorship,
    pharmacy, parkingEntrance, parking, musicSchool, motorcycleParking, marketplace, library,
    kitchen, gritBin, fountain, fastFood, doctors, dentist, communityCentre, college, clock,
    chargingStation, carWash, cafe, bicyclingParking, bench, bar, bank, atm, artsCentre, courtsCentre;

var monuments, talbotsTower;

var bridges, greensBridge, stFrancisBridge, ladyDesartBridge, johnsBridge;

// Administrative
var acas;
var cityBoundary, parishes, stMarysParish, stCanicesParish, stPatricksParish, stMaulsParish, stJohnsParish;
var brownfield, cemetery, commercial, construction, flowerbed, forest, government, grass, industrial, meadow,
    orchard, railway, religious, residentialLandUse, retail, landUseTypes;
var osmBoundaries;
var naturalOsm;
var leisure, garden, outdoor_seating, park, pitch, playground, sports_centre, stadium;
var floodzone;

var layerController;

const purple_dot = L.icon({
    iconUrl: './images/vendor/leaflet/dist/purpledot.png',
    iconSize: [10, 10]
});

const grey_dot = L.icon({
    iconUrl: './images/vendor/leaflet/dist/grey-dot.jpg',
    iconSize: [13, 10]
});

/**
 * Create the point to display for each piece of non-art data
 */
function createPurpledotIcon (feature, latlng)
{
    const x = [latlng.lat, latlng.lng];

    return L.marker(x, { icon: purple_dot });
}

/**
 * Create the point to display for each piece of Art data
 */
function createGreyDotIcon (feature, latlng)
{
    const x = [latlng.lat, latlng.lng];

    return L.marker(x, { icon: grey_dot });
}

/**
 * Create the Layer Controller used to toggle all layers
 *
 * This uses the Leaflet.Control.Layer.Tree plugin allowing us to nest layers.
 */
function createLayerController ()
{
    let overlayLayersTree = {
        label: 'All Data',
        children: [
            {
                label: ' Administrative',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    {
                        label: ' Official',
                        collapsed: true,
                        selectAllCheckbox: false,
                        children: [
                            { label: ' Areas of Conservation', layer: acas },
                            { label: ' City Boundary', layer: cityBoundary },
                        ]
                    },
                    {
                        label: ' Parishes',
                        collapsed: true,
                        selectAllCheckbox: false,
                        children: [
                            { label: ' St Mary\'s', layer: stMarysParish },
                            { label: ' St Canice\'s', layer: stCanicesParish },
                            { label: ' St Patrick\'s', layer: stPatricksParish },
                            { label: ' St Mauls\'', layer: stMaulsParish },
                            { label: ' St Johns\'', layer: stJohnsParish }
                        ]
                    },
                    {
                        label: ' OpenStreetMap',
                        collapsed: true,
                        selectAllCheckbox: false,
                        children: [
                            { label: ' OSM Boundaries', layer: osmBoundaries }
                        ]
                    }
                ]
            },
            {
                label: ' Amenity',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Arts Centre', layer: artsCentre },
                    { label: ' ATM', layer: atm },
                    { label: ' Bank', layer: bank },
                    { label: ' Bench', layer: bench },
                    { label: ' Bicycle parking', layer: bicyclingParking },
                    { label: ' Cafe', layer: cafe },
                    { label: ' Car Wash', layer: carWash },
                    { label: ' Charging Station', layer: chargingStation },
                    { label: ' Clock', layer: clock },
                    { label: ' College', layer: college },
                    { label: ' Community Centre', layer: communityCentre },
                    { label: ' Dentist', layer: dentist },
                    { label: ' Doctors', layer: doctors },
                    { label: ' Fast Food', layer: fastFood },
                    { label: ' Fountain', layer: fountain },
                    { label: ' Grit Bin', layer: gritBin },
                    { label: ' Kitchen', layer: kitchen },
                    { label: ' Library', layer: library },
                    { label: ' Marketplace', layer: marketplace },
                    { label: ' Motorcycle Parking', layer: motorcycleParking },
                    { label: ' Music School', layer: musicSchool },
                    { label: ' Parking', layer: parking },
                    { label: ' Parking Entrance', layer: parkingEntrance },
                    { label: ' Pharmacy', layer: pharmacy },
                    { label: ' Place of Worship', layer: placeOfWorship },
                    { label: ' Post Box', layer: postBox },
                    { label: ' Post Office', layer: postOffice },
                    { label: ' Pub', layer: pub },
                    { label: ' Public Office', layer: publicOffice },
                    { label: ' Restaurant', layer: restaurant },
                    { label: ' Social Centre', layer: socialCentre },
                    { label: ' Studio', layer: studio },
                    { label: ' Telephone', layer: telephone },
                    { label: ' Toilets', layer: toilets },
                    { label: ' Training', layer: training },
                    { label: ' Vending Machine', layer: vendingMachines },
                    { label: ' Waste Basket', layer: wasteBasket },
                    { label: ' Watering Place', layer: wateringPlace },
                ]
            },
            {
                label: ' Art',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Graffiti', layer: graffiti },
                    { label: ' Mural', layer: mural },
                    { label: ' Relief', layer: relief },
                    { label: ' Sculpture', layer: sculpture },
                    { label: ' Stained Glass', layer: stainedGlassWindow },
                    { label: ' Statue', layer: statue }
                ]
            },
            {
                label: 'Bridges',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Greens Bridge', layer: greensBridge },
                    { label: ' St. Francis Bridge', layer: stFrancisBridge },
                    { label: ' Lady Desart Bridge', layer: ladyDesartBridge },
                    { label: ' Johns Bridge', layer: johnsBridge }
                ]
            },
            {
                label: 'Buildings',
                selectAllCheckbox: false,
                children: [
                    {
                        label: ' Age',
                        children: [
                            {
                                label: ' Year Of Construction',
                                children: [
                                    { label: ' 1200-1300', layer: buildings_1200_1300 },
                                    { label: ' 1301-1650', layer: buildings_1301_1605 },
                                    { label: ' 1651-1765', layer: buildings_1651_1765 },
                                    { label: ' 1766-1815', layer: buildings_1766_1815 },
                                    { label: ' 1816-1916', layer: buildings_1816_1916 },
                                    { label: ' 1917-1950', layer: buildings_1917_1950 },
                                    { label: ' 1951-2000', layer: buildings_1951_2000 },
                                    { label: ' 2001-2022', layer: buildings_2001_2022 }
                                ]
                            },
                            {
                                label: ' Year Missing',
                                layer: buildingsWithoutYear
                            }
                        ]
                    },
                    {
                        label: ' Amenities',
                        selectAllCheckbox: false,
                        collapsed: true,
                        children: [
                            { label: ' Bank', layer: bankAmenity },
                            { label: ' Bar', layer: barAmenity },
                            { label: ' Bicycle Parking', layer: bicyclingParkingAmenity },
                            { label: ' Cafe', layer: cafeAmenity },
                            { label: ' Car Wash', layer: carWashAmenity },
                            { label: ' Cinema', layer: cinemaAmenity },
                            // { label: ' Clinic', layer: clinicAmenity }, not in the data
                            { label: ' College', layer: collegeAmenity },
                            { label: ' Community Centre', layer: communityCentreAmenity },
                            { label: ' Courthouse', layer: courtHouseAmenity },
                            { label: ' Coworking Space', layer: coworkingSpaceAmenity },
                            { label: ' Fast Food', layer: fastFoodAmenity },
                            { label: ' Fire Station', layer: fireStationAmenity },
                            { label: ' Fuel', layer: fuelAmenity },
                            { label: ' Graveyard', layer: graveyardAmenity },
                            { label: ' Library', layer: libraryAmenity },
                            // { label: ' Kindergarten', layer: kindergartenAmenity }, gdpr?
                            { label: ' Pharmacy', layer: pharmacyAmenity },
                            { label: ' Place of Worship', layer: placeOfWorshipAmenity },
                            { label: ' Police', layer: policeAmenity },
                            { label: ' Post Office', layer: postOfficeAmenity },
                            { label: ' Pub', layer: pubAmenity },
                            { label: ' Restaurant', layer: restaurantAmenity },
                            { label: ' School', layer: schoolAmenity },
                            { label: ' Shelter', layer: shelterAmenity },
                            { label: ' Social Facility', layer: socialFacilityAmenity },
                            { label: ' Taxi', layer: taxiAmenity },
                            { label: ' Theatre', layer: theatreAmenity },
                            { label: ' Townhall', layer: townhallAmenity },
                            { label: ' Vetinary', layer: vetinaryAmenity },
                        ]
                    },
                    {
                        label: 'Dereliction',
                        collapsed: true,
                        children: [
                            { label: ' Abandoned', layer: abandoned },
                            { label: ' Vacant', layer: vacant }
                        ]
                    },
                    {
                        label: ' Historic',
                        collapsed: true,
                        children: [
                            { label: ' Archaeological', layer: historicArcheologicalSite },
                            { label: ' Castle', layer: historicCastle },
                            { label: ' Church', layer: historicChurch },
                            { label: ' House', layer: historicHouse },
                            { label: ' Ruins', layer: historicRuins },
                            { label: ' Tower', layer: historicTower },
                            { label: ' Watermill', layer: historicWatermill },
                            { label: ' Yes', layer: historicYes }
                        ]
                    },
                    {
                        label: 'Roof Types',
                        collapsed: true,
                        children: [
                            { label: ' Double saltbox', layer: roofDoubleSaltbox },
                            { label: ' Flat', layer:  roofFlat },
                            { label: ' Gabled', layer: roofGabled },
                            { label: ' Half-hipped', layer: roofHalfHipped },
                            { label: ' Hipped', layer: roofHipped },
                            { label: ' Pitched', layer: roofPitched },
                            { label: ' Pyramidal', layer: roofPyramidal },
                            { label: ' Round', layer: roofRound },
                            { label: ' Saw', layer: roofSaw },
                            { label: ' Skillion', layer: roofSkillon },
                            { label: ' Sloped', layer: roofSloped }
                        ]
                    },
                    {
                        label: ' Types',
                        selectAllCheckbox: false,
                        collapsed: true,
                        children: [
                            { label: ' Apartments', layer: apartmentsPoly },
                            { label: ' Bank', layer: bankPoly },
                            { label: ' Bridge', layer: bridgePoly },
                            // { label: ' Chapel', layer: chapelPoly }, not found
                            { label: ' Church', layer: churchPoly },
                            { label: ' Cinema', layer: cinemaPoly },
                            { label: ' Civic', layer: civicPoly },
                            // { label: ' Clinic', layer: clinicPoly },
                            { label: ' College', layer: collegePoly },
                            { label: ' Commercial', layer: commercialPoly },
                            { label: ' Construction', layer: constructionPoly },
                            { label: ' Dovecote', layer: dovecotePoly },
                            { label: ' Detached', layer: Detached },
                            { label: ' Fire Station', layer: fireStationPoly },
                            { label: ' Garage', layer: garagePoly },
                            { label: ' Government', layer: governmentPoly },
                            { label: ' Hospital', layer: hospitalPoly },
                            { label: ' Hotel', layer: hotelPoly },
                            { label: ' House', layer: housePoly },
                            { label: ' Industrial', layer: industrialPoly },
                            { label: ' Office', layer: officePoly },
                            { label: ' Public', layer: publicPoly },
                            { label: ' Residential', layer: residentialPoly },
                            { label: ' Retail', layer: retailPoly },
                            { label: ' Roof', layer: roofPoly },
                            { label: ' School', layer: schoolPoly },
                            { label: ' Service', layer: servicePoly },
                            { label: ' Shed', layer: shedPoly },
                            { label: ' Stadium', layer: stadiumPoly },
                            { label: ' Teahouse', layer: teahousePoly },
                            // { label: ' Terrace', layer: terracePoly }, not found
                            { label: ' Tower', layer: towerPoly },
                            { label: ' Train Station', layer: trainStationPoly },
                            { label: ' Warehouse', layer: warehousePoly }
                        ]
                    },
                    {
                        label: ' Wheelchair',
                        collapsed: true,
                        children: [
                            { label: ' Yes', layer: wheelchairYes },
                            { label: ' Limited', layer: wheelchairLimited },
                            { label: ' No', layer: wheelchairNo }
                        ]
                    }
                ]
            },
            {
                label: 'Historic',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Archeological', layer: archaeological },
                    { label: ' City Gate', layer: cityGate },
                    { label: ' Memorial', layer: memorial },
                    { label: ' Protected Monuments', layer: monuments },
                    { label: ' Protected Structures', layer: rps },
                    { label: ' Ogham Stone', layer: oghamStone },
                    { label: ' Other', layer: historic },
                    { label: ' Ruins', layer: ruinsPoly },
                    { label: ' Street Lamp', layer: streetLamp },
                    { label: ' Talbot\s Tower', layer: talbotsTower },
                    { label: ' Tomb', layer: tomb },
                    { label: ' Uncategorised', layer: uncategorised },
                    { label: ' Walls', layer: walls },
                ]
            },
            {
                label: ' Land Use Types',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Brownfield', layer: brownfield },
                    { label: ' Cemetery', layer: cemetery },
                    { label: ' Commercial', layer: commercial },
                    { label: ' Construction', layer: construction },
                    { label: ' Floodzone', layer: floodzone },
                    { label: ' Flower bed', layer: flowerbed },
                    { label: ' Forest', layer: forest },
                    { label: ' Government', layer: government },
                    { label: ' Grass', layer: grass },
                    { label: ' Industrial', layer: industrial },
                    {
                        label: ' Leisure',
                        collapsed: true,
                        selectAllCheckbox: false,
                        children: [
                            { label: ' Garden', layer: garden },
                            { label: ' Outdoor seating', layer: outdoor_seating },
                            { label: ' Park', layer: park },
                            { label: ' Pitch', layer: pitch },
                            { label: ' Playground', layer: playground },
                            { label: ' Sports Centre', layer: sports_centre },
                            { label: ' Stadium', layer: stadium }
                        ]
                    },
                    { label: ' Meadow', layer: meadow },
                    { label: ' Natural', layer: naturalOsm },
                    { label: ' Orchard', layer: orchard },
                    { label: ' Parking', layer: parkingAmenity },
                    { label: ' Railway', layer: railway },
                    { label: ' Religious', layer: religious },
                    { label: ' Residential', layer: residentialLandUse },
                    { label: ' Retail', layer: retail }
                ]
            },
            {
                label: 'Streets',
                collapsed: true,
                selectAllCheckbox: false,
                children: [
                    { label: ' Corridor', layer: corridor },
                    { label: ' Footway', layer: footway },
                    { label: ' Path', layer: path },
                    { label: ' Pedestrian', layer: pedestrian },
                    { label: ' Residential', layer: residential },
                    { label: ' Secondary', layer: secondary },
                    { label: ' Service', layer: service },
                    { label: ' Steps', layer: steps },
                    { label: ' Tertiary', layer: tertiary },
                    { label: ' Unclassified', layer: unclassified }
                ],
            }
        ]
    }

    layerController = L.control.layers.tree(null, overlayLayersTree).addTo(map);
}
export default {
    name: 'BuildingsMap',
    components: {
        SideMapContainer,
    },
    data () {
        return {
            building: null,
            street: null,
            stories: [],
            buildingsKey: 0
        };
    },
    mounted () {
        /** 0: Bind variable outside of vue scope */
        window.buildingsMap = this;

        /** 1. Create map object */
        map = L.map('buildings-map', {
            center: [52.652046, -7.2501555],
            zoom: 15,
            scrollWheelZoom: false,
            smoothWheelZoom: true,
            smoothSensitivity: 1
        });

        window.buildingsMap.map = map;

        map.scrollWheelZoom = true;

        /** 2. Add tiles, attribution, set limits */
        const mapLink = '<a href="https://openstreetmap.org">OpenStreetMap</a>';

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; ' + mapLink + ' & Contributors',
            maxZoom: MAX_ZOOM,
            minZoom: MIN_ZOOM
        }).addTo(map);

        map.attributionControl.addAttribution(' Architecture Data &copy NIAH');

        // Add the layers
        addBuildingLayers(this.$store.state.globalmap.buildings.features);
        addStreetLayers(this.$store.state.globalmap.streets.features);
        addWallsLayer(this.$store.state.globalmap.walls.features, this.$store.state.globalmap.points.features);
        addPointsLayers(this.$store.state.globalmap.points.features);
        addArtLayer(this.$store.state.globalmap.points.features);
        addMonumentsLayer(this.$store.state.globalmap.monuments.features);
        addBridgesLayer(this.$store.state.globalmap.bridges.features);
        addTalbotsTowerLayer(this.$store.state.globalmap.talbotsTower.features);
        addACAsLayer(this.$store.state.globalmap.acas.features);
        addCityBoundaryLayer(this.$store.state.globalmap.cityBoundary);
        addParishesLayer(this.$store.state.globalmap.parishes);
        addFloodZoneLayer(this.$store.state.globalmap.floodzone);
        addLandUseTypes(this.$store.state.globalmap.landUseTypes);
        addOsmBoundaries(this.$store.state.globalmap.osmBoundaries);
        addNaturalOsm(this.$store.state.globalmap.naturalOsm);
        addLeisureOsm(this.$store.state.globalmap.leisure);
        addAmenitiesOsm(this.$store.state.globalmap.amenities);
        addRPSLayer(this.$store.state.globalmap.rps);

        window.buildingsMap.buildings = buildings;

        createLayerController();

        map.on('click', function (e) {
            window.buildingsMap.building = null;
            window.buildingsMap.street = null;
            window.buildingsMap.bridge = null;
            window.buildingsMap.buildingsKey++;
        });

        createLegends();
    }
};

/**
 * Use the array of buildings from the backend
 *
 * Create many layers that can be turned on and off
 */
function addBuildingLayers (buildingsGeojson)
{
    // Without Year
    const filteredBuildingsWithYear = buildingsGeojson.filter(feature => {
        return (feature.properties.hasOwnProperty('NEWDATE'));
    });

    buildingsWithoutYear = L.geoJSON(null, {
        onEachFeature: onEachBuilding
    });

    const filteredBuildingsWithoutYear = buildingsGeojson.filter(feature => {
        if (!feature.properties.hasOwnProperty('NEWDATE')) {
            return feature;
        }
    });

    buildingsWithoutYear.addData({
        features: filteredBuildingsWithoutYear,
        type: "FeatureCollection"
    });

    // With Year
    buildings = L.geoJSON(filteredBuildingsWithYear, {
        onEachFeature: onEachBuilding
    });

    const buildings_1200_1300_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1200, 1300);
    const buildings_1301_1605_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1301, 1605);
    const buildings_1651_1765_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1651, 1765);
    const buildings_1766_1815_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1766, 1815);
    const buildings_1816_1916_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1816, 1916);
    const buildings_1917_1950_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1917, 1950);
    const buildings_1951_2000_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 1951, 2000);
    const buildings_2001_2022_JS = buildingsHelper.getBuildingByYear(filteredBuildingsWithYear, 2001, 2022);

    buildings_1200_1300 = L.geoJSON(buildings_1200_1300_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1301_1605 = L.geoJSON(buildings_1301_1605_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1651_1765 = L.geoJSON(buildings_1651_1765_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1766_1815 = L.geoJSON(buildings_1766_1815_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1816_1916 = L.geoJSON(buildings_1816_1916_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1917_1950 = L.geoJSON(buildings_1917_1950_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_1951_2000 = L.geoJSON(buildings_1951_2000_JS, { onEachFeature: onEachBuilding }).addTo(map);;
    buildings_2001_2022 = L.geoJSON(buildings_2001_2022_JS, { onEachFeature: onEachBuilding }).addTo(map);;

    const apartmentsArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'apartments');
    const bankArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'bank');
    const bridgeArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'bridge');
    const chapelArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'chapel');
    const churchArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'church');
    const cinemaArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'cinema');
    const civicArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'civic');
    const clinicArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'clinic');
    const collegeArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'college');
    const commercialArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'commercial');
    const constructionArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'construction');
    const dovecoteArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'dovecote');
    const detachedArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'detached');
    const fireStationArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'fire_station');
    const garageArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'garage');
    const governmentArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'government');
    const hospitalArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'hospital');
    const hotelArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'hotel');
    const houseArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'house');
    const industrialArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'industrial');
    const officeArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'office');
    const publicArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'public');
    const residentialArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'residential');
    const retailArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'retail');
    const roofArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'roof');
    const ruinsArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'ruins');
    const schoolArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'school');
    const serviceArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'service');
    const shedArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'shed');
    const stadiumArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'stadium');
    const teahouseArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'teahouse');
    const terraceArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'terrace');
    const towerArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'tower');
    const trainStationAarray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'train_station');
    const warehouseArray = streetsHelper.getStreetByType(buildingsGeojson, 'building', 'warehouse');

    apartmentsPoly = L.geoJSON(apartmentsArray, { onEachFeature: onEachDefaultLayer });
    bankPoly = L.geoJSON(bankArray, { onEachFeature: onEachDefaultLayer });
    bridgePoly = L.geoJSON(bridgeArray, { onEachFeature: onEachDefaultLayer });
    chapelPoly = L.geoJSON(chapelArray, { onEachFeature: onEachDefaultLayer });
    churchPoly = L.geoJSON(churchArray, { onEachFeature: onEachDefaultLayer });
    cinemaPoly = L.geoJSON(cinemaArray, { onEachFeature: onEachDefaultLayer });
    civicPoly = L.geoJSON(civicArray, { onEachFeature: onEachDefaultLayer });
    clinicPoly = L.geoJSON(clinicArray, { onEachFeature: onEachDefaultLayer });
    collegePoly = L.geoJSON(collegeArray, { onEachFeature: onEachDefaultLayer });
    commercialPoly = L.geoJSON(commercialArray, { onEachFeature: onEachDefaultLayer });
    constructionPoly = L.geoJSON(constructionArray, { onEachFeature: onEachDefaultLayer });
    dovecotePoly = L.geoJSON(dovecoteArray, { onEachFeature: onEachDefaultLayer });
    Detached = L.geoJSON(detachedArray, { onEachFeature: onEachDefaultLayer });
    fireStationPoly = L.geoJSON(fireStationArray, { onEachFeature: onEachDefaultLayer });
    garagePoly = L.geoJSON(garageArray, { onEachFeature: onEachDefaultLayer });
    governmentPoly = L.geoJSON(governmentArray, { onEachFeature: onEachDefaultLayer });
    hospitalPoly = L.geoJSON(hospitalArray, { onEachFeature: onEachDefaultLayer });
    hotelPoly = L.geoJSON(hotelArray, { onEachFeature: onEachDefaultLayer });
    housePoly = L.geoJSON(houseArray, { onEachFeature: onEachDefaultLayer });
    industrialPoly = L.geoJSON(industrialArray, { onEachFeature: onEachDefaultLayer });
    officePoly = L.geoJSON(officeArray, { onEachFeature: onEachDefaultLayer });
    publicPoly = L.geoJSON(publicArray, { onEachFeature: onEachDefaultLayer });
    residentialPoly = L.geoJSON(residentialArray, { onEachFeature: onEachDefaultLayer });
    retailPoly = L.geoJSON(retailArray, { onEachFeature: onEachDefaultLayer });
    roofPoly = L.geoJSON(roofArray, { onEachFeature: onEachDefaultLayer });
    ruinsPoly = L.geoJSON(ruinsArray, { onEachFeature: onEachDefaultLayer });
    schoolPoly = L.geoJSON(schoolArray, { onEachFeature: onEachDefaultLayer });
    servicePoly = L.geoJSON(serviceArray, { onEachFeature: onEachDefaultLayer });
    shedPoly = L.geoJSON(shedArray, { onEachFeature: onEachDefaultLayer });
    stadiumPoly = L.geoJSON(stadiumArray, { onEachFeature: onEachDefaultLayer });
    teahousePoly = L.geoJSON(teahouseArray, { onEachFeature: onEachDefaultLayer });
    terracePoly = L.geoJSON(terraceArray, { onEachFeature: onEachDefaultLayer });
    towerPoly = L.geoJSON(towerArray, { onEachFeature: onEachDefaultLayer });
    trainStationPoly = L.geoJSON(trainStationAarray, { onEachFeature: onEachDefaultLayer });
    warehousePoly = L.geoJSON(warehouseArray, { onEachFeature: onEachDefaultLayer });

    // Abandoned
    const abandonedGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'abandoned', 'yes');
    abandoned = L.geoJSON(abandonedGeojson, { onEachFeature: onEachBuilding });

    // Vacant
    const vacantGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'vacant', 'yes');
    vacant = L.geoJSON(vacantGeojson, { onEachFeature: onEachBuilding });

    // Historic
    const historicChurchGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'church');
    const historicRuinsGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'ruins');
    const historicTowerGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'tower');
    const historicWatermillGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'watermill');
    const historicYesGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'yes');
    const historicArcheologicalGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'archaeological_site');
    const historicHouseGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'house');
    const historicCastleGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'historic', 'castle');

    historicChurch = L.geoJSON(historicChurchGeojson, { onEachFeature: onEachBuilding });
    historicRuins = L.geoJSON(historicRuinsGeojson, { onEachFeature: onEachBuilding });
    historicTower = L.geoJSON(historicTowerGeojson, { onEachFeature: onEachBuilding });
    historicWatermill = L.geoJSON(historicWatermillGeojson, { onEachFeature: onEachBuilding });
    historicYes = L.geoJSON(historicYesGeojson, { onEachFeature: onEachBuilding });
    historicArcheologicalSite = L.geoJSON(historicArcheologicalGeojson, { onEachFeature: onEachBuilding });
    historicHouse = L.geoJSON(historicHouseGeojson, { onEachFeature: onEachBuilding });
    historicCastle = L.geoJSON(historicCastleGeojson, { onEachFeature: onEachBuilding });

    // Roof Types
    const roofDoubleSaltboxGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'double_saltbox');
    const roofFlatGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'flat');
    const roofGabledGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'gabled');
    const roofHalfHippedGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'half-hipped');
    const roofHippedGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'hipped');
    const roofPitchedGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'pitched');
    const roofPyramidalGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'pyramidal');
    const roofRoundGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'round');
    const roofSawGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'saw');
    const roofSkillionGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'skillion');
    const roofSlopedGeojson = streetsHelper.getStreetByType(buildingsGeojson, 'roof_shape', 'sloped');

    roofDoubleSaltbox = L.geoJSON(roofDoubleSaltboxGeojson, { onEachFeature: onEachBuilding });
    roofFlat = L.geoJSON(roofFlatGeojson, { onEachFeature: onEachBuilding });
    roofGabled = L.geoJSON(roofGabledGeojson, { onEachFeature: onEachBuilding });
    roofHalfHipped = L.geoJSON(roofHalfHippedGeojson, { onEachFeature: onEachBuilding });
    roofHipped = L.geoJSON(roofHippedGeojson, { onEachFeature: onEachBuilding });
    roofPitched = L.geoJSON(roofPitchedGeojson, { onEachFeature: onEachBuilding });
    roofPyramidal = L.geoJSON(roofPyramidalGeojson, { onEachFeature: onEachBuilding });
    roofRound = L.geoJSON(roofRoundGeojson, { onEachFeature: onEachBuilding });
    roofSaw = L.geoJSON(roofSawGeojson, { onEachFeature: onEachBuilding });
    roofSkillon = L.geoJSON(roofSkillionGeojson, { onEachFeature: onEachBuilding });
    roofSloped = L.geoJSON(roofSlopedGeojson, { onEachFeature: onEachBuilding });

    // Wheelchair
    const wheelchairGeojsonYes = streetsHelper.getStreetByType(buildingsGeojson, 'wheelchair', 'yes');
    const wheelchairGeojsonLimited = streetsHelper.getStreetByType(buildingsGeojson, 'wheelchair', 'limited');
    const wheelchairGeojsonNo = streetsHelper.getStreetByType(buildingsGeojson, 'wheelchair', 'no');

    wheelchairYes = L.geoJSON(wheelchairGeojsonYes, { onEachFeature: onEachBuilding });
    wheelchairLimited = L.geoJSON(wheelchairGeojsonLimited, { onEachFeature: onEachBuilding });
    wheelchairNo = L.geoJSON(wheelchairGeojsonNo, { onEachFeature: onEachBuilding });
}
/**
 * On each building feature
 */
function onEachBuilding (feature, layer)
{
    const colour = buildingsHelper.getBuildingsColour(feature.properties.NEWDATE);

    layer.setStyle({
        fillOpacity: 0.5,
        color: colour
    });

    layer.on('click', async function (e)
    {
        const { str, building } = buildingsHelper.getStringBuildingObject(feature.properties);

        window.buildingsMap.building = building;

        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);

        L.DomEvent.stopPropagation(e);

        // Check if the building has any stories
        // await axios.get('/check-building', {
        //     params: {
        //         osm_id: building.osm_id
        //     }
        // })
        // .then(response => {
        //     console.log('check_building', response);
        //
        //     if (response.data.success) {
        //         window.buildingsMap.stories = response.data.stories;
        //     }
        // })
        // .catch(error => {
        //     console.error('check_building', error);
        // });

        window.buildingsMap.buildingsKey++;
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        const colour = buildingsHelper.getBuildingsColour(feature.properties.NEWDATE, feature.properties.hasStory);
        layer.setStyle({
            fillOpacity: 0.5,
            color: colour
        });
    });
}
/**
 * Use the array of streets from the backend
 *
 * Create many layers that can be turned on and off
 */
function addStreetLayers (streetsArray)
{
    // 1. Create empty L.geoJSON layers
    // All Types
    streetTypes = L.geoJSON(null, { onEachFeature: onEachStreetType });

    // Each Filtered Type (10)
    corridor = L.geoJSON(null, { onEachFeature: onEachStreetType });
    footway = L.geoJSON(null, { onEachFeature: onEachStreetType });
    path = L.geoJSON(null, { onEachFeature: onEachStreetType });
    pedestrian = L.geoJSON(null, { onEachFeature: onEachStreetType });
    residential = L.geoJSON(null, { onEachFeature: onEachStreetType });
    secondary = L.geoJSON(null, { onEachFeature: onEachStreetType });
    service = L.geoJSON(null, { onEachFeature: onEachStreetType });
    steps = L.geoJSON(null, { onEachFeature: onEachStreetType });
    tertiary = L.geoJSON(null, { onEachFeature: onEachStreetType });
    unclassified = L.geoJSON(null, { onEachFeature: onEachStreetType });

    // 2. Create a filtered geojson array of each layer we want to use
    // Get array of streets of column name "highway"
    const filteredStreetsArray = streetsHelper.getFilteredArray(streetsArray, 'highway');
    // Using street.highway, create filteredArray for various highway values
    const corridorArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'corridor');
    const footwayArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'footway');
    const pathArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'path');
    const pedestrianArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'pedestrian');
    const residentialArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'residential');
    const secondaryArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'secondary');
    const serviceArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'service');
    const stepsArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'steps');
    const tertiaryArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'tertiary');
    const unclassifiedArray = streetsHelper.getStreetByType(filteredStreetsArray, 'highway', 'unclassified');

    // 3.1 Add the filtered array to the empty geojson layer
    streetTypes.addData({ features: filteredStreetsArray, type: "FeatureCollection" });
    // 3.2 Add each filtered value type to the empty geojson layer
    corridor.addData({ features: corridorArray, type: "FeatureCollection" });
    footway.addData({ features: footwayArray, type: "FeatureCollection" });
    path.addData({ features: pathArray, type: "FeatureCollection" });
    pedestrian.addData({ features: pedestrianArray, type: "FeatureCollection" });
    residential.addData({ features: residentialArray, type: "FeatureCollection" });
    secondary.addData({ features: secondaryArray, type: "FeatureCollection" });
    service.addData({ features: serviceArray, type: "FeatureCollection" });
    steps.addData({ features: stepsArray, type: "FeatureCollection" });
    tertiary.addData({ features: tertiaryArray, type: "FeatureCollection" });
    unclassified.addData({ features: unclassifiedArray, type: "FeatureCollection" });
    // 3.3 Add the streetMaterial column type
    // streetsMaterial = L.geoJSON(null, {
    //     onEachFeature: onEachStreetMaterial
    // });
    // const filteredStreetsMaterial = streetsHelper.getFilteredArray(streetsArray, 'material');
    // streetsMaterial.addData({ features: filteredStreetsMaterial, type: "FeatureCollection" });
}

/**
 *
 */
function onEachStreetType (feature, layer)
{
    const colour = streetsHelper.getStreetColour(feature.properties.highway);

    if (colour)
    {
        layer.setStyle({
            fillOpacity: 0.5,
            color: colour
        });
    }

    layer.on('click', function (e)
    {
        const { str, street } = streetsHelper.getStringObject(feature.properties);
        window.buildingsMap.street = street;
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        const colour = streetsHelper.getStreetColour(feature.properties['highway']);
        if (colour)
        {
            layer.setStyle({
                fillOpacity: 0.5,
                color: colour
            });
        }
        layer.setStyle({
            fillOpacity: 0,
            color: colour
        });
    });
}

function addMonumentsLayer (monumentsArray)
{
    monuments = L.geoJSON(null, {
        onEachFeature: onEachMonument,
        pointToLayer: createPurpledotIcon
    });
    monuments.addData({
        features: monumentsArray,
        type: "FeatureCollection"
    });
}

function addBridgesLayer (bridgesArray)
{
    const greensBridgeJS = streetsHelper.getStreetByType(bridgesArray, 'name', 'Green\'s Bridge');
    const stFrancisBridgeJS = streetsHelper.getStreetByType(bridgesArray, 'name', 'Saint Francis Bridge');
    const ladyDesartBridgeJS = streetsHelper.getStreetByType(bridgesArray, 'name', 'Lady Desart Bridge');
    const johnsBridgeJS = streetsHelper.getStreetByType(bridgesArray, 'name', 'John\'s Bridge');

    greensBridge = L.geoJSON(greensBridgeJS, { onEachFeature: onEachBridge });
    stFrancisBridge = L.geoJSON(stFrancisBridgeJS, { onEachFeature: onEachBridge });
    ladyDesartBridge = L.geoJSON(ladyDesartBridgeJS, { onEachFeature: onEachBridge });
    johnsBridge = L.geoJSON(johnsBridgeJS, { onEachFeature: onEachBridge });
}

function addTalbotsTowerLayer (talbotsTowerArray)
{
    talbotsTower = L.geoJSON(talbotsTowerArray, {
        onEachFeature: onEachBridge
    }).addTo(map);
}

function addACAsLayer (acasArray)
{
    acas = L.geoJSON(acasArray, {
        onEachFeature: onEachACA
    });
}

function onEachACA (feature, layer)
{
    layer.on('click', function (e)
    {
        const { str } = acasHelper.getString(feature.properties);
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function addCityBoundaryLayer (cityBoundaryArray)
{
    cityBoundary = L.geoJSON(cityBoundaryArray, {
        onEachFeature: onCityBoundary
    });
}

function addParishesLayer (parishesArray)
{
    parishes = L.geoJSON(parishesArray, {
        onEachFeature: onEachParish
    });

    const stMarysParishArray = streetsHelper.getStreetByType(parishesArray.features, 'Name', 'Parish of St Mary\'s');
    const stCanicesParishArray = streetsHelper.getStreetByType(parishesArray.features, 'Name', 'Parish of St Canice\'s');
    const stPatricksParishArray = streetsHelper.getStreetByType(parishesArray.features, 'Name', 'Parish of St Patrick\'s');
    const stMaulsParishArray = streetsHelper.getStreetByType(parishesArray.features, 'Name', 'Parish of St Maul\'s');
    const stJohnsParishArray = streetsHelper.getStreetByType(parishesArray.features, 'Name', 'Parish of St John\'s');

    stMarysParish = L.geoJSON(stMarysParishArray, { onEachFeature: onEachParish });
    stCanicesParish = L.geoJSON(stCanicesParishArray, { onEachFeature: onEachParish });
    stPatricksParish = L.geoJSON(stPatricksParishArray, { onEachFeature: onEachParish });
    stMaulsParish = L.geoJSON(stMaulsParishArray, { onEachFeature: onEachParish });
    stJohnsParish = L.geoJSON(stJohnsParishArray, { onEachFeature: onEachParish });
}

function addFloodZoneLayer (floodZoneArray)
{
    floodzone = L.geoJSON(floodZoneArray, {
        onEachFeature: onEachFloodZone
    });
}

function addNaturalOsm (naturalOsmGeojson)
{
    naturalOsm = L.geoJSON(naturalOsmGeojson, {
        onEachFeature: onEachDefaultLayer
    });
}

function addLeisureOsm (leisureGeojson)
{
    leisure = L.geoJSON(leisureGeojson, {
        onEachFeature: onEachDefaultLayer
    });
    const gardenArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'garden');
    const outdoorSeatingArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'outdoor_seating');
    const parkArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'park');
    const pitchArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'pitch');
    const playgroundArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'playground');
    const sportsCentreArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'sports_centre');
    const stadiumArray = streetsHelper.getStreetByType(leisureGeojson.features, 'leisure', 'stadium');
    garden = L.geoJSON(gardenArray, { onEachFeature: onEachDefaultLayer });
    outdoor_seating = L.geoJSON(outdoorSeatingArray, { onEachFeature: onEachDefaultLayer });
    park = L.geoJSON(parkArray, { onEachFeature: onEachDefaultLayer });
    pitch = L.geoJSON(pitchArray, { onEachFeature: onEachDefaultLayer });
    playground = L.geoJSON(playgroundArray, { onEachFeature: onEachDefaultLayer });
    sports_centre = L.geoJSON(sportsCentreArray, { onEachFeature: onEachDefaultLayer });
    stadium = L.geoJSON(stadiumArray, { onEachFeature: onEachDefaultLayer });
}

function addRPSLayer (rpsGeojson)
{
    rps = L.geoJSON(rpsGeojson, {
        onEachFeature: onEachDefaultLayer,
        pointToLayer: createGreyDotIcon
    });
}

function addAmenitiesOsm (amenitiesGeojson)
{
    const bankArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'bank');
    const barArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'bar');
    const bicyclingParkingArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'bicycle_parking');
    const cafeArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'cafe');
    const carWashArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'car_wash');
    const cinemaArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'cinema');
    // const clinicArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'clinic'); // not found in the data
    const collegeArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'college');
    const communityCentreArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'community_centre');
    const courtHouseArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'courthouse');
    const coworkingSpaceArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'coworking_space');
    const fastFoodArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'fast_food');
    const fireStationArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'fire_station');
    const fuelArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'fuel');
    const graveyardArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'grave_yard');
    const libraryArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'library');
    const kindergartenArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'kindergarten');
    const parkingArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'parking');
    const pharmacyArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'pharmacy');
    const placeOfWorshipArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'place_of_worship');
    const policeArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'police');
    const postOfficeArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'post_office');
    const pubArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'pub');
    const restaurantArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'restaurant');
    const schoolArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'school');
    const shelterArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'shelter');
    const socialFacilityArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'social_facility');
    const taxiArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'taxi');
    const theatreArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'theatre');
    const townhallArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'townhall');
    const vererinaryArray = streetsHelper.getStreetByType(amenitiesGeojson.features, 'amenity', 'veterinary');

    bankAmenity = L.geoJSON(bankArray, { onEachFeature: onEachDefaultLayer });
    barAmenity = L.geoJSON(barArray, { onEachFeature: onEachDefaultLayer });
    bicyclingParkingAmenity = L.geoJSON(bicyclingParkingArray, { onEachFeature: onEachDefaultLayer });
    cafeAmenity = L.geoJSON(cafeArray, { onEachFeature: onEachDefaultLayer });
    carWashAmenity = L.geoJSON(carWashArray, { onEachFeature: onEachDefaultLayer });
    cinemaAmenity = L.geoJSON(cinemaArray, { onEachFeature: onEachDefaultLayer });
    // clinicAmenity = L.geoJSON(clinicArray, { onEachFeature: onEachDefaultLayer });
    collegeAmenity = L.geoJSON(collegeArray, { onEachFeature: onEachDefaultLayer });
    communityCentreAmenity = L.geoJSON(communityCentreArray, { onEachFeature: onEachDefaultLayer });
    courtHouseAmenity = L.geoJSON(courtHouseArray, { onEachFeature: onEachDefaultLayer });
    coworkingSpaceAmenity = L.geoJSON(coworkingSpaceArray, { onEachFeature: onEachDefaultLayer });
    fastFoodAmenity = L.geoJSON(fastFoodArray, { onEachFeature: onEachDefaultLayer });
    fireStationAmenity = L.geoJSON(fireStationArray, { onEachFeature: onEachDefaultLayer });
    fuelAmenity = L.geoJSON(fuelArray, { onEachFeature: onEachDefaultLayer });
    graveyardAmenity = L.geoJSON(graveyardArray, { onEachFeature: onEachDefaultLayer });
    libraryAmenity = L.geoJSON(libraryArray, { onEachFeature: onEachDefaultLayer });
    kindergartenAmenity = L.geoJSON(kindergartenArray, { onEachFeature: onEachDefaultLayer });
    parkingAmenity = L.geoJSON(parkingArray, { onEachFeature: onEachDefaultLayer });
    pharmacyAmenity = L.geoJSON(pharmacyArray, { onEachFeature: onEachDefaultLayer });
    placeOfWorshipAmenity = L.geoJSON(placeOfWorshipArray, { onEachFeature: onEachDefaultLayer });
    policeAmenity = L.geoJSON(policeArray, { onEachFeature: onEachDefaultLayer });
    postOfficeAmenity = L.geoJSON(postOfficeArray, { onEachFeature: onEachDefaultLayer });
    pubAmenity = L.geoJSON(pubArray, { onEachFeature: onEachDefaultLayer });
    restaurantAmenity = L.geoJSON(restaurantArray, { onEachFeature: onEachDefaultLayer });
    schoolAmenity = L.geoJSON(schoolArray, { onEachFeature: onEachDefaultLayer });
    shelterAmenity = L.geoJSON(shelterArray, { onEachFeature: onEachDefaultLayer });
    socialFacilityAmenity = L.geoJSON(socialFacilityArray, { onEachFeature: onEachDefaultLayer });
    taxiAmenity = L.geoJSON(taxiArray, { onEachFeature: onEachDefaultLayer });
    theatreAmenity = L.geoJSON(theatreArray, { onEachFeature: onEachDefaultLayer });
    townhallAmenity = L.geoJSON(townhallArray, { onEachFeature: onEachDefaultLayer });
    vetinaryAmenity = L.geoJSON(vererinaryArray, { onEachFeature: onEachDefaultLayer });
}

function addOsmBoundaries (osmBoundariesGeojson)
{
    osmBoundaries = L.geoJSON(osmBoundariesGeojson, {
        onEachFeature: onEachDefaultLayer
    });
}
/**
 * Default layer:
 *
 * Blue colour,
 * Shows generic popup,
 * Yellow on hover.
 */
function onEachDefaultLayer (feature, layer)
{
    layer.on('click', function (e)
    {
        const { str } = streetsHelper.getStringObject(feature.properties);

        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function addLandUseTypes (landUseGeoJson)
{
    landUseTypes = L.geoJSON(landUseGeoJson, {
        onEachFeature: onEachLandUseType
    });

    const brownFieldArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'brownfield');
    const cemeteryArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'cemetery');
    const commercialArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'commercial');
    const constructionArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'construction');
    const flowerBedArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'flowerbed');
    const forestArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'forest');
    const governmentArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'government');
    const grassArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'grass');
    const industrialArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'industrial');
    const meadowArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'meadow');
    const orchardArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'orchard');
    const railwayArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'railway');
    const religiousArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'religious');
    const resLandUseArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'residential');
    const retailArray = streetsHelper.getStreetByType(landUseGeoJson.features, 'landuse', 'retail');

    brownfield = L.geoJSON(brownFieldArray, { onEachFeature: onEachLandUseType });
    cemetery = L.geoJSON(cemeteryArray, { onEachFeature: onEachLandUseType });
    commercial = L.geoJSON(commercialArray, { onEachFeature: onEachLandUseType });
    construction = L.geoJSON(constructionArray, { onEachFeature: onEachLandUseType });
    flowerbed = L.geoJSON(flowerBedArray, { onEachFeature: onEachLandUseType });
    forest = L.geoJSON(forestArray, { onEachFeature: onEachLandUseType });
    government = L.geoJSON(governmentArray, { onEachFeature: onEachLandUseType });
    grass = L.geoJSON(grassArray, { onEachFeature: onEachLandUseType });
    industrial = L.geoJSON(industrialArray, { onEachFeature: onEachLandUseType });
    meadow = L.geoJSON(meadowArray, { onEachFeature: onEachLandUseType });
    orchard = L.geoJSON(orchardArray, { onEachFeature: onEachLandUseType });
    railway = L.geoJSON(railwayArray, { onEachFeature: onEachLandUseType });
    religious = L.geoJSON(religiousArray, { onEachFeature: onEachLandUseType });
    residentialLandUse = L.geoJSON(resLandUseArray, { onEachFeature: onEachLandUseType });
    retail = L.geoJSON(retailArray, { onEachFeature: onEachLandUseType });
}

function onEachLandUseType (feature, layer)
{
    const colour = landUseHelper.getColour(feature.properties.landuse);

    layer.setStyle({
        fillOpacity: 0.4,
        color: colour
    });

    layer.on('click', function (e)
    {
        const { str, bridge } = streetsHelper.getStringObject(feature.properties);

        // window.buildingsMap.bridge = bridge;
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        // window.buildingsMap.buildingsKey++;
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: colour
        });
    });
}

function onEachFloodZone (feature, layer)
{
    layer.on('click', function (e)
    {
        const str = "Apparently this area is at risk of flooding.";
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function onEachParish (feature, layer)
{
    layer.on('click', function (e) {
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(feature.properties.Name)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function onCityBoundary (feature, layer)
{
    layer.on('click', function (e) {
        const str = "1842 Municipal Boundary of Kilkenny city";
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function onEachBridge (feature, layer)
{
    layer.on('click', function (e)
    {
        const { str, bridge } = bridgesHelper.getString(feature.properties);
        window.buildingsMap.bridge = bridge;
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        window.buildingsMap.buildingsKey++;
        L.DomEvent.stopPropagation(e);
    });

    layer.on("mouseover", function(e) {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e) {
        layer.setStyle({
            fillOpacity: 0.5,
            color: "#3388ff"
        });
    });
}

function onEachMonument (feature, layer)
{
    layer.on('click', function (e)
    {
        const { str } = monumentsHelper.getString(feature.properties);
        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);
        L.DomEvent.stopPropagation(e);
    });
}

function addPointsLayers (pointsArray)
{
    // 1. Create empty L.geoJSON layers
    // All Types
    points = L.geoJson(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });

    // Each Filtered Type (41)
    artsCentre = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    atm = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    bank = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    bar = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    bench = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    bicyclingParking = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    cafe = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    carWash = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    chargingStation = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    clock = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    college = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    communityCentre = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    dentist = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    doctors = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    fastFood = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    fountain = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    gritBin = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    kitchen = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    library = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    marketplace = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    motorcycleParking = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    musicSchool = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    parking = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    parkingEntrance = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    pharmacy = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    placeOfWorship = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    postBox = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    postOffice = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    pub = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    publicOffice = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    recycling = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    restaurant = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    socialCentre = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    studio = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    telephone = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    theatre = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    toilets = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    training = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    vendingMachines = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    wasteBasket = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });
    wateringPlace = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createPurpledotIcon });

    // 2. Create a filtered geojson array of each layer we want to use
    // Get array of streets of column name "highway"
    const filteredPointsArray = streetsHelper.getFilteredArray(pointsArray, 'amenity');
    // Use filtered array to find values within the column
    const artsCentreArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'arts_centre');
    const atmArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'atm');
    const bankArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'bank');
    const barArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'bar');
    const benchArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'bench');
    const bicycleParkingArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'bicycle_parking');
    const cafeArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'cafe');
    const carWashArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'car_wash');
    const chargingStationArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'charging_station');
    const clockArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'clock');
    const collegeArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'college');
    const communityCentreArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'community_centre');
    const dentistArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'dentist');
    const doctorArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'doctors');
    const fastFoodArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'fast_food');
    const fountainArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'fountain');
    const gritBinArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'grit_bin');
    const kitchenArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'kitchen');
    const libraryArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'library');
    const marketPlaceArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'marketplace');
    const motorcycleParkingArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'motorcycle_parking');
    const musicSchoolArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'music_school'); // maybe remove _
    const parkingArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'parking');
    const parkingEntranceArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'parking_entrance');
    const pharmacyArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'pharmacy');
    const placeOfWorshipArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'place_of_worship');
    const postBoxArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'post_box');
    const postOfficeArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'post_office');
    const pubArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'pub');
    const publicOfficeArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'public_office');
    const recyclingArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'recycling');
    const restaurantArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'restaurant');
    const socialCentreArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'social_centre');
    const studioArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'studio');
    const telephoneArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'telephone');
    const theatreArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'theatre');
    const toiletsArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'toilets');
    const trainingArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'training');
    const vendingMachineArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'vending_machine');
    const wasteBasketArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'waste_basket');
    const wateringPlaceArray = streetsHelper.getStreetByType(filteredPointsArray, 'amenity', 'watering_place');

    points.addData({ features: filteredPointsArray, type: "FeatureCollection" });

    atm.addData({ features: atmArray, type: "FeatureCollection" });
    artsCentre.addData({ features: artsCentreArray, type: "FeatureCollection" });
    bank.addData({ features: bankArray, type: "FeatureCollection" });
    bar.addData({ features: barArray, type: "FeatureCollection" });
    bench.addData({ features: benchArray, type: "FeatureCollection" });
    bicyclingParking.addData({ features: bicycleParkingArray, type: "FeatureCollection" });
    cafe.addData({ features: cafeArray, type: "FeatureCollection" });
    carWash.addData({ features: carWashArray, type: "FeatureCollection" });
    chargingStation.addData({ features: chargingStationArray, type: "FeatureCollection" });
    clock.addData({ features: clockArray, type: "FeatureCollection" });
    college.addData({ features: collegeArray, type: "FeatureCollection" });
    communityCentre.addData({ features: communityCentreArray, type: "FeatureCollection" });
    dentist.addData({ features: dentistArray, type: "FeatureCollection" });
    doctors.addData({ features: doctorArray, type: "FeatureCollection" });
    fastFood.addData({ features: fastFoodArray, type: "FeatureCollection" });
    fountain.addData({ features: fountainArray, type: "FeatureCollection" });
    gritBin.addData({ features: gritBinArray, type: "FeatureCollection" });
    kitchen.addData({ features: kitchenArray, type: "FeatureCollection" });
    library.addData({ features: libraryArray, type: "FeatureCollection" });
    marketplace.addData({ features: marketPlaceArray, type: "FeatureCollection" });
    motorcycleParking.addData({ features: motorcycleParkingArray, type: "FeatureCollection" });
    musicSchool.addData({ features: musicSchoolArray, type: "FeatureCollection" });
    parking.addData({ features: parkingArray, type: "FeatureCollection" });
    parkingEntrance.addData({ features: parkingEntranceArray, type: "FeatureCollection" });
    pharmacy.addData({ features: pharmacyArray, type: "FeatureCollection" });
    placeOfWorship.addData({ features: placeOfWorshipArray, type: "FeatureCollection" });
    postBox.addData({ features: postBoxArray, type: "FeatureCollection" });
    postOffice.addData({ features: postOfficeArray, type: "FeatureCollection" });
    pub.addData({ features: pubArray, type: "FeatureCollection" });
    publicOffice.addData({ features: publicOfficeArray, type: "FeatureCollection" });
    recycling.addData({ features: recyclingArray, type: "FeatureCollection" });
    restaurant.addData({ features: restaurantArray, type: "FeatureCollection" });
    socialCentre.addData({ features: socialCentreArray, type: "FeatureCollection" });
    studio.addData({ features: studioArray, type: "FeatureCollection" });
    telephone.addData({ features: telephoneArray, type: "FeatureCollection" });
    theatre.addData({ features: theatreArray, type: "FeatureCollection" });
    toilets.addData({ features: toiletsArray, type: "FeatureCollection" });
    training.addData({ features: trainingArray, type: "FeatureCollection" });
    wasteBasket.addData({ features: wasteBasketArray, type: "FeatureCollection" });
    vendingMachines.addData({ features: vendingMachineArray, type: "FeatureCollection" });
    wateringPlace.addData({ features: wateringPlaceArray, type: "FeatureCollection" });
}

function onEachPoint (feature, layer)
{
    layer.on('click', async function (e)
    {
        const str = wallsHelper.getWallsString(feature.properties);

        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);

        L.DomEvent.stopPropagation(e);
    });
}

function addWallsLayer (wallsArray, pointsArray)
{
    walls = L.geoJSON(null, {
        onEachFeature: onEachWalls
    }).addTo(map);

    walls.addData({ features: wallsArray, type: "FeatureCollection" });

    // Parent array
    historic = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });

    // Child arrays from historic
    archaeological = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    cityGate = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    memorial = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    oghamStone = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    streetLamp = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    tomb = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });
    uncategorised = L.geoJSON(null, { onEachFeature: onEachWalls, pointToLayer: createPurpledotIcon });

    // All historic points
    const filteredHistoricArray = streetsHelper.getFilteredArray(pointsArray, 'historic');

    historic.addData({ features: filteredHistoricArray, type: "FeatureCollection" });

    // Filter all historic points by values
    const archeologicalArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'archaeological_site');
    const cityGateArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'city_gate');
    const memorialArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'memorial');
    const oghamStoneArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'ogham_stone');
    const streetLampArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'street_lamp');
    const tombArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'tomb');
    const uncategorisedArray = streetsHelper.getStreetByType(pointsArray, 'historic', 'yes');

    archaeological.addData({ features: archeologicalArray, type: "FeatureCollection" });
    cityGate.addData({ features: cityGateArray, type: "FeatureCollection" });
    memorial.addData({ features: memorialArray, type: "FeatureCollection "});
    oghamStone.addData({ features: oghamStoneArray, type: "FeatureCollection" });
    streetLamp.addData({ features: streetLampArray, type: "FeatureCollection" });
    tomb.addData({ features: tombArray, type: "FeatureCollection" });
    uncategorised.addData({ features: uncategorisedArray, type: "FeatureCollection" });
}

function addArtLayer (pointsArray)
{
    art = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });

    // All art points
    const filteredArtArray = streetsHelper.getFilteredArray(pointsArray, 'artwork_type');

    historic.addData({ features: filteredArtArray, type: "FeatureCollection" });
    graffiti = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });
    mural = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });
    relief = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });
    sculpture = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });
    stainedGlassWindow = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });
    statue = L.geoJSON(null, { onEachFeature: onEachPoint, pointToLayer: createGreyDotIcon });

    // Filtered art points
    const graffitiArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'graffiti');
    const muralArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'mural');
    const reliefArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'relief');
    const scultpureArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'sculpture');
    const stainedGlassArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'stained-glass_window');
    const statueArray = streetsHelper.getStreetByType(filteredArtArray, 'artwork_type', 'mural');

    graffiti.addData({ features: graffitiArray, type: "FeatureCollection" });
    mural.addData({ features: muralArray, type: "FeatureCollection" });
    relief.addData({ features: reliefArray, type: "FeatureCollection" });
    sculpture.addData({ features: scultpureArray, type: "FeatureCollection" });
    stainedGlassWindow.addData({ features: stainedGlassArray, type: "FeatureCollection" });
    statue.addData({ features: statueArray, type: "FeatureCollection" });
}
/**
 * On each walls feature
 */
function onEachWalls (feature, layer)
{
    layer.on("mouseover", function(e)
    {
        layer.setStyle({
            fillOpacity: 0.4,
            color: 'yellow'
        });
    });

    layer.on("mouseout",function(e)
    {
        layer.setStyle({
            fillOpacity: 0.5,
            color: '#3388ff'
        });
    });

    layer.on('click', async function (e)
    {
        const str = wallsHelper.getWallsString(feature.properties);

        L.popup(mapHelper.popupOptions)
            .setLatLng(e.latlng)
            .setContent(str)
            .openOn(map);

        L.DomEvent.stopPropagation(e);
    });
}

function createLegends ()
{
    // Buildings: Year of construction
    let legend = L.control({
        position: 'bottomleft'
    });

    legend.onAdd = function (map) {
        let legend = L.DomUtil.create('div', 'info legend');
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1200) + '"></i> 1200 - 1300' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1301) + '"></i> 1301 - 1650' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1651) + '"></i> 1651 - 1765' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1790) + '"></i> 1766 - 1815' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1816) + '"></i> 1816 - 1916' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1816) + '"></i> 1917 - 1950' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1816) + '"></i> 1951 - 2000' + '<br>';
        legend.innerHTML += '<i style="background:' + buildingsHelper.getBuildingsColour(1816) + '"></i> 2001 - 2022' + '<br>';
        return legend;
    };

    legend.addTo(map);
}
</script>

<style>
    #buildings-map {
        height: 100%;
        margin: 0;
        position: relative;
        width: 70%;
    }

    @media screen and (max-width: 768px)
    {
        #buildings-map {
            width: 100%;
        }
    }

    .leaflet-bottom {
        z-index: 1;
    }
    .leaflet-marker-icon {
        border-radius: 20px;
    }
    .mi {
        height: 100%;
        margin: auto;
        display: flex;
        justify-content: center;
        border-radius: 20px;
    }
    .leaflet-control {
        pointer-events: visiblePainted !important;
    }
    .leaflet-popup-content {
        padding: 20px !important;
        /*width: 550px !important;*/
    }
    .leaflet-control-layers-toggle.leaflet-layerstree-named-toggle {
        margin: 2px 5px;
        width: auto;
        height: auto;
        background-image: none;
    }
    .leaflet-layerstree-node {
    }
    .leaflet-layerstree-header input{
        margin-left: 0px;
    }
    .leaflet-layerstree-header {
    }
    .leaflet-layerstree-header-pointer {
        cursor: pointer;
    }
    .leaflet-layerstree-header label {
        display: inline-block;
        cursor: pointer;
    }
    .leaflet-layerstree-header-label {
    }
    .leaflet-layerstree-header-name {
    }
    .leaflet-layerstree-header-space {
    }
    .leaflet-layerstree-children {
        padding-left: 10px;
    }
    .leaflet-layerstree-children-nopad {
        padding-left: 0px;
    }
    .leaflet-layerstree-closed {
    }
    .leaflet-layerstree-opened {
    }
    .leaflet-layerstree-hide {
        display: none;
    }
    .leaflet-layerstree-nevershow {
        display: none;
    }
    .leaflet-layerstree-expand-collapse {
        cursor: pointer;
    }
</style>
