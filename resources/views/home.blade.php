@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/logger.css') }}" />
@endsection
@section('style')

.progress {height: 5px;}
label {
  display: block;
  padding-left: 15px;
  text-indent: -15px;
}
input {
  padding: 0;
  margin:0;
  vertical-align: bottom;
  position: relative;
  *overflow: hidden;
}

@endsection
@section('content')

<div style="width:90%;" class="container-fluid">
	@if($admin)
		<h3>Projects of - {{$user->name}}</h3>
	@else
		<h3>Projects</h3>
	@endif
	<div class="mainpanel">
		<div  style="background-color:#F0F0F0">
			<button id="new_project" title="Create New Project" type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#psettings_modal">New Project</button>
			
			<!--<label style="margin-top:-5px;padding-left: 0px;text-indent: 0px;" class="float-right" for="show_closed_projects">Show Archived Projects</label>
			<input style="margin-left:5px;"id="show_closed_projects" class="reload" type="checkbox" name="show_closed_projects" value="0"></input> -->
	
			<a class="float-right " id="activeprojects" href="#">
				Active Projects
			</a>
			<a class="float-right" id="inactiveprojects" href="#">
				Inactive Projects |
			</a>
			<a class="float-right" href="{{route('programsummary',[$user->name])}}">
				Summary |
			</a> 
		</div>
		@if($user->role == 'admin')
		<a href="/admin" style="margin-left:5px;" class="btn btn-info" role="button">Admin</a>
		@endif
		
		<br>
		<br>
		<div class="card_container">
		</div>
	</div>
</div>
<!-- Edit Modal -->
<div class="modal" id="psettings_modal">
  <div class="modal-dialog">
	<div class="modal-content">
	  <!-- Modal Header -->
	  <div class="modal-header">
		<h4 id="psettings_title" class="modal-title"></h4>
			<button type="button" class="close" data-dismiss="modal">&times;</button>
	  </div> 
	  <!-- Modal body -->
	  <div class="modal-body">
		 <form name="psettings_form" id='psettings_form' action="#" method="get">
			<input id="psettings_id"  type="hidden"  name="id" value="" readonly>
			<input id="psettings_last_synced"  type="hidden"  name="last_synced" value="Never" readonly>
			<div class="d-flex form-group">
			
				<label style="margin-top:3px;" for="jirauri">Server</label>
				<select style="margin-left:10px;" class="form-control-sm" id="psettings_jirauri" name="jirauri">
					@for($i=0;$i<count(config('jira.servers'));$i++)
						<option value="{{$i}}">{{config('jira.servers')[$i]['uri']}}</option>
					@endfor
				</select>&nbsp&nbsp
				<div class="d-flex  form-group">
				<label style="margin-top:3px;" for="jirauri">Jira Dependencies</label>
				<input id="psettings_jiradependencies" style="margin-top:10px;margin-left:10px;" class="" type="checkbox" name="jira_dependencies" value="0"></input>
				</div>
			</div>
			<div class="d-flex form-group">
				<label style="margin-top:3px;" for="name">Name</label>
				<input style="width:35%;margin-left:10px;" id="psettings_name" type="text" class="form-control-sm form-control" placeholder="Name" name="name"></input>
				<label style="padding:0;margin-top:3px;margin-left:30px;" for="name">OpenAir</label>
				<input style="width:35%;margin-left:10px;" id="psettings_oaname" type="text" class="form-control-sm form-control" placeholder="OpenAir Project Name" name="oaname"></input>
			</div>
			<div class="form-group">
				<label for="name">Description</label>
				<textarea id="psettings_description" class="form-control-sm form-control" rows="2" placeholder="Enter description" name="description"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="form-group">
				<label for="name">Query(s)</label>
				<textarea id="psettings_jiraquery" class="form-control-sm form-control" rows="2" placeholder="Enter Valid Jira Query" name="jiraquery"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="d-flex form-group">
				<!--Date picker -->
					<label for="sdate">Start&nbsp&nbsp</label>
					<input style="width:100%;" class="form-control-sm" id="psettings_sdate" type="date" name="sdate"></input>
				<!--Date picker -->
					<label style="margin-left:20px;" for="edate">End&nbsp&nbsp</label>
					<input style="width:100%;" class="form-control-sm" id="psettings_edate" type="date" name="edate"></input>
			</div>
			<div class="form-group d-flex">
				<label style="margin-top:5px;" for="name">Estimation</label>&nbsp&nbsp
				<select class="form-control-sm" id="psettings_estimation" name="estimation">
					<!-- <option value="0">Mix</option> -->
					<option value="0">Story Points</option>
					<option value="1">Time</option>
				</select>
			</div>
			<small  id="psettings_error" class="text-danger form-text"></small><br>
			<button id="create_project" type="submit" class="btn btn-primary d-none">Create</button>
			<button id="update_project" type="submit" class="btn btn-primary d-none">Update</button>
			<button id="delete_project" class="btn btn-danger float-right d-none">Delete</button>
		</form>
	  </div>
	</div>
  </div>
