<?php

namespace App\Categories;

use Illuminate\Database\Eloquent\Model;

class Industrial extends Model
{
    public $fillable = [
    	'oil',
    	'chemical',
    	'plastic',
    	'bricks',
    	'tape',
    	'other'
    ];

    protected $table = 'industrial';

    public function photo () {
    	return $this->belongsTo('App\Photo');
    }
}
