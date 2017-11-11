<?php
namespace Emails;	
	


function confirm_email($wk, $u, $status_id = ENROLLED) {
		
	$statuses = \Lookups\get_statuses();
	if (!isset($u['key']) || !$u['key']) {
		$key = \Users\get_key($u['id']);
	} else {
		$key = $u['key'];
	}
	$e = \Enrollments\get_an_enrollment($wk, $u); 
	$drop = URL."index.php?key=$key&ac=drop&wid={$wk['id']}";
	$trans = URL."index.php?key=$key&wid={$wk['id']}";
	$accept = URL."index.php?ac=accept&wid={$wk['id']}&key=$key";
	$decline = URL."index.php?ac=decline&wid={$wk['id']}&key=$key";
	$enroll = URL."index.php?key=$key&ac=enroll&wid={$wk['id']}";
	$textpref = URL."index.php?key=$key&v=text";
	$call = '';
	$late = '';
	$textpoint = '';
	$notifications = '';
		
	if ($e['while_soldout']) { 
		$message .= '<br><br>'.get_dropping_late_warning();
	}
	
	
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['showtitle']}";
			$point = "You are ENROLLED in {$wk['showtitle']}.";
			$textpoint = $point." For more info: ";
			$call = "To DROP, click here:\n{$drop}";
			if ($wk['cost'] > 0) {
				$call .= "\n\nPay in person or venmo. On the day of the workshop is fine. Venmo link:\nhttp://venmo.com/willhines?txn=pay&share=friends&amount={$wk['cost']}&note=improv%20workshop";
			}
			$send_faq = true;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['showtitle']}";
			$point = "You are wait list spot {$e['rank']} for {$wk['showtitle']}:";
			$textpoint = $point." For more info: ";
			$call = "To DROP, click here:\n{$drop}";
			break;
		case INVITED:
			$sub = "INVITED: {$wk['showtitle']} -- PLEASE RESPOND";
			$point = "A spot opened up in ({$wk['showtitle']}.";
			$textpoint = $point." Plz ACCEPT or DECLINE here: ";
			$call = "To ACCEPT, click here:\n{$accept}\n\nTo DECLINE, click here:\n{$decline}";
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['showtitle']}";
			$point = "You have dropped out of {$wk['showtitle']}";
			$textpoint = $point." For more info: ";
			if ($e['while_soldout'] == 1) {
				$late .= "\n".get_dropping_late_warning();
			}
			$call = "If you change your mind, re-enroll here:\n{$enroll}";
			break;
		default:
			$sub = "{$statuses[$status_id]}: {$wk['showtitle']}";
			$point = "You are a status of '{$statuses[$status_id]}' for {$wk['showtitle']}";
			$textpoint = $point." Go here for more info: ";
			break;
	}

	$text = '';
	if ($u['send_text']) {
		$last_bitly = shorten_link($trans);
		$textmsg = $textpoint.' '.$last_bitly;
		send_text($u, $textmsg);
	}
	
	$notifcations = '';
	if (!$u['send_text']) {
		$notifications = "\nWould you want to be notified via text? You can set text preferences:\n".$textpref;
	}

	$body = "$point $late

$call

Full info:

Title: {$wk['title']}
Description: {$wk['notes']}
When: {$wk['when']}
Where: {$wk['place']} {$wk['lwhere']}
Cost: {$wk['cost']}

$notifications

".email_footer($send_faq);	
	
	return mail($u['email'], $sub, $body, "From: ".WEBMASTER);
}


function send_text($u, $msg) {
	if (!$u['send_text'] || !$u['carrier_id'] || !$u['phone'] || strlen($u['phone']) != 10) {
		return false;
	}
	$carriers = \Lookups\get_carriers();
	$to = $u['phone'].'@'.$carriers[$u['carrier_id']]['email'];	
	$mailed=  mail($to, '', $msg, "From: ".WEBMASTER);
	return $mailed;
}


function shorten_link($link) {
	
	// bit.ly registered token is: 5d58679014e86b8b31cd124ed31185fa799980e7
	// under whines@gmail.com / meet1962
	
	$link = preg_replace('/localhost:8888/', 'www.willhines.net', $link);
	$link = urlencode($link);
	$to_bitly = "https://api-ssl.bitly.com/v3/shorten?access_token=5d58679014e86b8b31cd124ed31185fa799980e7&longUrl={$link}&format=txt";

	//$response = file_get_contents($to_bitly); // would rather do this than curl but i can't get it to work

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $to_bitly);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // bad, hard to fix
   	$response = curl_exec($ch);
	curl_close($ch);	
	
	//echo "Response: $response<br>\n";
	return $response;
	
}

function get_dropping_late_warning() {
	global $late_hours;
	return "NOTE: You are dropping within {$late_hours} hours of the start, and there was a waiting list. Is there a way you could find someone to take your spot?";
	
}




function email_footer($faq = false) {

	$faqadd = '';
	if ($faq) {
		$faqadd = strip_tags(get_faq());
	}
	return "
$faqadd
		
Thanks!
		
-Will Hines
HQ: 1948 Hillhurst Ave. Los Angeles, CA 90027
";
}

function get_faq() {
	
return "<h2>Questions</h2>
<dl>
<dt>Can I drop out?</dt>
<dd>Yes, use the link in your confirmation email to go to the web site, where you can drop out.</dd>

<dt>If there is a cost, how should I pay?</dt>
<dd>In cash, at the practice. Or Venmo it to Will Hines (whines@gmail.com)
Venmo link: <a href='http://venmo.com/willhines?txn=pay&share=friends&note=improv%20workshop'>http://venmo.com/willhines?txn=pay&share=friends&note=improv%20workshop</a></dd>

<dt>What if I'm on a waiting list?</dt>
<dd>You'll get an email the moment a spot opens up, with a link to ACCEPT or DECLINE.</dd>

<dt>What's the late policy? Or the policy on leaving early?</dt>
<dd>Arriving late or leaving early is fine. If you're late I might ask you to wait to join in until I say so.</dd>

<dt>What levels?</dt>
<dd>Anyone can sign up. The description may recommend a level but I won't enforce it.</dd>
</dl>";
}	
	
?>