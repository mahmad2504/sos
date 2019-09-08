<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use App\Utility;
use Auth;
use App\ProjectTree;
use App\Http\Controllers\ProjectController;
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
		$users =  User::all();
		foreach($users as $user)
		{
			$request = new Request();
			$request->user_id = $user->id;
			$request->local = 1;
			$pc = new ProjectController();
			$activeprojects = $pc->GetProjects($request);
			foreach($activeprojects as $project)
			{
				$tree = new ProjectTree($project);
				$tree->SyncJira(1);
			}
			
		}
		/*$projects = Project::all();
		foreach($projects as $project)
		{
			$tree  =  new ProjectTree($project);
			$tree->Sync(1);
		}*/
    }
}
