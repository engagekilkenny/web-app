<?php

namespace App\Http\Controllers\GlobalMap;

use App\Models\Engage\Building;
use App\Traits\FilterPhotosByGeoHashTrait;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GlobalMapController extends Controller
{
    use FilterPhotosByGeoHashTrait;

    /**
     * Get the buildings geojson file from the database
     *
     * Load all other layers from file
     */
    public function getAllLayers ()
    {
        $buildings = Building::all();

        $buildingsGeojson = [
            'type' => 'FeatureCollection',
            "name" => "kilkenny_roi",
            "crs" => [
                "type" => "name",
                "properties" => [
                    "name" => "urn:ogc:def:crs:OGC:1.3:CRS84"
                ]
            ],
            'features'  => []
        ];

        foreach ($buildings as $building)
        {
            $buildingsGeojson['features'][] = [
                'type' => 'Feature',
                'properties' => json_decode($building->attributes, true),
                 "geometry" => [
                     "type" => "MultiPolygon",
                     "coordinates" => json_decode($building->polygon, true)
                 ]
            ];
        }

        $streets = file_get_contents(public_path('/js/geojson/streets.geojson'));
        $walls = file_get_contents(public_path('/js/geojson/newWalls2.geojson'));
        $points = file_get_contents(public_path('/js/geojson/points.geojson'));
        $monuments = file_get_contents(public_path('/js/geojson/monuments.geojson'));
        $bridges = file_get_contents(public_path('/js/geojson/bridges.geojson'));
        $talbotsTower = file_get_contents(public_path('/js/geojson/talbotsTower.geojson'));
        $acas = file_get_contents(public_path('/js/geojson/acas.geojson'));
        $cityboundary = file_get_contents(public_path('/js/geojson/cityboundary.geojson'));
        $parishes = file_get_contents(public_path('/js/geojson/parishes.geojson'));
        $floodzone = file_get_contents(public_path('/js/geojson/floodzone.geojson'));
        $landUseTypes = file_get_contents(public_path('/js/geojson/landuseTypes.geojson'));
        $osmBoundaries = file_get_contents(public_path('/js/geojson/osmBoundaries.geojson'));
        $natural = file_get_contents(public_path('/js/geojson/naturalOSM.geojson'));
        $leisure = file_get_contents(public_path('/js/geojson/leisureOsm.geojson'));
        $amenities = file_get_contents(public_path('/js/geojson/buildingsWithAmenity.geojson'));

        return [
            'success' => true,
            'buildings' => $buildingsGeojson,
            'streets' => $streets,
            'walls' => $walls,
            'points' => $points,
            'monuments' => $monuments,
            'bridges' => $bridges,
            'talbotsTower' => $talbotsTower,
            'acas' => $acas,
            'cityBoundary' => $cityboundary,
            'parishes' => $parishes,
            'floodzone' => $floodzone,
            'landUseTypes' => $landUseTypes,
            'osmBoundaries' => $osmBoundaries,
            'natural' => $natural,
            'leisure' => $leisure,
            'amenities' => $amenities,
        ];
    }

//    /**
//     * Convert our photos object to a geojson array
//     *
//     * @param $photos
//     *
//     * @return array
//     */
//    protected function photosToGeojson ($photos) :array
//    {
//        $geojson = [
//            'type'      => 'FeatureCollection',
//            'features'  => null
//        ];
//
//        $features = [];
//
//        // Loop over all clusters and add each feature to the features array
//        // Todo - Remove duplication as 1 user may have uploaded many photos
//        foreach ($photos as $photo)
//        {
//            $showName = $showUsername = $teamName = false;
//
//            if ($photo->user)
//            {
//                $showName = $photo->user->show_name_maps;
//                $showUsername = $photo->user->show_username_maps;
//            }
//
//            if ($photo->team)
//            {
//                $teamName = $photo->team->name;
//            }
//
//            $feature = [
//                'type' => 'Feature',
//                'geometry' => [
//                    'type' => 'Point',
//                    'coordinates' => [$photo->lat, $photo->lon]
//                ],
//                'properties' => [
//                    'result_string' => $photo->verified >= 2 ? $photo->result_string : null,
//                    'filename' => $photo->verified >= 2 ? $photo->filename : '/assets/images/waiting.png',
//                    'datetime' => $photo->datetime,
//                    'cluster'  => false,
//                    'verified' => $photo->verified,
//                    'name'     => $showName ? $photo->user->name : null,
//                    'username' => $showUsername ? $photo->user->username : null,
//                    'team'     => $teamName ? $teamName : null
//                ]
//            ];
//
//            array_push($features, $feature);
//        }
//
//        $geojson['features'] = $features;
//
//        return $geojson;
//    }

//    /**
//     * Get photos point data at zoom levels 16 or above
//     *
//     * @return array
//     */
//    public function index (): array
//    {
//        $photos = $this->filterPhotosByGeoHash(
//            request()->zoom,
//            request()->bbox,
//            request()->layers ?: null
//        )->get();
//
//        return $this->photosToGeojson($photos);
//    }
}
