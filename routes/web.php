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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::get('/', 'HomeController@index');   // View
Route::get('/home', 'HomeController@index')->name('home');   // View
Route::get('/changePassword','HomeController@showChangePasswordForm')->name('showchangepassword'); // View
Route::post('/changePassword','HomeController@changePassword')->name('changePassword');

//// Project //////
Route::get('/project', 'ProjectController@getproject')->name('getproject');// arg project modal with no id
Route::post('/project', 'ProjectController@create')->name('createproject');// arg project modal with no id
Route::put('/project', 'ProjectController@update')->name('updateproject'); // arg project modal with id
Route::delete('/project', 'ProjectController@delete')->name('deleteproject'); // arg project.id
Route::get('/projects', 'ProjectController@getprojects')->name('getprojects');  // arg user.id


//// Sync //////
Route::get('/sync/jira', 'SyncController@syncjira')->name('syncjira');// arg project modal with project.id
Route::get('/sync/oa', 'SyncController@syncoa')->name('syncoa');// arg project modal with project.id

// Dashboard //
Route::get('/dashboard/{user}/{project}','DashboardController@Show')->name('dashboard');
// Admin /////
Route::get('/admin', 'AdminController@index')->name('adminhome');
Route::get('/admin/{user}', 'AdminController@showuserboard')->name('showuserboard');


// Widgets
Route::get('/widget/treeview/{user}/{project}','Widgets\TreeViewController@Show')->name('showtreeview');
Route::get('/widget/data/treeview/{id}','Widgets\TreeViewController@GetData')->name('gettreeviewdata');// project id

Route::get('/widget/gantt/{user}/{project}','Widgets\GanttController@Show')->name('showgantt');
Route::get('/widget/data/gantt/{id}','Widgets\GanttController@GetData')->name('getganttdata');// project id

Route::get('/widget/timechart/{user}/{project}','Widgets\TimeChartController@Show')->name('showtimechart');
Route::get('/widget/data/timechart/daily/{projectid}','Widgets\TimeChartController@GetDailyData')->name('getdailytimechartdata');// project id
Route::get('/widget/data/timechart/weekly/{projectid}','Widgets\TimeChartController@GetWeeklyData')->name('getweeklytimechartdata');// project id
Route::get('/widget/data/timechart/monthly/{projectid}','Widgets\TimeChartController@GetMonthlyData')->name('getmonthlytimechartdata');// project id




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

////Milestones
Route::get('/milestones/{projectid}','MilestoneController@Show'); // project id as input param


/// Test
Route::get('/test/tj/{projectid}','TestController@TJTest'); // project id as input param
Route::get('/test/sync/oa/{projectid}','TestController@OASync'); // project id as input param
Route::get('/test/worklogs/jira/{projectid}','TestController@GetJiraWorklogs'); // project id as input param
Route::get('/test/worklogs/oa/{projectid}','TestController@OAWorklogs'); // project id as input param
Route::get('/test/resource/timelogs/{projectid}/','TestController@ResourceTimeLogs');
Route::get('/test/tree/show/{projectid}','TestController@ShowTree'); // project id as input param
Route::get('/test/resource/worklogs/{projectid}','TestController@ResourceTimeLogs'); // project id as input param
