<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebId\Filemanager\App\MediaFromFiles;

class FixMediasWithStrSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mediaTool = new MediaFromFiles();
        $mediaTool->forceStrSlug();
        $mediaTool->populate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}