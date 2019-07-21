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
Route::get('/sync', 'SyncController@sync')->name('syncproject');// arg project modal with project.id


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


//// Project Resource //////
Route::get('/projectresource/{project_id}','ProjectResourceController@Show')->name('showprojectresources'); // project id as input param
Route::delete('/projectresource/{id}', 'ProjectResourceController@deleteprojectresource')->name('deleteprojectresource'); // arg project resource id
Route::put('/projectresource/{id}', 'ProjectResourceController@updateprojectresource')->name('updateprojectresource'); // arg project resource id

//// 
//Route::calendar('/calendar/{resource_id}','CalendarController@getcalendar')->name('getcalendar'); // project id as input param
Route::get('/calendar/{resource_name}','CalendarController@getcalendar')->name('getcalendar'); // project id as input param
Route::put('/calendar/{resource_name}','CalendarController@savecalendar')->name('savecalendar'); // project id as input param

/// Test
Route::get('/tjtest/{projectid}','TestController@TJTest'); // project id as input param


