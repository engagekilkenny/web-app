<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CleanKilkennyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function ($table) {

//            $table->dropForeign(['smoking_id']);
//            $table->dropForeign(['food_id']);
//            $table->dropForeign(['coffee_id']);
//            $table->dropForeign(['alcohol_id']);
//            $table->dropForeign(['softdrinks_id']);
//            $table->dropForeign(['art_id']);
//            $table->dropForeign(['brands_id']);
//            $table->dropForeign(['sanitary_id']);
//            $table->dropForeign(['political_id']);
//            $table->dropForeign(['pathways_id']);
//            $table->dropForeign(['other_id']);
//            $table->dropForeign(['drugs_id']);
//            $table->dropForeign(['dumping_id']);
//            $table->dropForeign(['industrial_id']);
//            $table->dropForeign(['trashdog_id']);
//            $table->dropForeign(['coastal_id']);

//            $table->dropColumn('smoking_id');
//            $table->dropColumn('food_id');
//            $table->dropColumn('coffee_id');
//            $table->dropColumn('alcohol_id');
//            $table->dropColumn('softdrinks_id');
//            $table->dropColumn('drugs_id');
//            $table->dropColumn('sanitary_id');
//            $table->dropColumn('other_id');
//            $table->dropColumn('coastal_id');
//            $table->dropColumn('pathways_id');
//            $table->dropColumn('art_id');
//            $table->dropColumn('brands_id');
//            $table->dropColumn('trashdog_id');
//            $table->dropColumn('political_id');
//            $table->dropColumn('dumping_id');
//            $table->dropColumn('industrial_id');
//            $table->dropColumn('dogshit_id');
        });

//        Schema::drop('alcohol');
//        Schema::drop('coffee');
//        Schema::drop('arts');
//        Schema::drop('brands');
//        Schema::drop('coastal');
//        Schema::drop('dogshit');
//        Schema::drop('drugs');
//        Schema::drop('dumping');
//        Schema::drop('farming');
//        Schema::drop('food');
//        Schema::drop('global_levels');
//        Schema::drop('halls');
//        Schema::drop('industrial');
//        Schema::drop('old_roles');
//        Schema::drop('pathways');
//        Schema::drop('politicals');
//        Schema::drop('quotes');
//        Schema::drop('sanitary');
//        Schema::drop('smoking');
//        Schema::drop('softdrinks');
//        Schema::drop('trashdog');
    }
}
