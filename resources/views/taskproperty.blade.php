@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
.progress {height: 10px;}
}

@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	<h3>{{ $project->name}}</h3>
	<div class="mainpanel">
	<div class="paneltitle">
		<a href="{{route('dashboard',[$user->name,$project->name])}}"style="margin-top:5px;margin-right:10px;"  rel="tooltip" title="Dashboard" class="float-right">Dashboard</a>
		<h3 style="margin-left:10px;margin-top:15px;" id='description' class="mr-auto">Milestone Configuration</h3>
	</div>
	<label style="margin-top:-5px;padding-right: 5px;text-indent: 0px;" class="float-right" for="checkbox">Show All Task</label>
	<input  style="margin-top:0px;margin-right:10px" class="float-right" id="showall"  field="all"  type="checkbox" false></input> 
	<table id="treetable" style="margin-top:-25px;display:none;" class="table">
		<caption style="caption-side:top;text-align: center">
		  <a href="#"  onclick="jQuery('#treetable').treetable('expandAll'); return false;">Expand all</a>&nbsp|
		  <a href="#" onclick="jQuery('#treetable').treetable('collapseAll'); return false;">Collapse all</a>
		</caption>
		<col style="width:25%;border-right:1pt solid lightgrey;"> <!--Title  --> 
		<col style="width:7%;border-right:1pt solid lightgrey;"> <!--Jira  --> 
		<col style="width:7%;border-right:1pt solid lightgrey;"> <!--Estimate  --> 
		<col style="width:20%;border-right:1pt solid lightgrey;"> <!--Alternate Title  --> 	
		<col style="width:12%;border-right:1pt solid lightgrey;"> <!--Start  --> 	
		<col style="width:12%;border-right:1pt solid lightgrey;"> <!--Duedate  --> 
		<col style="width:5%;border-right:1pt solid lightgrey;"> <!--Select  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Progress  --> 
		<col style="width:5%;border-right:1pt solid lightgrey;"> <!--Select  --> 
		<thead style="background-color: SteelBlue;color: white;font-size: .8rem;">
		  <tr>
			<th>Title</th>
			<th>Jira</th>
			<th id='estimatecolumn'></th>
			<th>Alternate Text</th>
			<th>Start Constraint</th>
			<th>Duedate</th>
			<th>Milestone</th>
			<th>Progress</th>
			<th>Select</th>
		  </tr>
		</thead>
		<tbody id="tablebody">
		</tbody>
	</table>
	<div id="legend">
		<span>Project<span style="margin-top:20px;padding:5px;" class="PROJECT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Requirement<span style="margin-top:20px;padding:5px;" class="REQUIREMENT">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Workpackage<span style="margin-top:20px;padding:5px;" class="WORKPACKAGE">&nbsp&nbsp&nbsp</span></span>
		<span>Epic<span style="margin-top:20px;padding:5px;" class="EPIC">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Task<span style="margin-top:20px;padding:5px;" class="TASK">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Defect<span style="margin-top:20px;padding:5px;" class="DEFECT">&nbsp&nbsp&nbsp</span></span>
	</div>
	</div>
</div>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/jquery.treetable.js') }}" ></script>
<script src="{{ asset('js/bootstrap-datepicker.min.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var _token = "{{ csrf_token() }}";
var cur_row = null;
var data = null;


function LoadProjectData(url,data,onsuccess,onfail)
{
	$.ajax({
		type:"GET",
		url:url,
		cache: false,
		data:data,
		success: onsuccess,
		error: onfail
	});
}
var estimate_units = '';
function OnProjectDataReceived(response)
{
	//console.log(response.description);
	//$('#description').append(response.description);
	if(response.estimation == 0)
	{
		header = 'Story Points';
		estimate_units = 'Points';
	}
	else
	{
		header = 'Time Estimates';
		estimate_units = 'Days';
	}
	
	$('#estimatecolumn').append(header);
}

function CreateElementId(tag,extid,extratag=null)
{
	var mid =  (extid+"").replace(/\./g, '-');
	if(extratag == null)
		return tag+"_"+mid;	
	return tag+"_"+extratag+"_"+mid;
}
function GetElement(tag,extid,extratag=null)
{
	id = CreateElementId(tag,extid,extratag);
	return $('#'+id);
}

