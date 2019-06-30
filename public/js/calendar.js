
var dataSource=null;
var calendar = null;
function editEvent(event) {
    $('#event-modal input[name="event-index"]').val(event ? event.id : '');
    //$('#event-modal input[name="event-name"]').val(event ? event.name : 'Holiday');
	$('#event-modal input[name="event-name"]').val('Holiday');
    $('#event-modal input[name="event-location"]').val(event ? event.location : '');
    $('#event-modal input[name="event-start-date"]').datepicker('update', event ? event.startDate : '');
    $('#event-modal input[name="event-end-date"]').datepicker('update', event ? event.endDate : '');
    $('#event-modal').modal();
}

function deleteEvent(event) {
    var dataSource = $('#calendar').data('calendar').getDataSource();

    for(var i in dataSource) {
        if(dataSource[i].id == event.id) {
            dataSource.splice(i, 1);
            break;
        }
    }
    console.log("Delete event");
    $('#calendar').data('calendar').setDataSource(dataSource);
}

function saveEvent() {
	console.log("Save event");
	if($('#event-modal input[name="event-name"]').val().trim().length==0)
		$('#event-modal input[name="event-name"]').val('Holiday');
	
    var event = {
        id: $('#event-modal input[name="event-index"]').val(),
        name: $('#event-modal input[name="event-name"]').val(),
        location: $('#event-modal input[name="event-location"]').val(),
        startDate: $('#event-modal input[name="event-start-date"]').datepicker('getDate'),
        endDate: $('#event-modal input[name="event-end-date"]').datepicker('getDate')
    }
    var dataSource = $('#calendar').data('calendar').getDataSource();
    if(event.id) {
        for(var i in dataSource) {
            if(dataSource[i].id == event.id) {
				dataSource[i].name = event.name;
                dataSource[i].location = event.location;
                dataSource[i].startDate = event.startDate;
                dataSource[i].endDate = event.endDate;
				dataSource[i].color = 'green';
            }
        }
    }
    else
    {
        var newId = 0;
        for(var i in dataSource) {
            if(dataSource[i].id > newId) {
                newId = dataSource[i].id;
            }
        }
        
        newId++;
        event.id = newId;
		event.color = 'green';
		event.color = 'green';
        dataSource.push(event);
    }
    
    $('#calendar').data('calendar').setDataSource(dataSource);
    $('#event-modal').modal('hide');
}
async function OnCalendarDataLoad(data)
{
	//await sleep(5000);
	
	calendar = data;
	dataSource = JSON.parse(calendar.data);
	for(var i=0;i<dataSource.length;i++)
	{
		dataSource[i].startDate = new Date(dataSource[i].startDate);
		dataSource[i].endDate = new Date(dataSource[i].endDate);
	}
	$('#calendar').data('calendar').setDataSource(dataSource);
	$('.loading').hide();
	$('#calendar').show();
	//$('#calendar-modal').show();
}
var delete_id = -1;

function OnCalendarDataLoadFail()
{
	$('.loading').hide();
	

}
function ShowCalendar(username)
{
	dataSource = null;
	resourcename = username;
	$('.loading').show();
	$('#calendar').hide();
	//$('#calendar-modal').hide();
	$("#delete_button").on( "click", function() {
		console.log("Hiding");
		deleteEvent(delete_id);
		$('#delete-modal').modal('hide');
	});
	
	$('#calendar').data('calendar').setDataSource(dataSource);
	$.ajax({
		type:"GET",
		url:'/calendar/'+resourcename,
		cache: false,
		data:null,
		success: OnCalendarDataLoad,
		error: OnCalendarDataLoadFail
	});
}

//	$('#calendar').data('calendar').setDataSource(dataSource);
//					$('#calendar-modal').show();
//					console.log("dddd");
function InitCalendar()
{
	var currentYear = new Date().getFullYear();
    $('#calendar').calendar({ 
        enableContextMenu: false,
        enableRangeSelection: true,
		allowOverlap:false,
		//style:'background',
        contextMenuItems:[
            {
                text: 'Update',
                click: editEvent
            },
            {
                text: 'Delete',
                click: deleteEvent
            }
        ],
        selectRange: function(e) {
			console.log("Select Range");
            editEvent({ startDate: e.startDate, endDate: e.endDate });
        },
        mouseOnDay: function(e) {
            if(e.events.length > 0) {
                var content = '';
                
                for(var i in e.events) {
                    //content += '<div class="event-tooltip-content">'
                    //               + '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].name + '</div>'
                    //                + '<div class="event-location">' + e.events[i].location + '</div>'
                    //            + '</div>';
					content += '<div class="event-tooltip-content">'
                                    + '<div class="event-name" style="color:' + e.events[i].color + '">' + e.events[i].id+"  "+e.events[i].name + '</div>'
                                + '</div>';				
                }
            
                $(e.element).popover({ 
                    trigger: 'manual',
                    container: 'body',
                    html:true,
                    content: content
                });
                
                $(e.element).popover('show');
            }
        },
        mouseOutDay: function(e) {
            if(e.events.length > 0) {
                $(e.element).popover('hide');
            }
        },
		clickDay: function(e) {
			
			console.log("Click day");
			if(e.events === undefined)
			{
				
			}
			else
			{
				if(e.events.length > 0)
				{
					//mscConfirm("Dddd", null);
					
					$('#delete-modal').modal();
					$(e.element).popover('hide');
					delete_id = e.events[0];
					//deleteEvent(e.events[0]);
					
					//console.log(e.events[0].id);
				}
			}
			
             //console.log($(e.element).hasClass("day-start"));
             //sconsole.log(yclick);
         },
        dayContextMenu: function(e) {
            $(e.element).popover('hide');
			
			//alert(e.events[0].id);
			e.preventDefault();
			return false;
        },
        dataSource:null 
    });
	 $('#save-event').click(function() {
        saveEvent();
    });
}