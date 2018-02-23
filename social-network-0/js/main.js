function fadeOut(el){
	var elem = document.getElementById(el);
	elem.style.transition = "opacity 0.5s linear 0s";
	elem.style.opacity = 0;
}
function fadeIn(el){
	var elem = document.getElementById(el);
	elem.style.transition = "opacity 0.5s linear 0s";
	elem.style.opacity = 1;
}

function restrict(elem){
	var tf = _(elem);
	var rx = new RegExp;
	if(elem == "email"){
		rx = /[' "]/gi;
	} else if(elem == "username"){
		rx = /[^_.a-z0-9]/gi;
	}
	tf.value = tf.value.replace(rx, "");
}


//quick function to make selecting and manipulating elements easier 
function _(x){
  return document.getElementById(x);
}

//ajax module
function ajaxObj( meth, url ) {
	var x = new XMLHttpRequest();
	x.open( meth, url, true );
	x.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	return x;
}
function ajaxReturn(x){
	if(x.readyState == 4 && x.status == 200){
	    return true;	
	}
}
//ajax module

function toggleElement(x){
	var x = _(x);
	if(x.style.display == 'block'){
		x.style.display = 'none';
	}else{
		x.style.display = 'block';
	}
	return false;
}

function toggleupdate(x){
	var i = _("info");
	if(i.style.display == 'none'){
		i.style.display = 'block';
	}else{
		i.style.display = 'none';
	}
	var x = _(x);
	if(x.style.display == 'block'){
		x.style.display = 'none';
	}else{
		x.style.display = 'block';
	}
	return false;
}

function toggleOpacity(x){
	var x = _(x);
	if(x.style.opacity == 1){
		x.style.opacity = 0;	
	}else{
		x.style.opacity = 1;	
	}
}