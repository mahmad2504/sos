@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.css') }}" />
<link rel="stylesheet" href="{{ asset('css/jquery.treetable.theme.default.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection
@section('style')
.progress {height: 10px;}
@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:none" class="center">

	<div class="loading">Loading&#8230;</div>
	<p id='description'>Description</p>
	<table id="treetable" style="display:none;  box-shadow: 10px 5px 5px grey;" class="table">
		<caption style="caption-side:top;text-align: center">
		  <a href="#"  onclick="jQuery('#treetable').treetable('expandAll'); return false;">Expand all</a>&nbsp|
		  <a href="#" onclick="jQuery('#treetable').treetable('collapseAll'); return false;">Collapse all</a>
		</caption>
		<col style="width:40%;border-right:1pt solid lightgrey;"> <!--Title  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Jira  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Blockers  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Dependecnies  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Sprint  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Estimates  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Duplicate  --> 
		<col style="width:10%;border-right:1pt solid lightgrey;"> <!--Progress  --> 
		
		<thead style="background-color: SteelBlue;color: white;font-size: .8rem;">
		  <tr>
			<th>Title</th>
			<th>Jira</th>
			<th class="blockers">Blockers</th>
			<th class="dependencies">Dependency</th>
			<th class='sprintcolumn'>Sprint</th>
			<th id='estimatecolumn'></th>
			<th>Duplicate</th>
			<th>Progress</th>
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
		<span>Workpackage<span style="margin-top:20px;padding:5px;" class="WORKPACKAGE">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Epic<span style="margin-top:20px;padding:5px;" class="EPIC">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Task<span style="margin-top:20px;padding:5px;" class="TASK">&nbsp&nbsp&nbsp</span></span>
		<span style="margin-top:20px;padding:15px;"></span>
		<span>Defect<span style="margin-top:20px;padding:5px;" class="DEFECT">&nbsp&nbsp&nbsp</span></span>
		
	</div>
</div>
<script src="{{ asset('js/jquery.treetable.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection
@section('script')

var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var cur_row = null;
var isloggedin = {{$isloggedin}};
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
	console.log(response.description);
	$('#description').append(response.description);
	if(response.estimation == 0)
	{
		header = 'Story Points';
		estimate_units = 'Points';
	}
	if(response.estimation == 1)
	{
		header = 'Time Estimates';
		estimate_units = 'Days';
	}
	$('#estimatecolumn').append(header);
}

