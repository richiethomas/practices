<script>
	function single_claim(id) {
	var amt = document.getElementById(id+'amount').value;
	var wp = document.getElementById(id+'whenpaid').value;
	var wh = document.getElementById(id+'whenhappened').value;	
	var wid = document.getElementById(id+'wid').value;	
	var uid = document.getElementById(id+'uid').value;	
	var ss = document.getElementById('searchstart').value;
	var se = document.getElementById('searchend').value;
	
	var link =encodeURI( '/admin-payments/singlecourse/?wid='+wid+'&amt='+amt+'&uid='+uid+'&wp='+wp+'&wh='+wh+'&searchstart='+ss+'&searchend='+se);
	//console.log(link);
	window.location.href = link;
	return false;
}

</script>
<div class='row'><div class='col-md-12 m-1'>
	
<h2><a href='/admin-payments/view'>Payments</a></h2>
<form action='/admin-payments/view/' method='post'>
<?php echo \Wbhkit\texty('searchstart', $searchstart, 'Search Start'); ?>
<?php echo \Wbhkit\texty('searchend', $searchend, 'Search End'); ?>
<?php echo \Wbhkit\submit('Search'); ?>
</form>

<?php

$weeknav = "<p><a href='/admin-payment/view/?searchstart=$lastweekstart&searchend=$lastweekend'>last week</a> | <a href='/admin-payment/view/'>this week</a> | <a href='/admin-payment/view/?searchstart=$nextweekstart&searchend=$nextweekend'>next week</a></p>\n";
echo $weeknav;

// list payroll items with delete button
echo "<h2 class='mt-4'>Payment Items (Claimed)</h2>\n";
if (count($payments) ==0) {
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
foreach ($payments as $p) {
	
	
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
	
	
	if ($p->fields['workshop_id']) {
		
		$wk_date = date('D M j ga', strtotime($p->wk->fields['start']));
				
		$guts .= "{$p->fields['title']} - {$p->wk->fields['title']} ($wk_date) - {$p->fields['amount']}\n";
	
		echo "<div class='col-6'>{$p->fields['title']} - <a href='/admin-workshop/view/{$p->fields['workshop_id']}'>{$p->wk->fields['title']}</a> <small>($wk_date)</small></div>";
		
	} else {
		
		$guts .= "{$p->fields['title']} (".date('D M j ga', strtotime($p->fields['when_happened'])). ") {$p->fields['amount']}\n";
	
		echo "<div class='col-6'>{$p->fields['title']} <small>(".date('D M j ga', strtotime($p->fields['when_happened'])).")</small></div>";
	}
	
	echo "<div class='col'>{$p->fields['amount']} <span class='ml-3'><small>(<a href='/admin-payments/del/?pid={$p->fields['id']}&searchstart=$searchstart&searchend=$searchend'>delete</a>)</small></span></div>";
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

echo "<h2 class='mt-4'>Unpaid Classes</h2>\n";

$faculty  = \Teachers\get_all_teachers();
$teacher_opts = \Teachers\teachers_dropdown_array(false, $faculty);


echo "<form action='/admin-payments/allcourses' method='post'>\n";
echo \Wbhkit\hidden('searchstart', $searchstart);
echo \Wbhkit\hidden('searchend', $searchend);

echo "<table class='table table-striped my-3'>
	<thead><tr>
		<th>who</th>
		<th>what / class</th>
		<th>how much</th>
		<th>paid</th>
		<th>action</th>
	</thead><tbody>";
	
// claims are class payments we think need to be made 
$claim_id = 0;
foreach ($claims as $c) {
		
	foreach ($payments as $p) {
		if (
			$p->fields['title'] == $c->fields['title'] && 
			$p->fields['workshop_id'] == $c->fields['workshop_id'] &&
			$p->fields['user_id'] == $c->fields['user_id']
			) {
			continue(2); // already claimed
		}
	}
		
	$what = $revenue = '';
	if ($c->fields['title'] == TEACHERPAY) {
		
		// $rate = $c->fields['amount']; 		
		if ($c->wk->fields['cost'] / $c->wk->fields['total_sessions'] <= 30) {
			$rate = 170;
		} else {
			$rate = 200;
		}
		
		$what = "<a href='/admin-workshop/view/{$c->wk->fields['id']}'>{$c->wk->fields['title']}</a> <small>(".\Wbhkit\figure_year_minutes(strtotime($c->wk->fields['start'])).")</small>";
		$revenue = "<small class='mx-3'>{$c->wk->fields['actual_revenue']} ($rate)</small>";
		
		$c->fields['amount'] = 
			$c->wk->fields['total_class_sessions']*$rate + 
			$c->wk->fields['total_show_sessions']*($rate / 2);
	} 
	
		
	$id = "pd{$claim_id}_";
	
	echo "<tr>\n";
	echo "<td>{$c->fields['user_name']}</td>\n";
	echo "<td>".
		\Wbhkit\hidden("{$id}whenhappened", $c->fields['when_happened'], true).
		\Wbhkit\hidden("{$id}uid", $c->fields['user_id'], true).
		\Wbhkit\hidden("{$id}wid", $c->fields['workshop_id'], true)."{$what}</td>";	
	echo "<td>".\Wbhkit\texty("{$id}amount", $c->fields['amount'], 0)."{$revenue}</td>\n";
	echo "<td>".\Wbhkit\texty("{$id}whenpaid", date("j-M-Y"), 0)."</td>\n";
	echo "<td><button class='btn btn-success btn-sm' onClick=\"return single_claim('pd{$claim_id}_')\">Claim</button></td>\n";
	echo "</tr>\n";
	
	$claim_id++;

}
echo "</tbody></table>\n";
echo \Wbhkit\submit("Submit All Claims");
echo "</form>\n";


echo "<h2 class='mt-4'>Add New Payment</h2>\n";

include 'assets/ajax/search_box.php';
echo "
<form action='/admin-payment/addsingle' method='post'>	
<table class='table'>
	<tr>
		<td>Who</td>
		<td>What</td>
		<td>How Much</td>
		<td>Happened</td>
		<td>Paid</td>
		<td>Class?</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><div class='form-group'>
		<input type='text' class='form-control' id='search-box' name='email' autocomplete='off'>
		<div id='suggesstion-box'></div>
		</div></td>
		<td>".\Wbhkit\texty("title", null, 0, null, null, 'Required', ' required', 'text')."</td>
		<td>".\Wbhkit\texty("amt", null, 0, null, null, null, null, 'number')."</td>
		<td>".\Wbhkit\texty("wh", date('d M Y'), 0, null, null, 'Required', ' required', 'text')."</td>
		<td>".\Wbhkit\texty("wp", date('d M Y'), 0, null, null, 'Required', ' required', 'text')."</td>
		<td>".\Wbhkit\drop('wid', $recent_workshops, null, 0)."</td>
		<td><button id='new_payment' class='btn btn-success' role='button'>Add Payment</button></td>
	</tr>
</table>
".\Wbhkit\hidden('searchstart', $searchstart).
\Wbhkit\hidden('searchend', $searchend)."
</form>
	
";
	
echo "\n";	
	
	
?>