function Update(extid)
{
	data[extid]._token = "{{ csrf_token() }}";
	console.log(data[extid]);
	ShowLoading();
	$.ajax({
			type:"PUT",
			url:'/taskproperty/'+projectid,
			cache: false,
			data:data[extid],
			success: function(response){
				console.log(response)
				data[extid] = response;
				UpdateRow(extid);
				HideLoading();
			},
			error: function(response){
				HideLoading();
			}
		});
}
$(document).ready(function()
{
	if(username != null)
		$('.navbar').removeClass('d-none');
	
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	LoadProjectData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('gettreedata',[$project->id]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			ShowTree(JSON.parse(response),false);
			
		},
		error: function (error) 
		{
			$('.loading').hide();
			console.log(error);  
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){})
		}
	});
})
function round(value, precision) 
{
	var multiplier = Math.pow(10, precision || 0);
	return Math.round(value * multiplier) / multiplier;
}
function ShowTree(response,showall)
{
	console.log(response);
	if(data == null)
		data = response;
	console.log(showall);
	$('#tablebody').empty();

	for (var exitid in data)
	{
		var row = data[exitid];
		var id = row['extid'];

		//var mid = (id+"").replace(/\./g, '-');

		var pid = row['pextid'];
		var isconfigured = row['isconfigured'];
		if(isconfigured == 'true')
			isconfigured = true;
		if(isconfigured == 'false')
			isconfigured = false;
		if(showall == false)
		{
			if( isconfigured != true)
				continue;
		}
		var atext = row['atext'];
		console.log(atext);
		if((atext == 'null') || (atext == null))
		  atext = '';
		var tend  = row['tend'];
		var tstart  = row['tstart'];
		var _class =row['issuetype'];
		var title=row['summary'];
		var link=row['jiraurl'];
		var linktext=row['key'];
		var estimate=round(row['estimate'],1);
		var progress=round(row['progress'],1);
		var status=row['status'];
		var priority=row['priority'];
		var ismilestone = row['ismilestone'];
		if(ismilestone == 'true')
			ismilestone = true;
		if(ismilestone == 'false')
			ismilestone = false;
		var duplicate=row['duplicate'];
		//data_array.push(row);
		var progressbar_animation_class = 'progress-bar-striped progress-bar-animated';
		
		if(_class == 'TASK')
		{
			if(status == 'OPEN')
				_class = 'TASK_OPEN';
			if(status == 'RESOLVED')
			{
				_class = 'TASK_RESOLVED';
			}
		}
		color = '';
		progressbar_color = 'green';
		if(status == 'RESOLVED')
		{
			progressbar_animation_class = '';
			progressbar_color = 'darkgreen';
		}
		else
		{
			if(priority == 1)
				color = 'red';
			if(priority == 2)
				color = 'orange';
		}

		var rowstr = '<tr ';
		rowstr += "data-tt-id='"+id+"' ";

		if(pid != '')
			rowstr += "data-tt-parent-id='"+pid+"'";
		
		rowstr += "style='border-bottom:1pt solid grey;' class='branch expanded'>";
		rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'><span class='"+_class+"'>";
		rowstr += id+" "+title+"</span></td>";
		
		////////////////////////////////////////////////// JIRA
		if(linktext == id)// Not a Jira Task 
			rowstr += '<td></td>';
		else
			rowstr += "<td><a style='font-size:.6rem; color:"+color+";' href='"+link+"/browse/"+linktext+"'>"+linktext+'</a></td>';

		//////////////////////////////////////////////////ESTIMATE
		rowstr += "<td  style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
		if(estimate > 0)
			rowstr += estimate+" "+estimate_units+"</span></td>";
		else
			rowstr += "</span></td>";

	
		//////////////////////////////////////////////////// ALTERNATE TEXT
		field = 'atext';
		mid = CreateElementId(field,id);		
		
		att = '';
		if(isconfigured == false)
			att += 'disabled ';
		rowstr += "<td   style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
		rowstr += "<input id='"+mid+"' extid='"+id+"' field='"+field+"' class='editable' type='text' value='"+atext+"' "+att+" ></input>";
		rowstr += "</td>";

		////////////////////////////////////////////////////// START DATE
		field = 'tstart';
		mid = CreateElementId(field,id);	
		if(isconfigured == false)
			att += 'disabled ';
		rowstr += "<td   style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
		rowstr += "<input  id='"+mid+"' extid='"+id+"' data-date-format='DD MMMM YYYY' class='editable' type='date' field='"+field+"' value='"+tstart+"' "+att+" ></input>";
		rowstr += "</td>";
		
		//////////////////////////////////////////////////////////// DUE DATE
		field = 'tend';
		mid = CreateElementId(field,id);
		
		rowstr += "<td   style='white-space: nowrap; text-overflow:ellipsis; overflow: hidden; max-width:1px;'>";
		rowstr += "<input id='"+mid+"' class='editable' field='"+field+"' extid='"+id+"' type='date' value='"+tend+"' "+att+" ></input>";
		rowstr += "</td>";
	
		///////////////////////////////////////////////////////////// SHOW
		field = 'ismilestone';
		mid = CreateElementId(field,id);
		
		att = '';
		if(isconfigured == false)
			att += 'disabled ';
		
		if(ismilestone == true)
			att += 'checked ';
		rowstr += "<td><input id='"+mid+"' extid='"+id+"'  class='editable'  field='"+field+"' extid='"+id+"'  type='checkbox' "+att+"></td>"; 
		
		////////////////////////////////////////////////////////////// PROGRESS
		var str = '<div class="shadow-lg progress position-relative" style="background-color:grey"><div class="progress-bar '+progressbar_animation_class+'" role="progressbar" style="background-color:'+progressbar_color+' !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
		rowstr += "<td>"+str+"</td>";


		///////////////////////////////////////////////////////// Is Configures	
		field = 'isconfigured';
		mid = CreateElementId(field,id);
		
		if(duplicate == 0)
		{
			if(isconfigured == true)
			{
				rowstr += '<td><input  id="'+mid+'" class="editable" field="'+field+'" extid="'+id+'"  type="checkbox" checked></td>';  		
			}
			else
			{
				rowstr += '<td><input id="'+mid+'"  class="editable" field="'+field+'" extid="'+id+'"  type="checkbox"></td>'; 
			}
		}
		else
		{
			rowstr += '<td>Duplicate</td>'; 
		}
		rowstr += "</tr>";
		$('#tablebody').append(rowstr);
	}
	
	$('#container').css('display','block');
	$("#treetable").treetable({ expandable: true },true);
	$("#treetable").show();
	$("#legend").show();
	$('#treetable').treetable('expandAll');
	//$("#treetable").treetable("expandNode", "1");
	
	$('.editable').on('change', function() {
		var extid = $(this).attr("extid");
		var value = $(this).val();
		var field = $(this).attr("field");
		if((field == 'ismilestone')||(field=='isconfigured'))
			value =  $(this).is(':checked');

		data[extid][field]=value;
		console.log(extid,field,"-"+value+"-");
		if(field == 'tstart')
		{
			console.log("fff");
			this.setAttribute(
        		"data-date",
       			 moment(this.value, "YYYY-MM-DD")
        		.format( this.getAttribute("data-date-format") )
   			 )
		}

		Update(extid);
	});
}
function UpdateTree()
{
	showall =  $('#showall').is(':checked');
	if(showall == true)
		ShowTree(data,true);
	else
		ShowTree(data,false);
	console.log("click show all");
}
function UpdateRow(extid)
{
	var row = data[extid];
	var id = row['extid'];
	isconfigured = GetElement('isconfigured',id);
	ismilestone = GetElement('ismilestone',id);
	atext = GetElement('atext',id);
	tend = GetElement('tend',id);
	tstart = GetElement('tstart',id);

	if((row.isconfigured == true)||(row.isconfigured == 'true'))
	{
		console.log(data[extid]);
		isconfigured.prop('checked', true);
		ismilestone.prop('disabled', false);
		atext.prop('disabled', false);
		tend.prop('disabled', false);
		tstart.prop('disabled', false);
	}
	else
	{
		isconfigured.prop('checked', false);
		ismilestone.prop('disabled', true);
		atext.prop('disabled', true);
		tend.prop('disabled', true);
		tstart.prop('disabled', true);
	}


	if((row.ismilestone == true)||(row.ismilestone == 'true'))
	{
		ismilestone.prop('checked', true);
		
	}
	else
	{
		ismilestone.prop('checked', false);

	}
	atext.text(row.atext);
	
	tstart.val(row.tstart);
	console.log("---"+row.tend);
	tend.val(row.tend);
}

$("#showall").click(function(e)
{
	UpdateTree();
});
@endsection
