<?php

namespace WebId\Filemanager\App\Console\Commands;

use App\Traits\ModuleCommandTrait;
use Illuminate\Console\Command;

class Install extends Command
{
    use ModuleCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webid:modulefilemanager:install
                                {--timeout=300} : How many seconds to allow each process to run.
                                {--debug} : Show process output or not. Useful for debugging.';

    /**
     * The console command description.
     * Completed on handle()
     *
     * @var string
     */
    protected $description = ''; //DON'T TOUCH ! See it on handle()

    /**
     * Name of Module
     *
     * @var string
     */
    protected $moduleName = 'Filemanager';

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
        $this->description = 'Install Webid'.$this->moduleName;
        $this->progressBar = $this->output->createProgressBar(4);
        $this->progressBar->start();
        $this->info(" Webid\\".$this->moduleName." installation started. Please wait...");
        $provider = "WebId\Filemanager\FilemanagerServiceProvider";

        $this->line(' Publishing tests');
        $this->publish('tests', $provider);

        $this->progressBar->finish();
        $this->toggleModule(true);
        $this->info(" Webid\\".$this->moduleName." installation finished.");
    }
}