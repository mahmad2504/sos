var source =  null;
var logger = new Logger('log');

function Clear()
{
	logger.clear()
}

function closeConnection() {
	if(source == null)
		return;
	
	source.close();
	logger.log('> Connection was closed');
	$('#sync').prop('disabled', false);
	updateConnectionStatus('Disconnected', false);
}
function Rebuild()
{
	Sync(1);
}
function Sync($rebuild=0) 
{
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
		updateConnectionStatus('Connected', true);
	}, false);

	source.addEventListener('error', function(event) 
	{
		//console.log("Error");
		//if (event.eventPhase == 2) 
		{ //EventSource.CLOSED
			logger.log('> Disconnected');
			$('#sync').prop('disabled', false);
			updateConnectionStatus('Disconnected', false);
			source.close();
		}
	}, false);
}
function Logger(id) {
  this.el = document.getElementById(id);
}
Logger.prototype.log = function(msg, opt_class) {
  var fragment = document.createDocumentFragment();
  var p = document.createElement('p');
  p.className = opt_class || 'info';
  p.textContent = msg;
  if(msg.search('Error::')>0)
  	p.style.color = "#ff0000";
  if(msg.search('Warning::')>0)
  	p.style.color = "#FF4500";
  //console.log(msg);
 //this.el.textContent = '';
  fragment.appendChild(p);
  this.el.appendChild(fragment);
};

Logger.prototype.clear = function() {
  this.el.textContent = '';
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