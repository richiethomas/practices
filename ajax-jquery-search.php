<script>
// AJAX call for autocomplete 
// from https://phppot.com/jquery/jquery-ajax-autocomplete-country-example/
$(document).ready(function(){
	//serach users
	$("#search-box").keyup(function(){
		$.ajax({
		type: "POST",
		url: "/ajax_get_users.php",
		data:'keyword='+$(this).val()+"&search=<?php echo (isset($search)) ? 1 : 0 ?>",
		beforeSend: function(){
			$("#search-box").css("background","#FFF");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		}
		});
	});
	
});
//To select user name
function selectUser(val) {
	$("#search-box").val(val);
	$("#suggesstion-box").hide();
}
</script>

