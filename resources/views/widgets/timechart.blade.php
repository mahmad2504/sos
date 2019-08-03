@extends('layouts.app')
@section('csslinks')
<link rel="stylesheet" href="{{ asset('css/bootstrap.3.3.7.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/timechart.style.css') }}" />
@endsection
@section('style')
@endsection
@section('content')
<div id="container" style="width:90%; margin-left: auto; margin-right: auto; display:block" class="center">
	 <div class="gantt"></div>
</div>
<script src="{{ asset('js/utility.js') }}" ></script>
<script src="{{ asset('js/bootstrap.3.3.7.min.js') }}" ></script>
<script src="{{ asset('js/jquery.fn.gantt.js') }}" ></script>
@endsection
@section('script')
var username = "{{$user->name}}";
var userid = {{$user->id}};
var projectid = {{$project->id}};
var isloggedin = {{$isloggedin}};

if(isloggedin)
{
	$('.navbar').removeClass('d-none');
	$('#dashboard_menuitem').show();
	$('#dashboard_menuitem').attr('href',"{{route('dashboard',[$user->name,$project->name])}}");
}
$('#navbarDropdown').hide();	
 $(function() {
            "use strict";

            var demoSource = [{
                name: "Sprint 0",
                desc: "Analysis",
                values: [{
                    from: 1320192000000,
                    to: 1322401600000,
                    label: "Requirement Gathering",
                    customClass: "ganttRed"
                }]
            },{
                desc: "Scoping",
                values: [{
                    from: 1322611200000,
                    to: 1323302400000,
                    label: "Scoping",
                    customClass: "ganttRed"
                }]
            },{
                name: "Sprint 1",
                desc: "Development",
                values: [{
                    from: 1323802400000,
                    to: 1325685200000,
                    label: "Development",
                    customClass: "ganttGreen"
                }]
            },{
                name: " ",
                desc: "Showcasing",
                values: [{
                    from: 1325685200000,
                    to: 1325695200000,
                    label: "Showcasing",
                    customClass: "ganttBlue"
                }]
            },{
                name: "Sprint 2",
                desc: "Development",
                values: [{
                    from: 1325695200000,
                    to: 1328785200000,
                    label: "Development",
                    customClass: "ganttGreen"
                }]
            },{
                desc: "Showcasing",
                values: [{
                    from: 1328785200000,
                    to: 1328905200000,
                    label: "Showcasing",
                    customClass: "ganttBlue"
                }]
            },{
                name: "Release Stage",
                desc: "Training",
                values: [{
                    from: 1330011200000,
                    to: 1336611200000,
                    label: "Training",
                    customClass: "ganttOrange"
                }]
            },{
                desc: "Deployment",
                values: [{
                    from: 1336611200000,
                    to: 1338711200000,
                    label: "Deployment",
                    customClass: "ganttOrange"
                }]
            },{
                desc: "Warranty Period",
                values: [{
                    from: 1336611200000,
                    to: 1349711200000,
                    label: "Warranty Period",
                    customClass: "ganttOrange"
                }]
            }];

            // shifts dates closer to Date.now()
            var offset = new Date().setHours(0, 0, 0, 0) -
                new Date(demoSource[0].values[0].from).setDate(35);
            for (var i = 0, len = demoSource.length, value; i < len; i++) {
                value = demoSource[i].values[0];
                value.from += offset;
                value.to += offset;
            }

            $(".gantt").gantt({
                source: demoSource,
                navigate: "scroll",
                scale: "weeks",
                maxScale: "months",
                minScale: "hours",
                itemsPerPage: 10,
                scrollToToday: true,
                useCookie: true,
                onItemClick: function(data) {
                    alert("Item clicked - show some details");
                },
                onAddClick: function(dt, rowId) {
                    alert("Empty space clicked - add an item!");
                },
                onRender: function() {
                    if (window.console && typeof console.log === "function") {
                        console.log("chart rendered");
                    }
                }
            });

            $(".gantt").popover({
                selector: ".bar",
                title: function _getItemText() {
                    return this.textContent;
                },
                container: '.gantt',
                content: "Here's some useful information.",
                trigger: "hover",
                placement: "auto right"
            });

            //prettyPrint();

        });


@endsection