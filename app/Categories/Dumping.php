<?php

namespace App\Categories;

use Illuminate\Database\Eloquent\Model;

class Dumping extends Model
{
    public $fillable = [
    	'small',
    	'medium',
    	'large'
    ];

    protected $table = 'dumping';

    public function photo () {
    	return $this->belongsTo('App\Photo');
    }
}
