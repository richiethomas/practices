<script>
// AJAX call for autocomplete 
// from https://phppot.com/jquery/jquery-ajax-autocomplete-country-example/


window.onload = function() {
	document.getElementById('search-box').addEventListener('keyup', handleSearch);
}

function handleSearch() {
	const xhttp = new XMLHttpRequest();
	
	xhttp.onload = function() {
		if (this.readyState == 4 && this.status == 200) {
			sub = document.getElementById("suggesstion-box");
			sub.style.display = 'block';
			sub.innerHTML = this.responseText;
			document.getElementById('search-box').style.backgroundColor = '#FFF';
		}
	}
	
	document.getElementById('search-box').style.backgroundColor = '#FFF';
	xhttp.open("POST", '/assets/ajax/get_users.php');
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	searchkey = document.getElementById('search-box').value;
	xhttp.send('keyword='+searchkey+"&search=<?php echo isset($search) ? 1 : 0 ?>");
	
}

//To select user name
function selectUser(val) {
	document.getElementById('search-box').value = val;
	document.getElementById('suggesstion-box').style.display = 'none';
}
</script>
