@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/logger.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />

@endsection
@section('style')
@endsection
@section('content')

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
				<label for="name">Commands</label>
				<textarea id="psettings_description" class="form-control-sm form-control" rows="2" placeholder="Enter commands" name="description"></textarea>
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
				<label style="margin-left:2px;margin-top:3px;" for="jirauri">Pull Description</label>
				<input id="psettings_task_description" style="margin-top:10px;margin-left:10px;" class="" type="checkbox" name="task_description" value="0"></input>
			</div>
			<small  id="psettings_error" class="text-danger form-text"></small><br>
			<button id="create_project" type="submit" class="btn btn-primary d-none">Create</button>
			<button id="update_project" type="submit" class="btn btn-primary d-none">Update</button>
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

<div style="width:95%;" class="container-fluid">
	@if($loggeduser != null)
		@if($loggeduser->name == 'admin')
			<div>Program Board - {{$user->name}}</div>
		@endif
	@endif
	<div class="mainpanel">
		<button style=""  id="new_project" title="Create New Project" type="button" class="btn btn-outline-success btn-sm">New Project</button>
		<input style="float:right;" id="filter" placeholder="Filter ..."></input>
		<div style="margin-top:20px;" id="example-table"></div>
	</div>
	
</div>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/eventsource.min.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/logger.js') }}" ></script>
@endsection
@section('script')
var thistoday="{{date("Y-m-d H:i:s")}}";
var admin={{$admin}};
var username = "{{$user->name}}";
var userid = "{{$user->id}}";
var tabledata = @json($projects);
var jiraservers = @json(config('jira.servers'));
var filter = "{{$filter}}";
//console.log(jiraservers);
var selected_row = null;
//define data
/*var tabledata = [
    {id:1, name:"Oli Bob", progress:12, gender:"male", rating:1, col:"red" },
    {id:2, name:"Mary May", progress:1, gender:"female", rating:2, col:"blue" },
    {id:3, name:"Christine Lobowski", progress:42, gender:"female", rating:0, col:"green" },
    {id:4, name:"Brendon Philips", progress:100, gender:"male", rating:1, col:"orange" },
    {id:5, name:"Margret Marmajuke", progress:16, gender:"female", rating:5, col:"yellow"},
];*/
result = Object.values(tabledata);


var table = null;

function stateparamLookup(cell){
    //cell - the cell component
	data = cell.getRow().getData();
	if(data.status.status == undefined)
		return {values:["SYSTEM"]};
		
	return {values:["SYSTEM", "ON HOLD", "CANCEL", "CLOSE","ON TRACK","DELAY"]}
   
}
function stateEditCheck(cell){
	if(admin == 1)
		return true;
	return false;
}

function GetRisksIssues(data,type)
{
    count = 0;
	references = [];
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues'][type]['Critical'] !==  undefined)
    {
        severity = 'Critical';
    }
	else if(data['risksissues'][type]['High'] !==  undefined)
    {
        severity = 'High';
    }
	else if(data['risksissues'][type]['Medium'] !==  undefined)
    {
        severity = 'Medium';
    }
	else
		return null;
	
    for (var key in data['risksissues'][type][severity]) 
    {
        references[count] = key;
        count++
    }
	obj = null;
    if(references.length > 0)
    {
		obj = {};
        str = references.toString();
		obj.references = str;
		obj.count = references.length;
        obj.type = severity;
    }
	return obj;
}

function GetBadge(severity)
{
	if(severity == 'Critical')
		return "badge-danger";
	else if(severity == 'High')
		return "badge-warning";
	else if(severity == 'Medium')
		return "badge-info";
}

function GetBlockers(data)
{
    count = 0;
	obj = null;
	blockers = [];
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues']['blockers'] ===  undefined)
		return;
	
    for (var key in data['risksissues']['blockers']) 
    {
        blockers[count] = key;
        count++
    }
    if(blockers.length > 0)
    {
		obj = {};
        str = blockers.toString();
		obj.references = str;
		obj.count = blockers.length;
        obj.type = 'Critical';
    }
	return obj;
}

function GetEscalations(data)
{
    count = 0;
	escalations = [];
	obj = null;
	if(data['risksissues'] === undefined)
		return null;
	if(data['risksissues']['escalations'] ===  undefined)
		return;
	
    for (var key in data['risksissues']['escalations']) 
    {
        escalations[count] = key;
        count++
    }
    if(escalations.length > 0)
    {
		obj = {};
        str = escalations.toString();
		obj.references = str;
		obj.count = escalations.length;
        obj.type = 'Critical';
    }
	return obj;
}


