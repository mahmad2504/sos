<?php
return 
[
	'servers' => [
		['uri'=>'http://jira.alm.mentorg.com:8080',
		 'username' => env('JIRA_EPS_USERNAME'),
		 'password' => env('JIRA_EPS_PASSWORD'),
		 'storypoints' => 'customfield_10022'
		],
		['uri'=>'http://ies-iesd-jira.ies.mentorg.com:8080',
		 'username' => env('JIRA_IESD_USERNAME'),
		 'password' => env('JIRA_IESD_PASSWORD'),
		 'storypoints' => 'customfield_10022'
		],
		['uri'=>'https://mentorgraphics.atlassian.net',
		 'username' => env('JIRA_ATTLASSIAN_USERNAME'),
		 'password' => env('JIRA_ATTLASSIAN_PASSWORD'),
		 'storypoints' => 'customfield_10022'
		]
	]
	
];