$(document).ready(function()
{
	if(isloggedin)
	{
		$('.navbar').removeClass('d-none');
		$('#dashboard_menuitem').show();
		$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	}
	
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
	LoadProjectData("{{route('getproject',['id'=>$project->id])}}",null,OnProjectDataReceived,function(response){});
	$.ajax(
	{
		type:"GET",
		url:"{{ route('gettreeviewdata',[$project->id]) }}",
		data:null,
		success: function(response)
		{
			$('.loading').hide();
			console.log(response);
			ShowTree(JSON.parse(response)) ;
		},
		error: function (error) 
		{
			$('.loading').hide();
			console.log(error);  
			mscAlert('Error', 'Project Database Missing. Please sync with Jira and try again', function(){window.location.href = "/";})
		}
	});
	function round(value, precision) 
	{
		var multiplier = Math.pow(10, precision || 0);
		return Math.round(value * multiplier) / multiplier;
	}
	function ShowTree(response)
	{
		console.log(response);
		var data = 
		[
			['10','','file','Title file1','http://www.google.com','HMIP','23','25'],
			['10-1','10','file','Title file2','http://www.google.com','HMIP','23','25'],
			['10-1-1','10-1','file','Title file2','http://www.google.com','HMIP','23','25'],
			['10-1-1-1','10-1-1','file','Title file2','http://www.google.com','HMIP','23','25']
		];
		data = response;
		var dependencies = 0;
		var blockers = 0;
		var sprints = 0;
		
		for (var exitid in data)
		{
			var row = data[exitid];
			var id = row['extid'];
			var pid = row['pextid'];
			var _class =row['issuetype'];
			var title=row['summary'];
			var link=row['jiraurl'];
			var linktext=row['key'];
			var estimate=Math.round(row['estimate']);
			var progress=round(row['progress'],1);
			var status=row['status'];
			var priority=row['priority'];
			var blockedtasks=row['blockedtasks'];
			var sprintstate = row['sprintstate'];
			var sprintname = row['sprintname'];
			var duplicate=row['duplicate'];
			var assignee=row['assignee'];
			if(assignee == 'unassigned')
				assignee = '';
			var sprintlink = link+"/secure/RapidBoard.jspa?rapidView="+row['sprintid'];
			
			var blockedtasksstr = '';
			
			
			var dtasks=row['dependson'];
			if(row['dependencies'] !== undefined)
			{
				dependencies = row['dependencies'];
			}
			if(row['blockers'] !== undefined)
			{
				blockers = row['blockers'];
			}
			console.log(blockers);
			
			var dtasksstr = '';
			
			var del ='';
			for(var i=0;i<dtasks.length;i++)
			{
				dtasksstr += del+"<a href='"+link+"/browse/"+key+"'>"+dtasks[i]+"</a>";
				del="&nbsp&nbsp";
			}
			
			var del ='';
			for (var key in blockedtasks)
			{
				blockedtasksstr += del+"<a href='"+link+"/browse/"+key+"'>"+key+"</a>";
				del="&nbsp&nbsp";
			}
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
			if(duplicate == 1)
			{
				rowstr += id+" "+title+'&nbsp&nbsp<span   class="badge badge-warning">Duplicate&nbsp&nbsp&nbsp&nbsp</span>'+"</span></td>";
			}
			else
				rowstr += id+"  "+title+"</span></td>";

			

			if(linktext == id)// Not a Jira Task 
				rowstr += '<td></td>';
			else
				rowstr += "<td><a style='font-size:.6rem; color:"+color+";' href='"+link+"/browse/"+linktext+"'>"+linktext+'</a></td>';
			rowstr += "<td class='blockers' style='font-size:.6rem;'>"+blockedtasksstr+"</td>";
			rowstr += "<td class='dependencies' style='font-size:.6rem;'>"+dtasksstr+"</td>";
			
			if(sprintstate == 'ACTIVE')
				style="color:green";
			else if(sprintstate == 'FUTURE')
				style="";
			else if(sprintstate == 'CLOSED')
				style="text-decoration: line-through;color:grey";
			else
				style= '';
			if(sprintname.length > 0)
				sprints = 1;
			rowstr += "<td class='sprintcolumn'><a style='"+style+"' href='"+sprintlink+"'>"+sprintname+'</a></td>';
			if(estimate > 0)
				rowstr += "<td>"+estimate+" "+estimate_units+"</td>";
			else
				rowstr += "<td></td>";

			if(duplicate == 1)
				rowstr += '<td></td>';
			else
			rowstr += '<td>'+assignee+'</td>';
			var str = '<div class="shadow-lg progress position-relative" style="background-color:grey"><div class="progress-bar '+progressbar_animation_class+'" role="progressbar" style="background-color:'+progressbar_color+' !important; width: '+progress+'%" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100"></div></div>'+'<small style="color:black;" class="justify-content-center d-flex">'+progress+'%</small>';
			
			
			rowstr += "<td>"+str+"</td>";
			rowstr += "</tr>";
			//console.log(rowstr);
			$('#tablebody').append(rowstr);
		}
		
		
		/*dependencies=0;
		blockers =0;
		sprints=0;*/
		width = 90;
		$('#container').css('width',width+'%');
		if(dependencies == 0)
		{
			width = width-10;
			$('.dependencies').hide();
			$('#container').css('width',width+'%');
		}
		if(blockers == 0)
		{
			width = width-10;
			$('.blockers').hide();
			$('#container').css('width',width+'%');
		}
		
		
		if(sprints == 0)
		{
			width = width-10;
			$('.blockers').hide();
			$('.sprintcolumn').hide();
		}
		$('#container').css('display','block');
		
		$("#treetable").treetable({ expandable: true });
		$("#treetable").show();
		$("#legend").show();
		$("#treetable").treetable("expandNode", "1");
		
	}
})
@endsection