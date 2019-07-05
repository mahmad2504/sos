<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;


use App\Project;
use App\User;
use App\Utility;
use Auth;
use App\ProjectTree;
use App;

class SyncProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        //
		$projects = Project::all();
		foreach($projects as $project)
		{
			$tree  =  new ProjectTree($project);
			$tree->Sync(1);
		}
    }
}
