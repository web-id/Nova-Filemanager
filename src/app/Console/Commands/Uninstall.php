<?php

namespace WebId\Filemanager\App\Console\Commands;

use App\Traits\ModuleCommandTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class Uninstall extends Command
{
    use ModuleCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webid:modulefilemanager:uninstall
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * Name of Module
     *
     * @var string
     */
    protected $moduleName = 'ModuleFileManager';

    /**
     * Array of folders to be deleted for uninstallation
     *
     * @var array
     */
    private $delete_dirs = [];

    /**
     * Array of files to be deleted for uninstallation
     *
     * @var array
     */
    private $delete_files = [];

    /**
     * Array of databases to be deleted for uninstallation
     *
     * @var array
     */
    private $delete_databases = ['medias'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->delete_files = [
            base_path('database/migrations/2018_08_27_150424_create_medias_table.php'),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->progressBar = $this->output->createProgressBar(4);
        $this->progressBar->start();
        $this->info(" Webid\\" . $this->moduleName . " uninstallation started. Please wait...");

        $this->line(' Deleting files');
        if($this->delete_files) {
            foreach ($this->delete_files as $file) {
                $this->deleteFileOrFolder($file);
            }
        }

        $this->line(' Deleting databses');
        if($this->delete_databases) {
            foreach ($this->delete_databases as $database) {
                Schema::dropIfExists($database);
                $this->deleteMigrationRow('2018_08_27_150424_create_medias_table');
            }
        }

        $this->progressBar->finish();
        $this->toggleModule(false);
        $this->info(" Webid\\" . $this->moduleName . " uninstallation finished.");
    }

}