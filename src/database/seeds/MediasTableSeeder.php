<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $medias = factory(App\Media::class, 10)->create();
        $folders = factory(App\Folder::class, 10)->create();
    }
}