</div>
<!-- End Edit Modal -->

<!-- Modal For Sync-->
<div class="modal fade" id="syncmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="overflow-y: initial;">
		<div class="modal-content">
			<div class="modal-body" style="width:500px;  height: 600px; overflow-y: auto; ">
				<div class="d-flex">
					<h3>Sync&nbsp&nbsp</h3>
					<span style="margin-top:8px;" id="synctitle"></span>
					
					<button data-dismiss="modal" class="ml-auto close">×</button>
				</div>
				<hr>
				<button style="margin-left:20px;display:none;" url='' projectid='' id="sync">Sync</button>
				<button style="" id="rebuild">Sync Jira</button>
				<button style="display:none;" url='' projectid='' id="oasync">Sync OA</button>
				<button style="float:right;" id="close">Disconnect</button><br>
				<hr>
				<div class="d-flex" style="margin-top:-10px;">
					<label style="font-size:10px;"><input id="cb_worklog" type="checkbox" />Rebuild worklogs</label>&nbsp
					<label style="font-size:10px;"><input id="cb_baseline" type="checkbox" />Create Baseline </label>
				</div>
				<hr style="margin-top:-5px;">
				<span style="float:right;margin-top:5px;" id="connection"></span>
				<div  style="display: block;margin-top: 20px;" id="log"></div>
				
			</div>
		</div>
	</div>
</div>
<!-- End Sync Modal -->
<script src="{{ asset('js/eventsource.min.js') }}" ></script>
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = "{{$user->id}}";
var projects = null;
var showclosedprojects =0;

