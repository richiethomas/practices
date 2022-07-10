
<script>
function single_claim(regid) {
	var id = '_'+regid;	
	var pa = document.getElementById('payamount'+id).value;
	var pw = document.getElementById('paywhen'+id).value;
	var pc = document.getElementById('paychannel'+id).value;	
	var ss = document.getElementById('searchstart').value;
	var se = document.getElementById('searchend').value;
	
	var link =encodeURI( '/admin-registrations/update/'+regid+'?regid='+regid+'&payamount='+pa+'&paywhen='+pw+'&paychannel='+pc+'&searchstart='+ss+'&searchend='+se);
	//console.log(link);

	window.location.href = link;
	return false;
}

</script>

<?php
$this_page = "/admin-registrations/view/?searchstart=".urlencode($searchstart)."&searchend=".urlencode($searchend);

echo "<h1><a href='$this_page&sortby=$sortby'>Registrations</a></h1>\n";
echo "<div class='row'><div class='col-md-12'>";


$nav = "<p><a href='/admin-registrations/view/?searchstart=$laststart&searchend=$lastend&sortby=$sortby'>last month</a> | <a href='/admin-registrations/view/?sortby=$sortby'>this month</a> | <a href='/admin-registrations/view/?searchstart=$nextstart&searchend=$nextend&sortby=$sortby'>next month</a></p>\n";
echo $nav;


echo "<form action='/admin-registrations/view/' method='post'>\n";

echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start');
echo \Wbhkit\texty('searchend', $searchend, 'Search End'); 
echo \Wbhkit\hidden("sortby", $sortby);


echo \Wbhkit\submit('Update');


echo "<div class='row my-2 py-2 fw-bold'>";
echo "<div class='col-md-1 ".heading_class('reg')."'><a href='{$this_page}&sortby=reg'>Reg #</a></div>\n";
echo "<div class='col-md-3 ".heading_class('student')."'><a href='{$this_page}&sortby=student'>Student</a></div>\n";
echo "<div class='col-md-3 ".heading_class('class')."'><a href='{$this_page}&sortby=class'>Class</a></div>\n";
echo "<div class='col-md-1'>Amount</div>";
echo "<div class='col-md-2 ".heading_class('when')."'><a href='{$this_page}&sortby=when'>When</a></div>";
echo "<div class='col-md-2'>Channel</div>";
echo "</div>\n";
		
foreach ($registrations as $r) {
		
	echo "<div class='row my-2 py-2 border-top";
	if (!show_when($r['pay_when']) && !$r['pay_channel']) { echo " bg-warning "; }
	echo "'>";
	
	echo "<div class='col-md-1'>{$r['id']}</div>\n";
	echo "<div class='col-md-3'>{$r['email']},<br>{$r['nice_name']}</div>\n";
	echo "<div class='col-md-3'><a href='/admin-workshop/view/{$r['workshop_id']}'>{$r['title']}</a>,<br> {$r['teacher_nice_name']},  ".\Wbhkit\figure_year_minutes(strtotime($r['start'])).", \${$r['cost']}</div>\n";
	echo "<div class='col-md-1'>".\Wbhkit\texty("payamount_{$r['id']}", $r['pay_amount'], 0)."</div>";
	echo "<div class='col-md-2'>".\Wbhkit\texty("paywhen_{$r['id']}", show_when($r['pay_when']), 0)."</div>";
	echo "<div class='col-md-1'>".\Wbhkit\texty("paychannel_{$r['id']}", $r['pay_channel'], 0)."</div>";
	echo "<div class='col-md-1'><button id='btn{$r['id']}' class='btn btn-success btn-sm' onClick=\"return single_claim('".$r['id']."')\">Claim</button></div>";
	echo "</div>\n";
		
}
echo \Wbhkit\submit('Update');
echo "</form>\n";
echo "</div></div>\n";	

function heading_class($col) {
	global $sortby;
	if ($col == $sortby) { return 'bg-info'; }
	return null;
}

function show_when($timestring) {
	if ($timestring && $timestring != '0000-00-00' && date('Y', strtotime($timestring)) != '1969') {
		return date('Y-m-d', strtotime($timestring));
	}
	return null;
}
  		
?>
