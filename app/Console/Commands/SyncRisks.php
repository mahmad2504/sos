<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use App\Jira\Jira;
use Carbon\Carbon;
class SyncRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:risks  {--beat=0}';

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
		$start = Carbon::now();
		$start->subDays(90);
		
		$dt = new \DateTime();
		$jql = 'labels = risk and duedate >=  '.$start->format('Y-m-d');
		$jira =  new Jira();
		$tickets = $jira->Sync($jql,null);
		foreach($tickets as $ticket)
			dump($ticket);
		echo $jql;
    }
}
