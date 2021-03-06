<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//echo DateTime::createFromFormat('d/M/Y', $date_added)->format('M/d/Y');

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/', 'HomeController@index');   // View
Route::get('/home', 'HomeController@index')->name('home');   // View
Route::get('/program/{username}', 'HomeController@programview')->name('program'); 
Route::get('/getmyprojects/{username}', 'HomeController@GetData')->name('getprojectsdata'); 

Route::get('/changePassword','HomeController@showChangePasswordForm')->name('showchangepassword'); // View
Route::post('/changePassword','HomeController@changePassword')->name('changePassword');

//// Project //////
Route::get('/project', 'ProjectController@getproject')->name('getproject');// arg project modal with no id
Route::post('/project', 'ProjectController@create')->name('createproject');// arg project modal with no id
Route::put('/project', 'ProjectController@update')->name('updateproject'); // arg project modal with id
Route::put('/archive', 'ProjectController@archive')->name('archiveproject'); // arg project modal with id
Route::delete('/project', 'ProjectController@delete')->name('deleteproject'); // arg project.id
Route::get('/projects', 'ProjectController@getprojects')->name('getprojects');  // arg user.id


//// Sync //////
Route::get('/sync/jira', 'SyncController@syncjira')->name('syncjira');// arg project modal with project.id  
Route::get('/sync/oa', 'SyncController@syncoa')->name('syncoa');// arg project modal with project.id

// Dashboard //
Route::get('/dashboard/{user}/{project}','DashboardController@Show')->name('dashboard');
// Admin /////
Route::get('/admin', 'AdminController@index')->name('adminhome');



//Program View
Route::get('/program/summary/{user}', 'Widgets\ProgramViewController@ShowSummary')->name('programsummary');   // View

// Widgets
Route::get('/widget/treeview/{user}/{project}','Widgets\TreeViewController@Show')->name('showtreeview');
Route::get('/widget/data/treeview/{id}','Widgets\TreeViewController@GetData')->name('gettreeviewdata');// project id
//showsprintsplitchart
Route::get('/widget/sprintsplit/{user}/{project}','Widgets\TreeViewController@ShowSprintSplit')->name('showsprintsplitchart');


Route::get('/widget/gantt/{user}/{project}/{key?}','Widgets\GanttController@Show')->name('showgantt');
Route::get('/widget/data/gantt/{projectid}/{key?}','Widgets\GanttController@GetData')->name('getganttdata');// project id

Route::get('/widget/timechart/{user}/{project}','Widgets\TimeChartController@Show')->name('showtimechart');
Route::get('/widget/data/timechart/{projectid}','Widgets\TimeChartController@GetData')->name('gettimechartdata');// project id

//// Report
Route::get('/widget/report/weekly/{user}/{project}','Widgets\ReportController@ShowWeeklyReport')->name('showweeklyreport');// {year?}/{weekno?}/{key?}
Route::get('/widget/data/report/weekly/{user}/{project}','Widgets\ReportController@GetWeeklyReport')->name('getweeklyreport');//{year?}/{weekno?}/{key?}

//// Report
Route::get('/widget/document/{user}/{project}','Widgets\ReportController@ShowDocument')->name('showdocument');
Route::get('/widget/excel/{user}/{project}','Widgets\ReportController@Downloadexcel')->name('downloadexcel');


//// Burnup
Route::get('/widget/burnup/{user}/{project}/{key?}','Widgets\BurnupController@Show')->name('showwburnupchart');

//// Defect
Route::get('/widget/defects/{user}/{project}/{key?}','Widgets\DefectController@Show')->name('showdefectchart');


/// Milestone Report
Route::get('/widget/milestone/{user}/{project}/{key?}','Widgets\MilestoneController@Show')->name('showwmilestonereport');

/// Milesrone status Report
Route::get('/widget/status/{user}/{project}/{key?}','Widgets\MilestoneController@ShowStatus')->name('showwmilestonestatus');

// Sprints
Route::get('/widget/sprints/{user}/{project}/{key?}','Widgets\SprintsController@ShowSprints')->name('showsprints'); 

// Sprints
Route::get('/widget/governance/{user}/{project}/{key?}','Widgets\GovernanceController@Show')->name('showgovernance'); 



//// Project Resource //////
Route::get('/projectresource/{project_id}','ProjectResourceController@Show')->name('showprojectresources'); // project id as input param
Route::delete('/projectresource/{id}', 'ProjectResourceController@deleteprojectresource')->name('deleteprojectresource'); // arg project resource id
Route::put('/projectresource/{id}', 'ProjectResourceController@updateprojectresource')->name('updateprojectresource'); // arg project resource id

//// Open Air Resources /////
Route::get('/openair/resources/{project_id}','OpenAirController@GetResources')->name('getopenairresources'); // project id as input param


////
//Route::calendar('/calendar/{resource_id}','CalendarController@getcalendar')->name('getcalendar'); // project id as input param
Route::get('/calendar/{resource_name}','CalendarController@getcalendar')->name('getcalendar'); // project id as input param
Route::put('/calendar/{resource_name}','CalendarController@savecalendar')->name('savecalendar'); // project id as input param

////TaskPropertyController
Route::get('/taskproperty/{projectid}','TaskPropertyController@Show')->name('showtaskproperties'); // project id as input param
Route::get('/taskproperty/data/{projectid}','TaskPropertyController@GetTreeData')->name('gettreedata');// project id
Route::put('/taskproperty/{projectid}','TaskPropertyController@Save'); // project id as input param
Route::put('/taskproperty/position/{projectid}','TaskPropertyController@SavePosition'); // project id as input param

/// Calendar
Route::get('/calendars','CalendarController@ShowCountryCalendarList'); // View all country calendar links (only admin can see)


/// Test
Route::get('/test/tj/{projectid}','TestController@TJTest'); // project id as input param
Route::get('/test/sync/oa/{projectid}','TestController@OASync'); // project id as input param
Route::get('/test/sync/jira/{projectid}','TestController@JiraSync'); // project id as input param
Route::get('/test/worklogs/jira/{projectid}/{jira_key}','TestController@GetJiraWorklogs'); // Jira key as parameter
Route::get('/test/worklogs/oa/{projectid}','TestController@OAWorklogs'); // project id as input param
Route::get('/test/resource/timelogs/{projectid}/','TestController@ResourceTimeLogs');
Route::get('/test/tree/show/{projectid}','TestController@ShowTree'); // project id as input param
Route::get('/test/resource/worklogs/{projectid}','TestController@ResourceTimeLogs'); // project id as input param


Route::get('sprintcalendar','ServicesController@ShowCalendar'); // project id as input param
Route::get('riskcalendar','ServicesController@ShowRisksCalendar');
Route::get('epiclogs','ServicesController@ShowEpicDetails');



Route::get('schedule', function () {
    return view('scripts/schedule');
});
Route::get('calendars', function () {
    return view('scripts/calendars');
});

URL::forceScheme('https');