function ShowPsettingsModalButtons(create=0,update=0,delete_button=0)
{
	if(create == 0)
		$('#create_project').addClass('d-none');
	else	
		$('#create_project').removeClass('d-none');
	
	if(update == 0)
		$('#update_project').addClass('d-none');
	else	
		$('#update_project').removeClass('d-none');
	
	if(delete_button == 0)
		$('#delete_project').addClass('d-none');
	else	
		$('#delete_project').removeClass('d-none');
}
function SetPsettingsModalFields(settings)
{
	$('#psettings_id').val(settings.id);
	$('#psettings_last_synced').val(settings.last_synced);
	$('#psettings_name').val(settings.name);
	$('#psettings_oaname').val(settings.oaname);
	$('#psettings_description').val(settings.description);
	$('#psettings_jiraquery').val(settings.jiraquery);
	$('#psettings_error').text(settings.error);
	$('#psettings_estimation').prop('selectedIndex',settings.estimation);
	$('#psettings_jirauri').prop('selectedIndex',settings.jirauri);
	$('#psettings_sdate').val(settings.sdate);
	$('#psettings_edate').val(settings.edate);
	$('#psettings_jiradependencies').prop('checked', settings.jira_dependencies);
}
function OnNewProject(event)
{
	console.log("OnNewProject Click Dialog");
	$('#psettings_title').text("New Project");
	settings = {};
	settings.id = '';
	settings.last_synced = 'Never';
	settings.name = '';
	settings.description = '';
	settings.jiraquery = '';
	settings.estimation = 0;
	settings.error = '';
	settings.sdate = MakeDate(dates.day(),dates.month()+1,dates.year());
	settings.edate = MakeDate(dates.day(),dates.month()+3,dates.year());
	settings.jira_dependencies = 0;
	
	SetPsettingsModalFields(settings);
	ShowPsettingsModalButtons(1,0,0)
}
function OnJiraDependenciesClick(event)
{
	 var element  = $(event.target);
	 console.log(element);
     if (element.prop('checked')){
          element.attr('value', 1);
     } else {
          element.attr('value', 0);
     }
}
function ValidateFormData(data)
{
	console.log(data);
	$(data).each(function(i, field)
	{
		data[field.name] = field.value;
	});
	$('#psettings_error').text('');
	
	if(data['name'].trim().length == 0)
	{
		$('#psettings_error').text('Project Name field cannot be empty');
		return -1;
	}
	if(data['jiraquery'].trim().length == 0)
	{
		$('#psettings_error').text('Jira Query field cannot be empty');
		return -1;
	}
	result = dates.compare(data['sdate'],data['edate']);
	console.log(result);
	if(result !== -1)
	{
		$('#psettings_error').text('Project Ends before start');
		return -1;
	}
	
	return 0;
}
function AddCard(project,row)
{
	if(project.estimation == 0)
		estimation = 'Story Points';
	else 
		estimation = 'Time';
	
	color='';
	if(project.dirty == 1)
		color='red';
	console.log(color);
	var col=$('<div class="col-sm-4">');
	var card=$('<div style="  box-shadow: 5px 5px 5px grey !important" class="card bg-white rounded bg-white shadow">');
	var progress=project.progress;
	var oaname=project.oaname;
	var baseline=project.baseline;
	var errors=null;project.errors;
	var progress = '<div class="shadow-lg progress position-relative" style=""><div class="progress-bar" role="progressbar" style="background-color:green !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
	if((baseline != null)&&(baseline.length==0))
		baseline=null;
	
	var headerstr ='<div  class="card-header border-success" style="background-color: #FFFAFA;">';
		headerstr +='<div class="d-flex">';
		projectend  = new Date(project.edate);
		today = new Date();
		com = 0;
		if(projectend.getTime() >= today.getTime())
			com = 1;
		if(project.archived==1)
			headerstr   +='<img src="/images/inactive.jpg" style="margin-left:-10px;margin-right:10px;width:20px;height:20px"></img>';
		else
		{
			if(com === 1)
				headerstr   +='<img src="/images/greenpulse.gif" style="margin-left:-10px;margin-right:10px;width:20px;height:20px"></img>';
			else
				headerstr   +='<img src="/images/redpulse.gif" style="margin-left:-10px;margin-right:10px;width:20px;height:20px"></img>';
		}

		headerstr   +='<span rel="tooltip" title="Project Name" id="card-name-'+project.id+'">'+project.name;
		headerstr +='<small style="" rel="tooltip" title="Estimation Method" class="text-muted">&nbsp&nbsp&nbsp'+estimation+'</small>';
		headerstr +='</span>';
		headerstr   +='</div>';
		headerstr   +='<div class="d-flex">';
			if(oaname != null)
				headerstr +='<img rel="tooltip" title="'+oaname+'" src="/images/openair.png" style="margin-top:0px;margin-left:20px;float:left;width:40px;height:13px;"></img>';
			if(baseline !=null)
				headerstr +='<img rel="tooltip" title="'+MakeDate2(baseline)+'" src="/images/baseline.png" style="margin-top:0px;margin-left:5px;float:left;width:35px;height:13px;"></img>';
		headerstr   +='</div>';
		headerstr   +='</div>';
		headerstr   +=progress;
	var header = $(headerstr);
	var body=$('<div class="card-body">');
	var desc=$('<p  rel="tooltip" title="Description" class="card-text" style="font-size:100%;">'+project.description+'</p>');
	var query=$('<p  rel="tooltip" title="Seed Jira Query" class="card-text" style="font-size:100%;">'+project.jiraquery+'</p>');
	var footerstr='<div class="card-footer bg-transparent">';
	footerstr+='<i projectid="'+project.id+'" class="editbutton far fa-edit icon float-left" rel="tooltip" title="Edit Project" data-toggle="modal" data-target="#editmodal"></i>';
	footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Sync With Jira" class="syncbutton fas fa-sync icon float-left ml-1"></i>';
	footerstr+='<a class="float-right ml-1" href='+'"/dashboard/'+username+'/'+project.name+'">';
		footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Dashboard" class="icon fas fa-chart-line float-right"></i></a>';
	footerstr+='<a class="float-right" href='+'"/projectresource/'+project.id+'">';
		footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Resources" class="icon fas fa-user-circle float-right"></i></a>';
	footerstr+='<a class="float-right" href='+'"/taskproperty/'+project.id+'">';
		footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Milestones" style="margin-right:5px;" class="icon fas fa-flag-checkered float-right"></i></a>';
	if(project.last_synced == 'Never')
		footerstr+='<p class="card-text" rel="tooltip" title="Last Sync time" style="color:'+color+';margin-left:70px;font-size:70%;">Never Synced '+'</p></div>';
	else
		footerstr+='<p class="card-text" rel="tooltip" title="Last Sync time" style="color:'+color+';margin-left:70px;font-size:70%;">Synced on '+MakeDate2(project.last_synced)+'</p></div>';

	var footer = $(footerstr);
	body.append(desc);
	body.append(query);
	card.append(header);
	card.append(body);
	card.append(footer);
	col.append(card);
	row.append(col);
	
}
function PopulateCard(projects)
{
	var j=1;
	var rownum = 1;
	$('.card_container').empty();
	var row=$('<div id="'+'row_'+rownum+'" class="row">');
	console.log("Creating Cards");
	$('.card_container').append(row);
	for(i=0;i<projects.length;i++)
	{
		AddCard(projects[i],row);
		if(j%3==0)
		{
			console.log("Appending");
			rownum++;
			row=$('<br><div id="'+'row_'+rownum+'" class="row">');
			
			rownum++;
			$('.card_container').append(row);
		}
		j++;
	}
}
function FindProject(id)
{
	for(var i=0;i<projects.length;i++)
	{
		if(projects[i].id == id)
			return projects[i];
	}
}
function OnEdit(event) // when edit button is pressed on card to show edit dialog
{
	event.preventDefault(); 
	console.log("Showing Edit Project Dialog");
	$element  = $(event.target);
	project  = FindProject($element.attr('projectid'));
	ShowPsettingsModalButtons(0,1,1);
	$('#psettings_title').text("Edit Project");
	
	settings = {};
	settings.id = project.id;
	settings.last_synced = project.last_synced;
	settings.name = project.name;
	settings.oaname = project.oaname;
	settings.description = project.description;
	settings.jiraquery = project.jiraquery;
	settings.estimation = project.estimation;
	settings.jirauri =  project.jirauri;
	settings.sdate = project.sdate;
	settings.edate = project.edate;
	settings.jira_dependencies = project.jira_dependencies;
	settings.error = '';
	SetPsettingsModalFields(settings);
	$('#psettings_modal').modal('show');
}

