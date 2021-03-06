<?php

namespace App\Models;

use App\Models\AI\Annotation;
use App\Models\Litter\Categories\Art;
use App\Models\Litter\Categories\Brand;
use App\Models\Litter\Categories\Smoking;
use App\Models\Teams\Team;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    	'filename',
    	'model',
    	'datetime',
    	'lat',
        'lon',
        'verification',
        'verified',
        'result_string',
        'total_litter',

        'display_name',
    	'location',
    	'road',
    	'suburb',
    	'city',
    	'county',
    	'state_district',
    	'country',
    	'country_code',

        'city_id',
        'state_id',
        'country_id',

        'buildings_id',
        'cables_id',
        'pipes_id',
        'roofs_id',
        'signs_id',
        'walls_id',
        'windows_id',

        'platform',
        'bounding_box',
        'geohash',
        'team_id',

        // annotations
        'bbox_skipped',
        'skipped_by',
        'bbox_assigned_to',
        'wrong_tags',
        'wrong_tags_by',
        'bbox_verification_assigned_to',

        // Introduced after resizing images to 500x500
        'five_hundred_square_filepath',
        'bbox_500_assigned_to',

        'address_array'
    ];

    protected $appends = ['selected', 'picked_up'];

    protected $casts = ['datetime'];

    /**
     * Create an Accessor that adds ['selected' => false] to each record
     */
    public function getSelectedAttribute ()
    {
        return false;
    }

    /**
     * Wrapper around photo presence, for better readability
     */
    public function getPickedUpAttribute ()
    {
        return !$this->remaining;
    }

    /**
     * A photo can have many bounding boxes associated with it
     */
    public function boxes ()
    {
        return $this->hasMany(Annotation::class);
    }

    /**
     * All Categories
     */
    public static function categories ()
    {
        return [
            'buildings',
            'cables',
            'pipes',
            'roofs',
            'signs',
            'walls',
            'windows'
        ];
    }

    /**
     * All Currently available Brands
     */
    public static function getBrands ()
    {
        return Brand::types();
    }

    /**
     * User who uploaded the photo
     *
     * This is unnecessarily loading
     * - photos_count
     * - team
     * - total_categories
     */
    public function user ()
    {
    	return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Team that uploaded the photo
     */
    public function team ()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Return the tags for an image
     *
     * Remove any keys with null values
     */
    public function tags ()
    {
        foreach ($this->categories() as $category)
        {
            if ($this->$category)
            {
                foreach ($this->$category->types() as $tag)
                {
                    if (is_null($this->$category[$tag]))
                    {
                        unset ($this->$category[$tag]);
                    }
                }
            }
        }
    }

    /**
     * Update and return the total amount of litter in a photo
     */
    public function total ()
    {
        $total = 0;

        foreach ($this->categories() as $category)
        {
            if ($this->$category)
            {
                // We dont want to include brands in total_litter
                // Increment total_litter when its not brands
                if ($category !== 'brands')
                {
                    $total += $this->$category->total();
                }
            }
        }

        $this->total_litter = $total;
        $this->save();
    }

    /**
     * Save translation key => value for every item on each category that has a value
     *
     * Format: category.item quantity, category.item quantity,
     *
     * eg. smoking.butts 3, alcohol.beerBottles 4,
     *
     * We use the result_string on the global map for 2 reasons.
     * 1. We don't have to eager load any data.
     * 2. This format can be translated into any language.
     */
    public function translate ()
    {
        $result_string = '';

        foreach ($this->categories() as $category)
        {
            if ($this->$category)
            {
                $result_string .= $this->$category->translate();
            }
        }

        $this->result_string = $result_string;
        $this->save();
    }

    /**
     * Location relationships
     */
    public function country ()
    {
    	return $this->hasOne('App\Models\Location\Country');
    }

    public function state ()
    {
        return $this->hasOne('App\Models\Location\State');
    }

    public function city ()
    {
    	return $this->hasOne('App\Models\Location\City');
    }

    /**
     * All Categories
     */
    public function buildings ()
    {
        return $this->belongsTo(Building::class);
    }

    public function cables ()
    {
        return $this->belongsTo(Cable::class);
    }

    public function pipes ()
    {
        return $this->belongsTo(Pipe::class);
    }

    public function roofs ()
    {
        return $this->belongsTo(Roof::class);
    }

    public function signs ()
    {
        return $this->belongsTo(Sign::class);
    }

    public function walls ()
    {
        return $this->belongsTo(Wall::class);
    }

    public function windows ()
    {
        return $this->belongsTo(Window::class);
    }
}
