<script>
$( document ).ready(function() {
	// payroll code
	$( "#todays_date" ).click(function(e) {
		var d = new Date();
		var td = (d.getMonth()+1)+'/'+d.getDate()+'/'+d.getFullYear();
		$("input[name*='whenpaid']").val(td);
		e.preventDefault();
	});
	$( "#this_date" ).click(function(e) {
		var td = $('#this_date_val').val();
		$("input[name*='whenpaid']").val(td);
		e.preventDefault();
	});

});


function single_claim(task, tableid) {
	var id = 'pd_'+task+'_'+tableid+'_';
	var amt = document.getElementById(id+'amount').value;
	var wp = document.getElementById(id+'whenpaid').value;
	var wh = document.getElementById(id+'whenhappened').value;
	var tid = document.getElementById(id+'teacherid').value;
	var ss = document.getElementById('searchstart').value;
	var se = document.getElementById('searchend').value;
	var link =encodeURI( '/admin-payroll/singleadd/?task='+task+'&table_id='+tableid+'&amount='+amt+'&teacher_id='+tid+'&when_paid='+wp+'&when_happened='+wh+'&searchstart='+ss+'&searchend='+se);
	//console.log(link);
	window.location.href = link;
	return false;
}

</script>
<div class='row'><div class='col-md-10'>
	
<h2>Payroll</h2>
<form action='/admin-payroll/view/' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Search'); ?>
</form>

<?php

$weeknav = "<p><a href='/admin-payroll/view/?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='/admin-payroll/view/'>this week</a> | <a href='/admin-payroll/view/?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;

// list payroll items with delete button
echo "<h2 class='mt-4'>Payroll Items (Claimed)</h2>\n";
if (count($payrolls) ==0) {
	echo "<p>None!</p>\n";
}

$last_when_paid = null;
$last_teacher_name = null;
$pay_teacher_total = null;
$pay_date_total = null;
$pay_grand_total = null;
	
	
function total_row($name, $total, &$guts) {
	echo "<div class='row border-top mt-1'><div class='col-6'>Total for {$name}:</div><div class='col'><b>$total</b></div></div>\n";
	
	if ($guts) {
		echo \Wbhkit\textarea($name.'ta', $guts, 0);
	}
	$guts = null;
	
}
function teacher_header($name) {
	echo "<h4 class='mt-3'>{$name}</h4>\n";
	
}

$guts = null; // cut and paste version of teacher pay items
foreach ($payrolls as $p) {
	
	//break it down by date
	//within date, break it down by teacher
	//show it in a way that doesn't take a ton of room
	// show session number (which means you have to get it from db)
	// are we getting primary id from payrolls?
	
	
	if ($p->fields['when_paid'] != $last_when_paid) {
		if ($last_when_paid != 0) {
			total_row($last_teacher_name, $pay_teacher_total, $guts);
			$pay_teacher_total = 0;
			total_row(date('j-M-Y', strtotime($last_when_paid)), $pay_date_total, $guts);
			$pay_date_total = 0;
		}
		echo "<h3 class='mt-2'>".date('j-M-Y', strtotime($p->fields['when_paid']))."</h3>";
		echo teacher_header($p->fields['teacher_name']);
		
	} elseif ($p->fields['teacher_name'] != $last_teacher_name) {
		if ($last_teacher_name) {
			total_row($last_teacher_name, $pay_teacher_total, $guts);
			$pay_teacher_total = 0;
		}
		echo teacher_header($p->fields['teacher_name']);
	}
	echo "<div class='row'>\n";
	
	$guts .= "{$p->fields['title']} (".date('D M j ga', strtotime($p->fields['start'])).' #'.($p->fields['rank'] ? $p->fields['rank'] : 'show').") {$p->fields['amount']}\n";
	
	echo "<div class='col-6'>{$p->fields['title']} <small>({$p->fields['workshop_id']}) (".date('D M j ga', strtotime($p->fields['start'])).' #'.($p->fields['rank'] ? $p->fields['rank'] : 'show').")</small></div>";
	echo "<div class='col'>{$p->fields['amount']} <span class='ml-3'><small>(<a href='/admin-payroll/del/?pid={$p->fields['id']}&searchstart=$searchstart&searchend=$searchend'>delete</a>)</small></span></div>";
	echo "</div>\n";

	$pay_teacher_total += $p->fields['amount'];
	$pay_date_total += $p->fields['amount'];
	$pay_grand_total += $p->fields['amount'];
	$last_teacher_name = $p->fields['teacher_name'];
	$last_when_paid = $p->fields['when_paid'];
	
}

total_row($last_teacher_name, $pay_teacher_total, $guts);
total_row(date('j-M-Y', strtotime($last_when_paid)), $pay_date_total, $guts);
total_row('Grand Total', $pay_grand_total, $guts);

// list unclaimed items

echo "<h2 class='mt-4'>Claims</h2>\n";

$faculty  = \Teachers\get_all_teachers();
$teacher_opts = \Teachers\teachers_dropdown_array(false, $faculty);


echo "<form action='/admin-payroll/add' method='post'>\n";
echo \Wbhkit\hidden('searchstart', $searchstart);
echo \Wbhkit\hidden('searchend', $searchend);

echo "<table class='table table-striped my-3'>
	<thead><tr>
		<th>who</th>
		<th>what</th>
		<th>rev / pay</th>
		<th>when</th>
		<th>action</th>
	</thead><tbody>";

foreach ($claims as $c) {
	
	
	foreach ($payrolls as $p) {
		if ($p->fields['task'] == $c['task'] && $p->fields['table_id'] == $c['table_id']) {
			continue(2); // already claimed
		}
	}
	
	// ONLY SHOW FIRST SESSIONS 
	if ($c['rank'] != 1) {
		continue(1); 
	}
	
	$t = \Teachers\find_teacher_in_teacher_array($c['teacher_id'], $faculty);
	
	
	$c['amount'] = $c['total_class_sessions']*$t['default_rate'] + $c['total_show_sessions']*($t['default_rate'] / 2);
		
	$id = "pd_{$c['task']}_{$c['table_id']}_";
	
	echo "<tr>\n";
	echo "<td>".\Wbhkit\drop("{$id}teacherid", $teacher_opts, $c['teacher_id'], 
	0)."</td>\n";
	echo "<td><a href='/admin-workshop/view/{$c['id']}'>{$c['title']}</a> <small>({$c['workshop_id']}) (".date('D M j ga', strtotime($c['start'])).' #'.($c['rank'] ? $c['rank'] : 'show').")</small></td>\n";
	echo "<td><small class='mx-3'>{$c['actual_revenue']}<br>".\Wbhkit\texty("{$id}amount", $c['amount'], 0)."</td>\n";
	echo "<td>".\Wbhkit\texty("{$id}whenpaid", date("j-M-Y"), 0)."</td>\n";
	echo "<td><button class='btn btn-success btn-sm' onClick=\"return single_claim('".$c['task']."', '".$c['table_id']."')\">Claim</button></td>\n";
	echo \Wbhkit\hidden("{$id}whenhappened", $c['start'], true);
	echo "</tr>\n";

}
echo "</tbody></table>\n";
echo \Wbhkit\submit("Submit All Claims");
echo "</form>\n";

echo "<button id=\"todays_date\" class=\"btn btn-success m-1\"  role=\"button\">Make Paid Dates Today</button> | <button id=\"this_date\" class=\"btn btn-success m-1\"  role=\"button\">Use This Date:</button><input type='text' class='mx-md-1' id='this_date_val' name='this_date_val'  value='".date("j-M-Y")."'>\n";

	
?>