function ShowTable()
{
	//Build Tabulator
	table = new Tabulator("#example-table", 
	{
		/*height:"311px",*/
		persistenceMode:true , 
		persistentLayout:true,
		persistentFilter:true,
		layout:"fitColumns",
		//layout:"fitDataFill",
		movableColumns: true,
		tooltips:true,
		reactiveData:true, //turn on data reactivity
		data:result, //load data into table
		columns:[
			{width:50,title:"Id", field:"id", sorter:"number"},
			{width:300,title:"Name", field:"name", sorter:"string",
				formatter:function(cell, formatterParams, onRendered)
				{
					data = cell.getRow().getData();
					if(data.status !== undefined)
					{
						risks = GetRisksIssues(data.status,'risks');
						if(cell.getValue().length > 30)
							html = cell.getValue().substring(0,30)+"...";
						else
						html = cell.getValue();
						
						if(risks != null)
						{
							badge = GetBadge(risks.type);
							html += '<span style="margin-top:2px;" title="Risks '+risks.references+'" class="ml-1 d-flex float-right badge '+badge+'">'+risks.count+'</span>';
						}
						
						issues = GetRisksIssues(data.status,'issues');
						if(issues != null)
						{
							badge = GetBadge(issues.type);
							html += '<span style="margin-top:2px;" title="Issues '+issues.references+'" class="d-flex float-right badge '+badge+'">'+issues.count+'</span>';
						}
						blockers = GetBlockers(data.status);
						if(blockers != null)
						{
							badge = GetBadge(blockers.type);
							html += '<span style="margin-top:2px;" title="Blockers '+blockers.references+'" class="d-flex float-right badge '+badge+'">'+blockers.count+'</span>';
						}
						
						escalations = GetEscalations(data.status);
						if(escalations != null)
						{
							badge = GetBadge(escalations.type);
							html += '<span style="margin-top:2px;" title="Escalations '+escalations.references+'" class="d-flex float-right badge '+badge+'">'+escalations.count+'</span>';
						}
						
						return html;
					}
					return cell.getValue();
				}
			},
			/*{width:100,title:"Estimation", field:"estimation", sorter:"string",
				formatter:function(cell, formatterParams, onRendered){
					if(cell.getValue() == 1)
						return 'Time Based';
					else
						return 'Story Points';
				}
			},*/
			{width:90,title:"Estimate", field:"status.estimate", sorter:"numeric",
				formatter:function(cell, formatterParams, onRendered)
				{
					if(cell.getValue() > 0)
					{
						data = cell.getRow().getData();
						estimate = Round(cell.getValue());
						remaining  = Round(data.status.remaining);
						return '<span title="Estimated">'+estimate+'<span>/<span title="Remaining">'+remaining+'<span>';
					}
				}
			},
			{width:100,title:"Server", field:"jirauri", sorter:"string",
				formatter:function(cell, formatterParams, onRendered){
					return jiraservers[cell.getValue()].uri;
				}
			},
			{width:100,title:"Start", field:"sdate", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"(invalid date)",
				}
			
			},
			{width:100,title:"Deadline", field:"edate", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"(invalid date)",
				}
			
			},
			{width:100,title:"Forecast<br>Finish", field:"status.end", sorter:"string", formatter:"datetime", formatterParams:{
					inputFormat:"YYYY-MM-DD",
					outputFormat:"MMM DD YYYY",
					invalidPlaceholder:"",
				}
			
			},
			
			{width:200,title:"Progress", field:"status.progress",
				formatter:"progress", formatterParams:{
					min:0,
					max:100,
					legend :function(value){return '<span style="font-size:10px;font-weight:bold">'+value + "%"+'</span>'},
					color:["lightgreen"],
					legendColor:"#000000",
					legendAlign:"center",
				}
			},
			
			{width:100,title:"Status", field:"status.status", sorter:"string",
				formatter:function(cell, formatterParams, onRendered)
				{
					return "<img width='80px' src='/images/"+cell.getValue()+".png'></img>"; 

				}
			},
			{title:"Query", field:"jiraquery", sorter:"string"},
			{width:100,title:"Status", field:"state", editor:"select", 
				editorParams:stateparamLookup,editable:stateEditCheck,
				formatter:function(cell, formatterParams, onRendered)
				{
					row = cell.getRow();
					data = row.getData();
					value  = data.status.status;
					return "<img width='80px' src='/images/"+value+".png'></img>"; 

				}
			},
			{width:150,title:"", field:"icons", sorter:"string",
				formatter:function(cell, formatterParams, onRendered)
				{
					icons = '<i style="margin-top:2px;float:right;color:grey;" onclick="OnDelete('+data.id+')" title="delete" class="icon fa fa-trash  ml-1" aria-hidden="true"></i>&nbsp'+
						    '<i style="margin-top:3px;float:right;" onclick="OnArchive('+data.id+')" title="archive" class="icon fa fa-archive  ml-1" aria-hidden="true"></i>&nbsp'+
							'<i title="Settings" onclick="OnEditSettings('+data.id+')" class="icon fas far fa-edit icon ml-0"></i>'+
							'<i title="Sync With Jira" onclick="OnSync('+data.id+')" class="fas fa-sync icon  ml-1"></i>'+
							'<a href='+'"/projectresource/'+data.id+'">'+
							'<i title="Resources"  class="icon fas fa-user-circle ml-1"></i></a>'+
							'<a href='+'"/taskproperty/'+data.id+'">'+
							'<i title="Milestones"  class="icon fas fa-flag-checkered ml-1"></i>';
							
							
					return icons;
				}
			},
			{title:"", field:"status.rv", sorter:"numeric",
				formatter:function(cell, formatterParams, onRendered)
				{
					row = cell.getRow();
					data = row.getData();
					if(data.status.cv === undefined)
						return;
					cv  = data.status.cv;
					rv  = data.status.rv;
					color='';
					if(rv > cv)
						color='red';
					else
						color='green';
					return '<span title="Current Velocity">'+cv+'</span>'+"/"+'<span style="color:'+color+'" title="Required Velocity">'+rv+'</span>';
				}
			},
			{width:70,title:"Last<br>Sync", field:"last_synced", sorter:"string", formatter:
				function(cell, formatterParams, onRendered)
				{
					//cell - the cell component
		
					//do some processing and return the param object
					last_synced = MakeDate2(cell.getValue());
					if(last_synced == '')
					{
						cell.getElement().style["font-weight"] = "bold";
						//cell.getElement().style.color = "red";
						return cell.getValue();
					}
					else
					{
						ms = Math.floor(( Date.parse(thistoday) - Date.parse(cell.getValue()) ));
						t = millisToDaysHoursMinutes(ms);
						if(t.d > 0)
							return t.d+" days";
						else if(t.h > 0)
						{
							return t.h+" hours";
						}
						else if(t.m > 0)
						{
							return t.m+" min";
						}
						else if(t.s > 0)
						{
							return t.s+" sec";
						}
						else
							return "5 sec";
					}
					
				}
			},
			{title:"", field:"visible", editor:"tick",
				formatter:function(cell, formatterParams, onRendered)
				{
					//return cell.getValue();
					if(cell.getValue()=='false')
						return '<i title="Public Hidden" style="color:red;" class="far fa-times-circle"></i>';
					else
						return '<i title="Public Viewable" style="color:green;" class="far fa-check-circle"></i>';
					
				}
			}
			
			
			
			/*
			{title:"Progress", field:"progress", sorter:"number", formatter:"progress"},
			{title:"Gender", field:"gender", sorter:"string"},
			{title:"Rating", field:"rating", formatter:"star", align:"center", width:100},
			{title:"Favourite Color", field:"col", sorter:"string"},*/
		],
		cellEdited:function(cell)
		{
			//cell - cell component
			//console.log(cell.getValue());
			/*field = cell.getField();
			if(field == 'visible')
			{
				row = cell.getRow();
				data = row.getData();
				if(data.visible == 1)
					data.visible = 0;
				else
					data.visible = 1;
			}
			
			console.log(data);*/
			
			UpdateRow(cell.getRow());
		},
		
		cellClick:function(e, cell)
		{
			
			row = cell.getRow();
			data = row.getData();
			
			selected_row = row;
			field = cell.getField();
			console.log("Row clicked");
			if((field == 'state')||(field == 'icons')||(field == 'visible'))
				return false;
			
			if(data.status.status == undefined)
			{
				mscAlert({
				  title: 'Error',
				  subtitle: 'Sync Project to view its dashboatd',
				  okText: 'Close',    // default: OK
				});
				return;
			}
			
			//console.log(field);
			project = row.getData();
			window.location.href = "/dashboard/"+username+"/"+project.id;
		},
		rowFormatter:function(row)
		{
			//row.getElement().style.backgroundColor = "#A6A6DF";
			//row - row component
			var data = row.getData();
			//console.log(MakeDate2(data.last_synced));
			//if(data.last_synced == "blue")
			//{
			//	row.getElement().style["font-weight"] = "bold";
			//}
		}
	});
	table.hideColumn("jirauri");
	table.hideColumn("jiraquery");
	table.hideColumn("status.status");
	table.setFilter("name", "like", filter);
	if(admin == 0)
	{
		table.hideColumn("icons");
		table.hideColumn("visible");
	}
	else
	{
		table.showColumn("icons");
		table.showColumn("visible");
	}
}
$(document).ready(function()
{
	console.log("Loading Home Page2");
	if(admin == 1)
		ShowNavBar();
	ShowTable();
	
	$('#update_project').on('click', OnUpdateProject);
	$('#create_project').on('click', OnCreateProject);
	$('#archive').on('click', OnArchive);
	$('#new_project').on('click', OnNewProject);
	$("#filter").on("input", function(){
		table.setFilter("name", "like", $(this).val());
    });
	if(admin == 0)
	{
		$('#new_project').hide();
		$('#filter').hide();
	}
	InitLoader();
});
function OnNewProject(event)
{
	console.log("OnNewProject Click Dialog");
	$('#psettings_title').text("New Project");
	settings = {};
	settings.id = '';
	settings.last_synced = 'Never';
	settings.name = '';
	settings.description = 'link_implementedby=1\r\nlink_parentof=1\r\nlink_testedby=1\r\nepic_query=';
	settings.jiraquery = '';
	settings.estimation = 0;
	settings.error = '';
	sdate = new Date(thistoday);
	settings.sdate = MakeDate(sdate.getDate(),sdate.getMonth()+1,sdate.getFullYear());
	sdate.setMonth(sdate.getMonth() + 3);
	settings.edate = MakeDate(sdate.getDate(),sdate.getMonth()+1,sdate.getFullYear());
	console.log(settings.sdate);
	settings.jira_dependencies = 0;
	
	SetPsettingsModalFields(settings);
	ShowPsettingsModalButtons(1,0,0);
	$('#psettings_modal').modal('show');
}
function OnCreateProject(event) 
{
	event.preventDefault(); 
	console.log("Creating Project");
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
			HideLoading();
			console.log(response);  
			table.addData(response);
			var rows = table.searchRows("id", "=",response.id);
			rows[0].getElement().style.backgroundColor  = "lightgreen";	
			setTimeout(function(){ rows[0].getElement().style.backgroundColor  = "white";}, 1000);
			
		},
		error: function (error) 
		{
			HideLoading();
			console.log(error);
			$('#psettings_error').text(error.responseJSON.message);
		}
	});
}
function millisToDaysHoursMinutes(miliseconds) {
  var days, hours, minutes, seconds, total_hours, total_minutes, total_seconds;
  
  total_seconds = parseInt(Math.floor(miliseconds / 1000));
  total_minutes = parseInt(Math.floor(total_seconds / 60));
  total_hours = parseInt(Math.floor(total_minutes / 60));
  days = parseInt(Math.floor(total_hours / 24));

  seconds = parseInt(total_seconds % 60);
  minutes = parseInt(total_minutes % 60);
  hours = parseInt(total_hours % 24);
  
  return { d: days, h: hours, m: minutes, s: seconds };

};
function OnEditSettings(id)
{
	//console.log("OnEditSettings");
	var rows = table.searchRows("id", "=",id);
	selected_row = rows[0];
	project = rows[0].getData();
	
	ShowPsettingsModalButtons(0,1,1);
	$('#psettings_title').text("Edit Project");
	SetPsettingsModalFields(project);
	
	$('#psettings_modal').modal('show');
}

