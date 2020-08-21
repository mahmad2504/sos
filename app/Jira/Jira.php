<?php

namespace App\Jira;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Configuration\ArrayConfiguration;
use App\Jira\JiraFields;
use App\Jira\Ticket;
class Jira
{
	public function __construct()
	{
		$this->jf = new JiraFields();
	}
	public function Sync($jql,$updated=null,$server='EPS')
	{
		$max = 500;
		$start = 0;
		if($server=='EPS')
		{
			$issueService = new IssueService(new ArrayConfiguration([
			 'jiraHost' => env('JIRA_EPS_URL'),
              'jiraUser' => env('JIRA_EPS_USERNAME'),
             'jiraPassword' => env('JIRA_EPS_PASSWORD'),
			]));
		}
		if($server=='IESD')
		{
			$issueService = new IssueService(new ArrayConfiguration([
			 'jiraHost' => env('JIRA_IESD_URL'),
              'jiraUser' => env('JIRA_IESD_USERNAME'),
             'jiraPassword' => env('JIRA_IESD_PASSWORD'),
			]));
		}
		
		if($updated!=null)
			$jql = $jql." and updated >= '".$updated."'";

		//echo "Query for active tickets \n".$jql."\n";
		$expand = [];//['changelog'];
		$fields = [];
		foreach($this->jf->Standard() as $field)
			$fields[] = $field;
		foreach($this->jf->Custom() as $field)
			$fields[] = $field;

		$issues = [];
		$start = 0;
		$max = 500;
		//dump($fields);
		while(1)
		{
			$data = $issueService->search($jql,$start, $max,$fields,$expand);
			if(count($data->issues) < $max)
			{
				foreach($data->issues as $issue)
				{
					$ticket = new Ticket($issue);
					$issues[] = $ticket ;
				}
				//echo count($issues)." Found"."\n";
				return $issues;
			}
			foreach($data->issues as $issue)
			{
				$ticket = new Ticket($issue);
				$issues[] = $ticket ;	
			}
			$start = $start + count($data->issues);
		}
		
	}
}