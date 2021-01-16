<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;

class CompileResultsString extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'global:compile-verified-translated-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate string result of verified data for global map.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Instead of having to query the database to get the data for each photo
     * We save the metadata on the photos table to speed up page load
     * and avoid additional requests
     *
     * When a record exists, we apply the translation key => value,
     * for every item in each category.
     *
     * Todo - chunk this.
     */
    public function handle()
    {
        $photos = Photo::where('verified', '>', 0)->get();

        foreach ($photos as $photo)
        {
            if (is_null($photo->result_string))
            {
                $photo->translate();
            }
        }
    }
}
