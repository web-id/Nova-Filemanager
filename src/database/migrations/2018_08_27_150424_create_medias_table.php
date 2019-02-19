<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use WebId\Filemanager\App\MediaFromFiles;

class CreateMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('medias')) {
            Schema::create('medias', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('extension')->nullable();
                $table->string('path')->nullable();
                $table->string('alt')->nullable();
                $table->timestamps();
            });
        }

        $mediaTool = new MediaFromFiles();
        $mediaTool->populate();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medias');
    }
}