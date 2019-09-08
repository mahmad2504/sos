@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/table.css') }}" />
<link rel="stylesheet" href="{{ asset('css/tooltipster.bundle.min.css') }}" />
@endsection
@section('style')
.h6{
    font-size: 16px;
 }
@endsection
@section('content')

<div>
	<!-- <h4 class="center" id="summary" style="width:60%;margin-bottom:-17px;">Projects Status</h4> -->

	<table style="width:60%;" class="center zui-table">
		<thead>
			<tr>
				<th width="25%"  style="text-align: left;">Project</th>
				<th width="17%" >Blockers</th>
				<th width="17%" >Risks</th>
				<th width="17%" >Issues</th> 
				<th width="14%" >Progress</th> 
				<th width="10%" >Status</th>
			</tr>
		</thead>`
	<tbody>
		@for($i=0;$i<count($data);$i++)
		<tr>
			<td style="text-align: left;font-weight:bold" id="desc{{$i}}"></td>
			<td id="blockers{{$i}}"></td>
			<td id="risks{{$i}}"></td>
			<td id="issues{{$i}}"></td>
			<td id="progress{{$i}}"></td>
			<td id="status{{$i}}"></td>
		</tr>
		@endfor		
	</tbody>
	
    </table>
</div>

<script src="{{ asset('js/msc-script.js') }}" ></script>
<script src="{{ asset('js/tooltipster.bundle.min.js') }}" ></script>
<script src="{{ asset('js/radialIndicator.min.js') }}" ></script>
@endsection
@section('script')
var user = @json($user);
var isloggedin = {{$isloggedin}};
var data = @json($data);


'use strict';
if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	
}

$(function() 
{

	console.log(data);
	for(i=0;i<data.length;i++)
	{
		project = data[i];
		blockers = project.risksissues.blockers;
		risks = project.risksissues.risks;
		issues = project.risksissues.issues;
		status = project.status;
		progress = project.progress;
		
		url = '/dashboard/'+user.name+'/'+project.summary;
		console.log(url);
		$('#desc'+i).html('<h6><a href="'+url+'">'+project.summary+'</a></h6>');
		
		count=0;
		title = '';
		for(var key in blockers)
		{
			title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
			count++;
		}
		
		if(count > 0)
			$('#blockers'+i).html('<h6><span  title="'+title+'" class="tp badge badge-danger">'+count+' Blocker</span></h6>');
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		count=0;
		title = '';
		badges = '<h6>';
		
		if(risks['Critical'] != undefined)
		{
			for(var key in risks['Critical'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-danger">'+count+' Critical</span>&nbsp&nbsp';
		
		count=0;
		title = '';
		if(risks['High'] != undefined)
		{
			for(var key in risks['High'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-warning">'+count+' High</span>&nbsp&nbsp';

			count=0;
		title = '';
		if(risks['Medium'] != undefined)
		{
			for(var key in risks['Medium'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-info">'+count+' Medium</span>&nbsp&nbsp';

		badges += '</h6>'; 
		
		$('#risks'+i).html(badges);
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		count=0;
		title = '';
		badges = '<h6>';
		
		if(issues['Critical'] != undefined)
		{
			for(var key in issues['Critical'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-danger">'+count+' Critical</span>&nbsp&nbsp';
		
		count=0;
		title = '';
		if(issues['High'] != undefined)
		{
			for(var key in issues['High'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-warning">'+count+' High</span>&nbsp&nbsp';

			count=0;
		title = '';
		if(issues['Medium'] != undefined)
		{
			for(var key in issues['Medium'])
			{
				title += "<a href='"+project.jiraurl+"/browse/"+key+"'>"+key+"</a><br>";
				count++;
			}
		}
		if(count > 0)
			badges += '<span  title="'+title+'"  class="tp badge badge-info">'+count+' Medium</span>&nbsp&nbsp';

		badges += '</h6>' ;
		
		$('#issues'+i).html(badges);
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$('#status'+i).html("<img width='80px' src='/images/"+status+".png'></img>"); 
	
		$('#progress'+i).radialIndicator({
        barColor: '#2E8B57',
        radius:10,
        barWidth: 4,
        initValue: Math.round(progress),
        roundCorner : true,
        percentage: true
  	 	});
	
	}
	$('.tp').tooltipster({ interactive: true, contentAsHTML: true});
	
});
@endsection