@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/bootstrap-year-calendar.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/msc-style.css') }}" />
@endsection

@section('style')
.modal-header h3 { width: 90% }
.modal-content 
{
   background-clip: border-box;
   border: none !important;
}
.boxshadow 
{
  -moz-box-shadow: 3px 3px 5px #535353;
  -webkit-box-shadow: 3px 3px 5px #535353;       
  box-shadow: 3px 3px 5px #535353;
}
.roundbox
{  
  -moz-border-radius: 6px 6px 6px 6px;
  -webkit-border-radius: 6px;  
  border-radius: 6px 6px 6px 6px;
}
.modal-dialog-calendar,
.modal-content-calendar {
    /* 80% of window height */
    height: 90%;
}

.modal-body-calendar {
    /* 100% = dialog height, 120px = header + footer */
    overflow-y: scroll;
}

.modal-dialog-delete {
    margin: 20vh auto 0px auto
}

.modal-dialog-event {
    margin: 20vh auto 0px auto
}

.modal-content { 
    border-radius: 10px;
    -webkit-border-radius: 10px;
    -moz-border-radius: 10px;
	
}

.modal-dialog {
    border: 10px !important;
}

.modal-content-event {
  background-color:#cdcdcd;
} 

@endsection


@section('content')


<!-- Button trigger modal -->

<!-- Modal -->
<div class="modal fade" id="calendar-modal" tabindex="-1" role="dialog" aria-labelledby="calendar-modal-label" aria-hidden="true">
  <div class="modal-dialog-calendar modal-dialog modal-lg"  role="document">
    <div class="modal-content-calendar modal-content">
      <div class="modal-header">
        <button id="save_calendar" type="button" class="btn btn-primary">Save changes</button>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body-calendar modal-body">
		
        <div style="width:100%;" id="calendar">Loading</div>
		<div style="display:none" class="loading"></div>
		
      </div>
      <!-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>-->
    </div>
  </div>
</div>

<div class="modal fade" id="event-modal" tabindex="-1" role="dialog" aria-labelledby="event-modal-label" aria-hidden="true">
  <div class="modal-dialog-event modal-dialog role="document">
	<div class="modal-content-event modal-content">
	  
	  <div class="modal-body ">
		<input value="" name="event-index" type="hidden">
		<form class="form-horizontal">
			<div class="form-group">
				<label for="min-date" class="col-sm-4 control-label">Name</label>
				<div class="col-sm-12">
					<input name="event-name" class="form-control" type="text" value="fff">
				</div>
			</div> 
			<!-- <div class="form-group">
				<label for="min-date" class="col-sm-4 control-label">Location</label>
				<div class="col-sm-12">
					<input name="event-location" class="form-control" type="text">
				</div> 
			</div>-->
			<div class="form-group">
				<label for="min-date" class="col-sm-4 control-label">Dates</label>
				<div class="col-sm-12">
					<div class="input-group input-daterange" data-provide="datepicker">
						<input name="event-start-date" class="form-control" value="2012-04-05" type="text">
							<span class="input-group-text">to</span>
							<input name="event-end-date" class="form-control" value="2012-04-19" type="text">
					</div>
				</div>
			</div>
		</form>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="button" id="save-event" class="btn btn-primary">Mark</button>
		</div>
		
	  </div>
	 
	</div>
  </div>
</div>

<div class="modal fade" id="delete-modal" tabindex="-1" role="dialog" aria-labelledby="delet-modal-label" aria-hidden="true">
  <div class="modal-dialog-delete modal-dialog modal-sm" role="document">
    <div class=" modal-content">
      <div class="modal-header alert-primary">
        <button id="delete_button" type="button" class="btn btn-danger">Delete</button>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
    </div>
  </div>
</div>


<div style="width:80%; margin-left: auto; margin-right: auto; text-align:center;color:grey" class="center">
	<div id="table">
	</div>
</div>


<script src="{{ asset('js/bootstrap-datepicker.min.js') }}" ></script>
<script src="{{ asset('js/bootstrap-year-calendar.min.js') }}" ></script>
<script src="{{ asset('js/tabulator.table.js') }}" ></script>
<script src="{{ asset('js/calendar.js') }}" ></script>
<script src="{{ asset('js/msc-script.js') }}" ></script>
@endsection

@section('script')
var userid = {{$user->id}};
var username =  "{{$user->name}}";
var projectname =  "{{$project->name}}";
var resources = @json($presources);
var _token = "{{ csrf_token() }}";
var table = null;
function OnCalendarSaved(data)
{
	$('.loading').hide();
	$('#calendar-modal').modal('hide');
}
function OnCalendarSaveFailed(data)
{
	$('.loading').hide();
	$('#calendar-modal').modal('hide');
}
async function OnSaveCalendar(data)
{
	var dataSource = $('#calendar').data('calendar').getDataSource();
	$('.loading').show();
	for(i=0;i<dataSource.length;i++)
	{
		dataSource[i].startDate = moment(dataSource[i].startDate).format('YYYY-MM-DD');
		dataSource[i].endDate = moment(dataSource[i].endDate).format('YYYY-MM-DD');
	}
	calendar._token = "{{ csrf_token() }}"
	calendar.data=dataSource;
	console.log(calendar);
	
	$.ajax({
		type:"PUT",
		url:'/calendar/'+resourcename,
		cache: false,
		data:calendar,
		success: OnCalendarSaved,
		error: OnCalendarSaveFailed
	});
}

$(document).ready(function()
{
	console.log("Loading Resource Page");
	$('.navbar').removeClass('d-none');
	$('#save_calendar').on('click',OnSaveCalendar);
	
	table = new Tabulator("#table", InitTabulator());
	InitCalendar();
	
})
@endsection