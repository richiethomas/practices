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
	var uid = document.getElementById(id+'userid').value;
	var ss = document.getElementById('searchstart').value;
	var se = document.getElementById('searchend').value;
	var link =encodeURI( '/admin-payroll/singleadd/?task='+task+'&table_id='+tableid+'&amount='+amt+'&user_id='+uid+'&when_paid='+wp+'&when_happened='+wh+'&searchstart='+ss+'&searchend='+se);
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
$last_user_name = null;
$pay_user_total = null;
$pay_date_total = null;
$pay_grand_total = null;
	
	
function total_row($name, $total, &$guts) {
	echo "<div class='row border-top mt-1'><div class='col-6'>Total for {$name}:</div><div class='col'><b>$total</b></div></div>\n";
	
	if ($guts) {
		echo \Wbhkit\textarea($name.'ta', $guts, 0);
	}
	$guts = null;
	
}
function user_header($name) {
	echo "<h4 class='mt-3'>{$name}</h4>\n";
	
}

$guts = null; // cut and paste version of teacher pay items
foreach ($payrolls as $p) {
	
	
	if ($p->fields['when_paid'] != $last_when_paid) {
		if ($last_when_paid != 0) {
			total_row($last_user_name, $pay_user_total, $guts);
			$pay_user_total = 0;
			total_row(date('j-M-Y', strtotime($last_when_paid)), $pay_date_total, $guts);
			$pay_date_total = 0;
		}
		echo "<h3 class='mt-2'>".date('j-M-Y', strtotime($p->fields['when_paid']))."</h3>";
		echo user_header($p->fields['user_name']);
		
	} elseif ($p->fields['user_name'] != $last_user_name) {
		if ($last_user_name) {
			total_row($last_user_name, $pay_user_total, $guts);
			$pay_user_total = 0;
		}
		echo user_header($p->fields['user_name']);
	}
	echo "<div class='row'>\n";
	
	
	if ($p->fields['task'] == 'workshop' || $p->fields['task'] == 'class') {
				
		$guts .= "{$p->wk['title']} (".date('D M j ga', strtotime($p->fields['when_happened'])).' #'.($p->wk['rank'] ? $p->wk['rank'] : 'show').") {$p->fields['amount']}\n";
	
		echo "<div class='col-6'>{$p->wk['title']} <small>({$p->wk['id']}) (".date('D M j ga', strtotime($p->wk['start'])).' #'.($p->wk['rank'] ? $p->wk['rank'] : 'show').")</small></div>";
		
	} elseif ($p->fields['task'] == 'task') {
		
		$guts .= "{$p->task->fields['title']} (".date('D M j ga', strtotime($p->fields['when_happened'])). ") {$p->fields['amount']}\n";
	
		echo "<div class='col-6'>{$p->task->fields['title']} <small>(".date('D M j ga', strtotime($p->fields['when_happened'])).")</small></div>";
	}
	
	echo "<div class='col'>{$p->fields['amount']} <span class='ml-3'><small>(<a href='/admin-payroll/del/?pid={$p->fields['id']}&searchstart=$searchstart&searchend=$searchend'>delete</a>)</small></span></div>";
	echo "</div>\n";


	$pay_user_total += $p->fields['amount'];
	$pay_date_total += $p->fields['amount'];
	$pay_grand_total += $p->fields['amount'];
	$last_user_name = $p->fields['user_name'];
	$last_when_paid = $p->fields['when_paid'];
	
}

total_row($last_user_name, $pay_user_total, $guts);
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
		<th>how much</th>
		<th>when</th>
		<th>action</th>
	</thead><tbody>";
	
// claims are formatted as payroll objects 
foreach ($claims as $c) {
		
	foreach ($payrolls as $p) {
		if ($p->fields['task'] == $c->fields['task'] && $p->fields['table_id'] == $c->fields['table_id']) {
			continue(2); // already claimed
		}
	}
	
	
	if ($c->fields['task'] == 'workshop' || $c->fields['task'] == 'class') {
		
		// if class is nearly sold out, bonus rate
		// (rate should have been stored as 'amount' on this claim)
		if ($c->wk['cost'] != 1 && $c->wk['enrolled'] / $c->wk['capacity'] > .75) {
			$rate = $c->fields['amount'] + 50;
		} else {
			$rate = $c->fields['amount'];
		}
		
		$what = "<a href='/admin-workshop/view/{$c->wk['id']}'>{$c->wk['title']}</a> <small>({$c->wk['id']}) (".date('D M j ga', strtotime($c->wk['start'])).' #'.($c->wk['rank'] ? $c->wk['rank'] : 'show').")</small><br>
<small class='mx-3'>{$c->wk['actual_revenue']} ($rate)";
		
		
		$c->fields['amount'] = 
			$c->wk['total_class_sessions']*$rate + 
			$c->wk['total_show_sessions']*($rate / 2);
		
		
	} else {
		
		$what = $c->task->fields['title'];
		
	}
	
	
	

		
	$id = "pd_{$c->fields['task']}_{$c->fields['table_id']}_";
	
	echo "<tr>\n";
	echo "<td>{$c->fields['user_name']}".\Wbhkit\hidden("{$id}userid", $c->fields['user_id'])."</td>\n";
	echo "<td>$what</td>\n";
	echo "<td>".\Wbhkit\texty("{$id}amount", $c->fields['amount'], 0)."</td>\n";
	echo "<td>".\Wbhkit\texty("{$id}whenpaid", date("j-M-Y"), 0)."</td>\n";
	echo "<td><button class='btn btn-success btn-sm' onClick=\"return single_claim('".$c->fields['task']."', '".$c->fields['table_id']."')\">Claim</button></td>\n";
	echo \Wbhkit\hidden("{$id}whenhappened", $c->fields['when_happened'], true);
	echo "</tr>\n";

}
echo "</tbody></table>\n";
echo \Wbhkit\submit("Submit All Claims");
echo "</form>\n";

echo "<button id=\"todays_date\" class=\"btn btn-success m-1\"  role=\"button\">Make Paid Dates Today</button> | <button id=\"this_date\" class=\"btn btn-success m-1\"  role=\"button\">Use This Date:</button><input type='text' class='mx-md-1' id='this_date_val' name='this_date_val'  value='".date("j-M-Y")."'>\n";

	
?>