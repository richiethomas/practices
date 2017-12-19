<?php
namespace Emails;	

require_once 'Mail.php';
//require_once 'Mail/mime.php';	


// Mail_mime did weird things to encoding of mail
// turned '=' into '=3D' and other things
function centralized_email($to, $sub, $body) {
		
	// connect to SMTP
	$params = array();
	$params["host"] = "mail.willhines.net";
	$params["port"] = '26';
	$params["auth"] = "PLAIN";
	$params["username"] = 'will@willhines.net';
	$params["password"] = EMAIL_PASSWORD;
	$smtp = \Mail::factory('smtp', $params);
	
	$crlf = "\n";
	 $headers = array(
                     'From'       => WEBMASTER,
                     'Reply-To'   => WEBMASTER,
					 'Return-Path' => WEBMASTER,
                     'Subject'       => $sub,
					 'To'			=> $to);

	  $headers['MIME-Version'] = "1.0";
	  $headers['Content-Type'] = "text/html";
	  $headers['charset'] = "iso-8859-1";

	 $sent = $smtp->send($to, $headers, $body);
	
	$ts = date("Y-m-d H:i:s").' ';
	$mail_activity = "emailed '$to', '$sub'\n";
	if ($sent) {
		$return = true;
		if (DEBUG_MODE) {
			file_put_contents(MAIL_LOG, $ts.$mail_activity, FILE_APPEND | LOCK_EX);
		}
	} else {
		$error = error_get_last();
		$return = "Error type '{$error['type']}': '{$error['message']}' in file '{$error['file']}', line '{$error['line']}'. Attempted: $mail_activity\n";
		if (DEBUG_MODE) {
			file_put_contents(MAIL_LOG, $ts.$return, FILE_APPEND | LOCK_EX);
		}
	}
	return $return;
}

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
	$textpref = URL."index.php?key=$key";
	$call = '';
	$late = '';
	$textpoint = '';
	$notifications = '';
		
	if ($e['while_soldout']) { 
		$message .= '<br><br>'.get_dropping_late_warning();
	}
	
	
	$send_faq = false;
	$email_markup = null;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['showtitle']}";
			$point = "You are ENROLLED in {$wk['showtitle']}.";
			$textpoint = $point." ";
			$call = "To DROP, click here:\n{$drop}";
			if ($wk['cost'] > 0) {
				$call .= "<br><br>Pay in person or venmo. On the day of the workshop is fine.<br>Venmo link:\nhttp://venmo.com/willhines?txn=pay&share=friends&amount={$wk['cost']}&note=improv%20workshop";
			}
			$send_faq = false;
			$email_markup = set_email_markup($e, $wk, $u);
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['showtitle']}";
			$point = "You are wait list spot {$e['rank']} for {$wk['showtitle']}:";
			$textpoint = $point." ";
			$call = "To DROP, click here:\n{$drop}";
			break;
		case INVITED:
			$sub = "INVITED: {$wk['showtitle']} -- PLEASE RESPOND";
			$point = "A spot opened up in ({$wk['showtitle']}.";
			$textpoint = $point." ACCEPT or DECLINE: ";
			$call = "To ACCEPT, click here:\n{$accept}\n\nTo DECLINE, click here:\n{$decline}";
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['showtitle']}";
			$point = "You have dropped out of {$wk['showtitle']}";
			$textpoint = $point." ";
			if ($e['while_soldout'] == 1) {
				$late .= "<br><i>".get_dropping_late_warning()."</i>";
			}
			$call = "If you change your mind, re-enroll here:\n{$enroll}";
			$email_markup = set_email_markup($e, $wk, $u, true); // cancel event
			break;
		default:
			$sub = "{$statuses[$status_id]}: {$wk['showtitle']}";
			$point = "You are a status of '{$statuses[$status_id]}' for {$wk['showtitle']}";
			$textpoint = $point." ";
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
		$notifications = "<p>Would you want to be notified via text? You can set text preferences:<br>".$textpref."</p>";
	}

	$body = "$email_markup
<p>$point $late</p>

<p>$call</p>

<p>Full info:</p>

<p><b>Title:</b> {$wk['title']}<br>
<b>When:</b> {$wk['when']}<br>
<b>Where:</b> {$wk['place']} {$wk['lwhere']}<br>
<b>Cost:</b> {$wk['cost']}</p>
<b>Description:</b> {$wk['notes']}</p>

$notifications

".email_footer($send_faq);	
	
	return centralized_email($u['email'], $sub, $body);
}


function set_email_markup($e, $wk, $u, $cancel = false) {

	if ($cancel) {
		$status = "http://schema.org/ReservationCancelled";
	} else {
		$status = "http://schema.org/ReservationConfirmed";
	}

 return "<script type=\"application/ld+json\">
 {
   \"@context\": \"http://schema.org\",
   \"@type\": \"EventReservation\",
   \"reservationNumber\": \"wbhwk{$e['id']}\",
    \"reservationStatus\": \"{$status}\",
   \"underName\": {
     \"@type\": \"Person\",
     \"name\": \"{$u['nice_name']}\"
   },
   \"reservationFor\": {
     \"@type\": \"EducationEvent\",
     \"name\": \"{$wk['title']}\",
     \"startDate\": \"{$wk['start']}\",
 	 \"endDate\": \"{$wk['end']}\",
     \"performer\": {
          \"@type\": \"Person\",
          \"name\": \"Will Hines\",
          \"image\": \"http://willhines.net/home_files/wh_clay_med.jpg\"
        },
     \"location\": {
       \"@type\": \"Place\",
       \"name\": \"{$wk['place']}\",
       \"address\": {
         \"@type\": \"PostalAddress\",
         \"streetAddress\": \"{$wk['address']}\",
         \"addressLocality\": \"{$wk['city']}\",
         \"addressRegion\": \"{$wk['state']}\",
         \"postalCode\": \"{$wk['zip']}\",
         \"addressCountry\": \"US\"
       }
     }
   },
    \"ticketNumber\": \"{$e['rank']}\",
    \"numSeats\": \"1\",
   \"modifiedTime\": \"".date("Y-m-d H:i:s")."\",
   \"modifyReservationUrl\": \"http://willhines.net/practices/index.php?wid={$wk['id']}\"
 }
 </script>";
	
}


function send_text($u, $msg) {
	if (!$u['send_text'] || !$u['carrier_id'] || !$u['phone'] || strlen($u['phone']) != 10) {
		return false;
	}
	
	$carriers = \Lookups\get_carriers();
	$to = $u['phone'].'@'.$carriers[$u['carrier_id']]['email'];	
	$mail_status = centralized_email($to, '', $msg);
	//echo "<pre>".print_r($u, true)."<br>to: $to<br>mail status: $mail_status<br>msg: $msg</pre>\n";
	return $mail_status;
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
	
	return $response;
	
}



function get_dropping_late_warning() {
	global $late_hours;
	return "NOTE: You are dropping within {$late_hours} hours of the start, and there was a waiting list. Is there a way you could find someone to take your spot?";
	
}




function email_footer($faq = false) {

	$faqadd =  $faq ? get_faq() : '';

	return "
$faqadd

<p>Thanks!</p>

<p>-Will Hines<br>
HQ: 1948 Hillhurst Ave. Los Angeles, CA 90027</p>";
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