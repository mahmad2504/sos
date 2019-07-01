@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/logger.css') }}" />
@endsection
@section('style')
.progress {height: 5px;}
@endsection
@section('content')

<div class="container">
	@if($admin)
		<h3>Program Dashboard - {{$user->name}}</h3>
	@endif
    <button rel="tooltip" title="Create New Project" id="new_project" class="btn btn-primary float-left" data-toggle="modal" data-target="#psettings_modal">Add Project</button>
	@if($user->role == 'admin')
	<a href="/admin" style="margin-left:5px;" class="btn btn-info" role="button">Admin</a>
	@endif
	
	<br>
	<br>
	<div class="card_container">
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
				<label style=" padding: 0; margin-top:3px;" for="jirauri">Server&nbsp&nbsp</label>
				<select class="form-control-sm" id="psettings_jirauri" name="jirauri">
					@for($i=0;$i<count(config('jira.servers'));$i++)
						<option value="{{$i}}">{{config('jira.servers')[$i]['uri']}}</option>
					@endfor
				</select>&nbsp&nbsp
				<div class="form-group">
					<input id="psettings_jiradependencies" style="margin-top:10px;" class="" type="checkbox" name="jira_dependencies" value="0">Jira Dependencies</input>
				</div>
			</div>
			<div class="d-flex form-group">
				<label style="padding:0;margin-top:3px;" for="name">Name&nbsp&nbsp&nbsp</label>
				<input id="psettings_name" type="text" class="form-control-sm form-control" placeholder="Name" name="name">
			</div>
			<div class="form-group">
				<label for="name">Description</label>
				<textarea id="psettings_description" class="form-control-sm form-control" rows="2" placeholder="Enter description" name="description"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="form-group">
				<label for="name">Query</label>
				<textarea id="psettings_jiraquery" class="form-control-sm form-control" rows="2" placeholder="Enter Valid Jira Query" name="jiraquery"></textarea>
				<small  class="form-text text-muted"></small>
			</div>
			<div class="d-flex">
				<!--Date picker -->
				<div class="form-group">
					<label for="sdate">Start&nbsp&nbsp</label>
					<input class="form-control-sm" id="psettings_sdate" type="date" name="sdate"></input>
				</div>
				<!--Date picker -->
				<div style="margin-left: 50px;" class="form-group">
					<label for="edate">End&nbsp&nbsp</label>
					<input class="form-control-sm" id="psettings_edate" type="date" name="edate"></input>
				</div>
			</div>
			<div class="form-group d-flex">
				<label style="margin-top:5px;" for="name">Estimation</label>&nbsp&nbsp
				<select class="form-control-sm" id="psettings_estimation" name="estimation">
					<option value="0">Mix</option>
					<option value="1">Story Points</option>
					<option value="2">Time</option>
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
				<button style="margin-left:20px;" url='' projectid='' id="sync">Sync</button>
				<button style="" id="rebuild">Rebuild</button>
				<button style="margin-left:40px;" id="close">Disconnect</button>
				<span style="float:right;margin-right:20px;margin-top:5px;" id="connection"></span>
				<hr>
				<div  style="display: block;margin-top: 20px;" id="log"></div>
			</div>
		</div>
	</div>
</div>
<!-- End Sync Modal -->
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = "{{$user->id}}";
var projects = null;

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
	settings.edate = MakeDate(dates.day(),dates.month()+1,dates.year()+1);
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
	if(project.estimation == 1)
		estimation = 'Story Points';
	else if(project.estimation == 2)
		estimation = 'Time';
	else
		estimation = 'Story Points/Time';
	
	color='';
	if(project.dirty == 1)
		color='red';
	console.log(color);
	var col=$('<div class="col-sm-4">');
	var card=$('<div  class="card bg-white rounded bg-white shadow">');
	var progress=project.progress;
	var progress = '<div class="shadow-lg progress position-relative" style=""><div class="progress-bar" role="progressbar" style="background-color:green !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
			
	
	var headerstr ='<div  class="card-header border-success" style="background-color: #FFFAFA;">';
		headerstr +='<div class="d-flex">';
		headerstr   +='<img src="/images/greenpulse.gif" style="margin-left:-10px;margin-right:10px;width:20px;height:20px"></img>';
		headerstr   +='<h5 rel="tooltip" title="Project Name" id="card-name-'+project.id+'">'+project.name+'</h5>';
		headerstr +='</div>';
		headerstr +='<small style="margin-top:-10px;margin-left:20px;float:left" rel="tooltip" title="Estimation Method" class="float-left text-muted">'+estimation+'</small></div>';
		headerstr   +=progress;
	var header = $(headerstr);
	var body=$('<div class="card-body">');
	var desc=$('<p  rel="tooltip" title="Description" class="card-text" style="font-size:100%;">'+project.description+'</p>');
	var query=$('<p  rel="tooltip" title="Seed Jira Query" class="card-text" style="font-size:100%;">'+project.jiraquery+'</p>');
	var footerstr='<div class="card-footer bg-transparent">';
	footerstr+='<i projectid="'+project.id+'" class="editbutton far fa-edit icon float-left" rel="tooltip" title="Edit Project" data-toggle="modal" data-target="#editmodal"></i>';
	footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Sync With Jira" class="syncbutton fas fa-sync icon float-left ml-1"></i>';
	footerstr+='<a class="float-right ml-1" href='+'"/dashboard/'+username+'/'+project.name+'">';
		footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Dashboard" class="icon fas fa-list-alt float-right"></i></a>';
	footerstr+='<a class="float-right" href='+'"/projectresource/'+project.id+'">';
		footerstr+='<i projectid="'+project.id+'" rel="tooltip" title="Resources" class="icon fas fa-user-circle float-right"></i></a>';
	footerstr+='<p class="card-text" rel="tooltip" title="Last Sync time" style="color:'+color+';margin-left:70px;font-size:70%;">Last sync '+project.last_synced+'</p></div>';
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
	$('#synctitle').text(project.name);
	console.log("Sync button pressed project#"+project.name);
	$('#sync').attr('projectid', project.id);
	$('#sync').attr('url', "{{route('syncproject')}}");
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
	$('.editbutton').on('click', OnEdit); 
	$('.syncbutton').on('click', OnSync);
	HideLoading();
}
function LoadProjectsData(onsuccess,onfail)
{
	data = {};
	data.user_id = userid;
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
