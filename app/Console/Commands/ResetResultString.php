<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Photo;

class ResetResultString extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'global:reset-results-string';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all result strings back to null';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $photos = Photo::all();

        foreach ($photos as $photo)
        {
            $photo['result_string'] = NULL;
            $photo->save();
        }
    }
}
