<?php
return 
[
	'servers' => [
		['uri'=>'https://jira.alm.mentorg.com',
		 'username' => env('JIRA_EPS_USERNAME'),
		 'password' => env('JIRA_EPS_PASSWORD'),
		 'storypoints' => 'customfield_10022',
		 'sprint' => 'customfield_11040',
		 'risk_severity' => 'customfield_12733',
		 'link_implemented_by' => 'implemented by',
		 'link_parentof' => 'Is Parent of',
		],
		['uri'=>'http://ies-iesd-jira.ies.mentorg.com:8080',
		 'username' => env('JIRA_IESD_USERNAME'),
		 'password' => env('JIRA_IESD_PASSWORD'),
		 'storypoints' => 'customfield_10022',
		 'sprint' => 'customfield_11040',
		 'risk_severity' => 'customfield_12733',
		 'link_implemented_by' => 'implemented by',
		 'link_parentof' => 'Is Parent of',
		],
		['uri'=>'https://mentorgraphics.atlassian.net',
		 'username' => env('JIRA_ATTLASSIAN_USERNAME'),
		 'password' => env('JIRA_ATTLASSIAN_PASSWORD'),
		 'storypoints' => 'customfield_10004',
		 'sprint' => 'customfield_10007',
		 'risk_severity' => 'customfield_12733',
		 'link_implemented_by' => 'is implemented by',
		 'link_parentof' => 'Is Parent of',
		]
	]
];