function OnSync(id)
{
	var rows = table.searchRows("id", "=",id);
	selected_row = rows[0];
	project = rows[0].getData();
	
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
function OnShowResources()
{
	console.log("OnShowResources");
}
function  OnConfigureMilestones(id)
{
	alert("OnConfigureMilestones "+id);
}			
function OnArchive(id)
{
	var rows = table.searchRows("id", "=",id);
	selected_row = rows[0];
	mscConfirm("Archive", "Project will be archived permanantly\nPlease notedown its id("+id+") to unrchive in future\nAre you sure to archive?", 
	function()
	{
		data = {};
		data.id = id;
		data._token = "{{ csrf_token() }}";
		ShowLoading();
		$.ajax(
		{
			type:"PUT",
			url:'{{route('archiveproject')}}',
			data:data,
			success: function(response)
			{
				HideLoading();
				console.log(response); 
				selected_row.getElement().style.backgroundColor  = "grey";	
				setTimeout(function(){ selected_row.delete() }, 1000);
			},
			error: function (error) 
			{
				HideLoading();
				mscAlert({
				  title: 'Error',
				  subtitle: error.responseJSON.original.message,
				  okText: 'Close',    // default: OK
				});
			}
		});
	},
	function() 
	{
		
	});
	
}
function OnDelete(id)
{
	var rows = table.searchRows("id", "=",id);
	selected_row = rows[0];
	
	mscConfirm("Delete", "Project will be deleted permentaly\nAre you sure?", function()
	{
		data = {};
		data.id = id;
		data._token = "{{ csrf_token() }}";
		ShowLoading();
		$.ajax(
		{
			type:"DELETE",
			url:'{{route('deleteproject')}}',
			data:data,
			success: function(response)
			{
				HideLoading();
				console.log(response); 
				selected_row.getElement().style.backgroundColor  = "grey";	
				setTimeout(function(){ selected_row.delete() }, 1000);
				
			},
			error: function (error) 
			{
				HideLoading();
				mscAlert({
				  title: 'Error',
				  subtitle: error.responseJSON.original.message,
				  okText: 'Close',    // default: OK
				});
			}
		});
	},
	function() {
		
	});
}
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
	$('#psettings_task_description').prop('checked', settings.task_description);
}
function ValidateFormData(data)
{
	//console.log(data);
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
	//console.log(result);
	if(result !== -1)
	{
		$('#psettings_error').text('Project Ends before start');
		return -1;
	}
	return 0;
}
function UpdateRow(row)
{
	data = row.getData();

	data._token = "{{ csrf_token() }}";
	
	$.ajax(
	{
		type:"PUT",
		url:'{{route('updateproject')}}',
		data:data,
		success: function(response){
			$('#psettings_modal').modal('hide');
			HideLoading();
			console.log(response);		
			row.update(response); 
			row.reformat();
			console.log(row.getData());

		},
		error: function (error) 
		{
			HideLoading();
			console.log(error);
			$('#psettings_error').text(error.responseJSON.original.message);
		}
	});
	console.log(data);
}
function OnSyncModalClosed()
{
	closeConnection();
	ShowLoading();
	RefreshProject(selected_row);
}
function InitLoader()
{
	"use strict";
	console.log("Loading Sync Module");
	$("#oasync").click(OASync); 
	$("#sync").click(Sync); 
	$("#close").click(closeConnection);
	$("#rebuild").click(Rebuild);
	$('#syncmodal').on('hidden.bs.modal',OnSyncModalClosed);
	updateConnectionStatus('Disconnected', false);
}
function RefreshProject(row)
{
	data = row.getData();
	$.ajax(
	{
		type:"GET",
		url:'{{route('getproject')}}'+'?id='+data.id,
		data:null,
		success: function(response){
			HideLoading();
			row.update(response); 
			row.reformat();
			console.log(response);  
			row.getElement().style.backgroundColor  = "powderblue";
			setTimeout(function(){ row.getElement().style.backgroundColor= "white"; }, 2000);
			
		},
		error: function (error) 
		{
			HideLoading();
			$('#psettings_error').text(error.responseJSON.original.message);
		}
	});
	
}
function OnUpdateProject(event)
{
	event.preventDefault(); 
	
	var form = $('#psettings_form');
	if(ValidateFormData(form.serializeArray())==-1)
		return;
	var serializedData = form.serialize();
	data = {};
	$(form.serializeArray()).each(function(i, field)
	{
		data[field.name] = field.value;
	});
	data.jira_dependencies = 0;
	if($('#psettings_jiradependencies').prop('checked'))
		data.jira_dependencies = 1;
	
	if($('#psettings_task_description').prop('checked'))
		data.task_description = 1;
		
	data.estimation = $('#psettings_estimation').prop('selectedIndex');
	//console.log(data.estimation);
	data.jirauri =  $('#psettings_jirauri').val();
	data.user_id = userid;
	data.last_synced = 'Need Sync';
	data.state = selected_row.getCell('state').getValue();
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
			HideLoading();
			selected_row.update(response); 
			selected_row.getElement().style.backgroundColor  = "powderblue";
			setTimeout(function(){ selected_row.getElement().style.backgroundColor= "white"; }, 2000);
			console.log(response);  
			//LoadProjectsData(OnProjectsDataLoad);
			
		},
		error: function (error) 
		{
			HideLoading();
			$('#psettings_error').text(error.responseJSON.original.message);
		}
	});
	return false;
}
@endsection
