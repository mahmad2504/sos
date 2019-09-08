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
use App\Http\Controllers\SyncController;
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
            $i=0;
			foreach($activeprojects as $project)
			{
                //if($i++ ==  3)
                {
                    $sc = new SyncController();
                    $request->projectid = $project->id;
                    $request->rebuild = 1;
                    $request->debug = 1;
                    $sc->SyncJira($request);
                    //break;
                }
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
