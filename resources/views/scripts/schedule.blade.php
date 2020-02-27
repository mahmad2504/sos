<!doctype html>

<html lang="en">
<head>
  <title>Project Schedule Calculator</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>

<body>

<!-- Modal -->
<div id="Modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		
		<h4 class="modal-title">Schedule</h4>
	  </div>
	  <div class="modal-body">
		
	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	</div>
  </div>
</div>


<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <h2 class="text-center mb-5">Project Schedule Calculator</h2>
           
            <hr class="mb-5">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <span class="anchor" id="formLogin"></span>

                    <!-- form card login with validation feedback -->
                    <div class="card card-outline-secondary">
                        <div class="card-header">
                            <h3 class="mb-0">Data</h3>
                        </div>
                        <div class="card-body">
                            <form class="form" role="form" autocomplete="off" id="dataform" novalidate="" >
                                <div class="form-group">
                                    <label for="uname1">Estimates</label>
                                    <input type="text" class="form-control" name="estimate" id="estimate" required="">
									<span class="form-text small text-muted">
                                         Total Story points of all Priority-1 Requrements
                                    </span>
                                    <div class="invalid-feedback">Please enter valied value</div>
                                </div>
                                <div class="form-group">
                                    <label for="uname1">Planned Velocity</label>
                                    <input type="text" class="form-control" name="velocityperweek" id="velocityperweek" required="">
									<span class="form-text small text-muted">
                                         Planned velocity per week with 100% utilization of all resources where
										 Velocity is total story points covered in a week period with all allocated resources.
                                    </span>
                                    <div class="invalid-feedback">Please enter valied value</div>
                                </div>
                                <div class="form-group">
									<label >Project Start</label>
									<input type="date" name="projectstart" id="projectstart" max="3000-12-31" min="1000-01-01" class="form-control">
								</div>
                            </form>
                        </div>
						<button class="btn btn-success btn-lg float-right" id="compute">Compute Schedule</button>
                        <!--/card-body-->
                    </div>
                    <!-- /form card login -->

                </div>
            </div>
            <!--/row-->
        <br><br><br><br>
        </div>
        <!--/col-->
    </div>
    <!--/row-->
    <hr>
    <p class="text-center">
        <a class="small text-info d-inline-block" href="#">© 2020 Copyright : Embedded Platform Solutions</a>
		
    </p>
    
</div>
<script>
	var estimate = 0;
	var velocityperweek=0;
	var projectstart=null;
	$(document).ready(function () {
		console.log( "ready!" );
	});
	$( "#compute" ).click(function() {
		if(ValidateInput() == false)
		{
			alert( "Invalid Input Data" );
			return;
		}
		DumpInputData();
		velocityperday = velocityperweek/5;
		
		latefinish = Math.ceil(estimate/(velocityperday*1/2));
		earlyfinish = Math.ceil(estimate/(velocityperday*7/10));
		
		console.log("Earlyfinish = "+earlyfinish);
		console.log("Latefinish = "+latefinish);
		
		projectstart = new Date(projectstart);
		dateval = projectstart.getDate();
		//console.log(dateval);
		for(i=1;i<earlyfinish;i++)
		{
			projectstart.setDate(dateval + 1);
			dateval = projectstart.getDate();
			while(projectstart.getDay() == 6 || projectstart.getDay() == 0) 
			{
				projectstart.setDate(dateval + 1);
				dateval = projectstart.getDate();
			}
		}
		featurefreeze = projectstart.toString().substring(0,15);
		for(i=earlyfinish;i<latefinish;i++)
		{
			projectstart.setDate(dateval + 1);
			dateval = projectstart.getDate();
			while(projectstart.getDay() == 6 || projectstart.getDay() == 0) 
			{
				projectstart.setDate(dateval + 1);
				dateval = projectstart.getDate();
			}

		}
		release = projectstart.toString().substring(0,15);
		$('.modal-body').empty();
		$('.modal-body').append( "<p>Feature Freeze = "+featurefreeze+"</p>" );
		$('.modal-body').append( "<p>Release = "+release+"</p>" );
		$('#Modal').modal('show');
		//console.log(projectstart.getDate());
		/*latefinish_weeks = Math.trunc(latefinish/5);// weeks
		earlyfinish_weeks = Math.trunc(earlyfinish/5);// weeks
		
		latefinish_days = Math.round(latefinish%5);// weeks
		earlyfinish_days = Math.round(earlyfinish%5);// weeks
		
		console.log(earlyfinish_weeks+"-"+earlyfinish_days);
		console.log(latefinish_weeks+"-"+latefinish_days);*/
		
		
	});
	function isEmpty(val)
	{
		return (val === undefined || val == null || val.length <= 0) ? true : false;
	}
	function DumpInputData()
	{
		console.log("Estimate="+estimate);
		console.log("Velocity per week="+velocityperweek);
		console.log("Start of project="+projectstart);
	}
	function ValidateInput()
	{
		estimate = $('#estimate').val();
		velocityperweek = $('#velocityperweek').val();
		projectstart=$('#projectstart').val();
		if(isEmpty(estimate))
			return false;
		if(isEmpty(velocityperweek))
			return false;
		if(isEmpty(projectstart))
			return false;
		return true;
	}
</script>
</body>
</html>