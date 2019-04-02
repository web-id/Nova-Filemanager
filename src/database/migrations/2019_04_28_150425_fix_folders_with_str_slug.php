<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebId\Filemanager\App\MediaFromFiles;

class FixFoldersWithStrSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mediaTool = new MediaFromFiles();
        $mediaTool->forceStrSlugFolder();
        $mediaTool->forceStrSlugFolderInBdd();
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