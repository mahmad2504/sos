var source =  null;
var logger = new Logger('log');
var nextmsgid = 0;
var firstmsgid = 0;
function Clear()
{
	logger.clear();
	nextmsgid = 0;
	firstmsgid = 0;
}

function closeConnection() {
	if(source == null)
		return;
	
	source.close();
	logger.log('> Connection was closed');
	$('#sync').prop('disabled', false);
	$('#rebuild').prop('disabled', false);
	$('#oasync').prop('disabled', false);
	updateConnectionStatus('Disconnected', false);
}
function Rebuild()
{
	Sync(1);
}
function OASync()
{
	nextmsgid = 0;
	firstmsgid = 0;
	$('#log').empty();
	console.log("Connecting with Open Air Server");
	$('.info').empty();
	$('#log').show();
	var projectid = $('#oasync').attr('projectid');
	source = new EventSource($('#oasync').attr('url')+'?projectid='+projectid);
	console.log($('#oasync').attr('url')+'?projectid='+projectid);
	source.addEventListener('message', function(event) {
	var data = JSON.parse(event.data);
	var d = new Date(data.id * 1e3);
	var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	logger.log('' + timeStr+' '+data.msg);
	}, false);

	source.addEventListener('open', function(event) 
	{
		logger.log('> Connected');
		$('#sync').prop('disabled', true);
		$('#rebuild').prop('disabled', true);
		$('#oasync').prop('disabled', true);
		updateConnectionStatus('Connected', true);
	}, false);

	source.addEventListener('error', function(event) 
	{
		//console.log("Error");
		//if (event.eventPhase == 2) 
		{ //EventSource.CLOSED
			logger.log('> Disconnected');
			$('#sync').prop('disabled', false);
			$('#rebuild').prop('disabled', false);
			$('#oasync').prop('disabled', false);
			updateConnectionStatus('Disconnected', false);
			source.close();
		}
	}, false);
}
function Sync($rebuild=0) 
{
	nextmsgid = 0;
	firstmsgid = 0;
	$('#log').empty();
	if($rebuild == 1)
		console.log("Initiating Rebuild");
	else
		console.log("Initiating Sync");
	var projectid = $('#sync').attr('projectid');
	console.log($('#sync').attr('url'));
	$('.info').empty();
	$('#log').show();
	//source = new EventSource($(this).attr('url'));
	if($rebuild == 1)
		source = new EventSource($('#sync').attr('url')+'?projectid='+projectid+'&rebuild=1');
	else
		source = new EventSource($('#sync').attr('url')+'?projectid='+projectid);
	
	source.addEventListener('message', function(event) {
	var data = JSON.parse(event.data);
	var d = new Date(data.id * 1e3);
	var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	logger.log('' + timeStr+' '+data.msg);
	}, false);

	source.addEventListener('open', function(event) 
	{
		logger.log('> Connected');
		$('#sync').prop('disabled', true);
		$('#rebuild').prop('disabled', true);
		$('#oasync').prop('disabled', true);
		updateConnectionStatus('Connected', true);
	}, false);

	source.addEventListener('error', function(event) 
	{
		//console.log("Error");
		//if (event.eventPhase == 2) 
		{ //EventSource.CLOSED
			logger.log('> Disconnected');
			$('#sync').prop('disabled', false);
			$('#rebuild').prop('disabled', false);
			$('#oasync').prop('disabled', true);
			updateConnectionStatus('Disconnected', false);
			source.close();
		}
	}, false);
}
function Logger(id) {
  this.el = document.getElementById(id);
  this.id = id;
  
}
Logger.prototype.log = function(msg, opt_class) {
  var fragment = document.createDocumentFragment();
  var p = document.createElement('p');
  p.className = opt_class || 'info';
  
  
  msgcount = nextmsgid - firstmsgid;
  if(msgcount > 20)
  {
	  document.getElementById(firstmsgid).remove();
	  firstmsgid++;
  }
  var wait  = 0;
  p.setAttribute("id", nextmsgid++);
  if(msg.search('Error::')>0)
  {
  	p.style.color = "#ff0000";
	msg = msg.replace('Error::','');
  }
  else if(msg.search('Warning::')>0)
  {
  	p.style.color = "#FF4500";
	msg = msg.replace('Warning::','');
  }
  
  else if(msg.search('Success::')>0)
  {
  	p.style.color = "#32CD32";
    msg = msg.replace('Success::','');
  }
   else if(msg.search('Wait::')>0)
  {
  	 msg = msg.replace('Wait::','');
	 wait  = 1;
  }
  p.textContent = msg;
  //console.log(msg);
 //this.el.textContent = '';
  fragment.appendChild(p);
  
  
  		
  //var img = $('<img id="dynamic">'); //Equivalent: $(document.createElement('img'))
  //img.attr('src', '/images/loading.gif');
  //img.css({ 'height': '80px', 'width': '80px' });
  //img.appendTo('#'+this.id);
  var e = document.getElementById('loader');
  if(e)
	  e.remove();
 
  this.el.appendChild(fragment);
  if(wait)
  {
	var img = $('<div id="loader" class="loader">'); //Equivalent: $(document.createElement('img'))
	img.appendTo('#'+(nextmsgid-1));
  }	
};

Logger.prototype.clear = function() {
	$('#log').empty();
	
  //this.el.textContent = '';
};
function updateConnectionStatus(msg, connected) {
  var el = document.querySelector('#connection');
  if (connected) {
    if (el.classList) {
      el.classList.add('connected');
      el.classList.remove('disconnected');
    } else {
      el.addClass('connected');
      el.removeClass('disconnected');
    }
  } else {
    if (el.classList) {
      el.classList.remove('connected');
      el.classList.add('disconnected');
    } else {
      el.removeClass('connected');
      el.addClass('disconnected');
    }
  }
  el.innerHTML = msg + '<div></div>';
}