function OnSync(event)
{
	event.preventDefault(); 
	$element  = $(event.target);
	project  = FindProject($element.attr('projectid'));
	if((project.oaname == null)||(project.oaname.length == 0))
		$('#oasync').hide();
	else
		$('#oasync').show();

	$('#synctitle').text(project.name);
	console.log("Sync button pressed project#"+project.name);
	$('#sync').attr('projectid', project.id);
	$('#sync').attr('url', "{{route('syncjira')}}");
	
	$('#oasync').attr('projectid', project.id);
	$('#oasync').attr('url', "{{route('syncoa')}}");
	$( "#cb_worklog" ).prop( "checked", false );
	$( "#cb_baseline" ).prop( "checked", false );
	Clear();
	$('#syncmodal').modal('show');	
}

function OnProjectsDataLoad(response)
{
	console.log("Projects Data Received");
	HideLoading();
	projects = response;
	console.log(projects);
	PopulateCard(projects);
	$('#activeprojects').on('click', LoadActiveProjects);
	$('#inactiveprojects').on('click', LoadInActiveProjects);
	$('.editbutton').on('click', OnEdit); 
	$('.syncbutton').on('click', OnSync);
	HideLoading();
}
function LoadProjectsData(onsuccess,onfail)
{
	data = {};
	data.user_id = userid;
	data.showclosedprojects=showclosedprojects;
	data._token = "{{ csrf_token() }}";
	ShowLoading();
	$.ajax({
		type:"GET",
		url:'{{route('getprojects')}}',
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}

function OnUpdateProject(event)
{
	event.preventDefault(); 
	//var form = $(event.target);
	var form = $('#psettings_form');
	if(ValidateFormData(form.serializeArray())==-1)
		return;
	var serializedData = form.serialize();
	console.log("*****************");
	console.log($('#psettings_jiradependencies').prop('checked'));
	data = {};
	$(form.serializeArray()).each(function(i, field)
	{
		data[field.name] = field.value;
	});
	data.jira_dependencies = 0;
	if($('#psettings_jiradependencies').prop('checked'))
		data.jira_dependencies = 1;
	
	data.estimation = $('#psettings_estimation').prop('selectedIndex');
	console.log(data.estimation);
	data.jirauri =  $('#psettings_jirauri').val();
	data.user_id = userid;
	data._token = "{{ csrf_token() }}";
	console.log(data);
	ShowLoading();
	
	$.ajax(
	{
		type:"PUT",
		url:'{{route('updateproject')}}',
		data:data,
		success: function(response){
			$('#psettings_modal').modal('hide');
			console.log(response);  
			LoadProjectsData(OnProjectsDataLoad);
			
		},
		error: function (error) 
		{
			HideLoading();
			console.log(error);
			$('#psettings_error').text(error.responseJSON.message);
		}
	});
	return false;
}

function OnDeleteProject(event)
{
	console.log("Deleting Project");
	event.preventDefault(); 
	data = {};
	data.id = $('#psettings_id').val();
	data._token = "{{ csrf_token() }}";
	ShowLoading();
	$.ajax(
	{
		type:"DELETE",
		url:'{{route('deleteproject')}}',
		data:data,
		success: function(response)
		{
			$('#psettings_modal').modal('hide');
			console.log(response);  
			LoadProjectsData(OnProjectsDataLoad);
		},
		error: function (error) 
		{
			HideLoading();
			console.log(error);
			$('#psettings_error').text(error.responseJSON.message);
		}
	});
	return false;
}
function OnCreateProject(event) 
{
	console.log("Creating Project");
	event.preventDefault(); 
	var form = $('#psettings_form');
	if(ValidateFormData(form.serializeArray())==-1)
		return;
	
	data = {};
	$(form.serializeArray()).each(function(i, field)
	{
		data[field.name] = field.value;
	});
	data.user_id = userid;
	data._token = "{{ csrf_token() }}";
	ShowLoading();
	$.ajax(
	{
		type:"POST",
		url:'{{route('createproject')}}',
		data:data,
		success: function(response)
		{
			$('#psettings_modal').modal('hide');
			console.log(response);  
			LoadProjectsData(OnProjectsDataLoad,null);
		},
		error: function (error) 
		{
			HideLoading();
			console.log(error);
			$('#psettings_error').text(error.responseJSON.message);
		}
	});
}
function InitLoader()
{
	"use strict";
	console.log("Loading Sync Module");
	$("#oasync").click(OASync); 
	$("#sync").click(Sync); 
	$("#close").click(closeConnection);
	$("#rebuild").click(Rebuild);
	updateConnectionStatus('Disconnected', false);
}
function OnSyncModalClosed()
{
	closeConnection();
	ShowLoading();
	LoadProjectsData(OnProjectsDataLoad,null);

}
function LoadActiveProjects(event)
{
	showclosedprojects =0;
	LoadProjectsData(OnProjectsDataLoad,null);
}
function LoadInActiveProjects(event)
{
	showclosedprojects =1;
	LoadProjectsData(OnProjectsDataLoad,null);
}
$(document).ready(function()
{
	console.log("Loading Home Page");
	ShowNavBar();
	LoadProjectsData(OnProjectsDataLoad,null);
		
	$('#new_project').on('click', OnNewProject);
	$('#create_project').on('click', OnCreateProject);
	$('#update_project').on('click', OnUpdateProject);
	$('#delete_project').on('click', OnDeleteProject);
	$('#syncmodal').on('hidden.bs.modal',OnSyncModalClosed);
	InitLoader();
});
@endsection
