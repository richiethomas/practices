<?php
namespace Emails;	

require_once 'Mail.php';
//require_once 'Mail/mime.php';	


// Mail_mime did weird things to encoding of mail
// turned '=' into '=3D' and other things
function centralized_email($to, $sub, $body) {
		
	global $logger, $lookups;	
		
	
	$crlf = "\n";
	$headers = array(
                    'From'       => WEBMASTER,
                    'Reply-To'   => WEBMASTER,
					'Return-Path' => WEBMASTER,
                    'Subject'       => $sub,
					'To'			=> $to);

	  $headers['MIME-Version'] = "1.0";
	  $headers['Content-Type'] = "text/html";
	  $headers['charset'] = "ISO-8859-1";



	  $body = wordwrap($body, 80, "<br>\n");

	  if (LOCAL) {
	  	// connect to SMTP
		$smtp = get_smtp_object();
		$to = 'whines@gmail.com'; // everything to me on local
 	 	$sent = $smtp->send($to, $headers, $body);  // laptop can use the SMTP server on willhines.net
		
 	  } else {
		  unset($headers['Subject']);
		  unset($headers['To']);
		  $stringheaders = '';
		  foreach ($headers as $key => $value) {
			  $stringheaders .= "$key: $value\r\n";
		  }
	  	 $sent = mail($to, $sub, $body, $stringheaders); // willhinesimprov.com uses local server
 	  }
	
	$ts = date("Y-m-d H:i:s").' ';
	$mail_activity = "emailed '$to', '$sub'\n";
	if ($sent) {
		$return = true;
		if (DEBUG_MODE) {
			$logger->error($mail_activity);
		} else {
			$logger->info($mail_activity);
		}
	} else {
		$error = error_get_last();
		$return = "Error type '{$error['type']}': '{$error['message']}' in file '{$error['file']}', line '{$error['line']}'. Attempted: $mail_activity\n";
		$logger->error($return);
	}
	return $return;
}

function confirm_email($e, $status_id = ENROLLED) {

	$wk = $e->wk;
	$u = $e->u;	
	$trans = URL."workshop.php?wid={$wk['id']}";
	$textpref = URL."you.php";
	
	$body = '';
	$textpoint = '';
	$notifications = '';	
		
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['title']}";
			$body = $textpoint = "You are ENROLLED in the workshop \"{$wk['title']}\".";
			$body = "<p>$body</p>";

			if ($wk['location_id'] == ONLINE_LOCATION_ID) {
				$body .= "<p>ZOOM LINK:<br>\n----------<br>\nThe Zoom link to your workshop is: {$wk['online_url']}. Try to show up 5 minutes early if you can so we can get started right away.  If your class is multiple sessions, that link should work for all of them. We'll send you an email if the link changes.</p>";
			}
			
			if ($wk['cost'] > 1) {
				$body .= "<p>PAYMENT:<br>\n--------<br>\nClass costs \${$wk['cost']} (USD). Pay via Venmo @wgimprovschool (business) or PayPal payments@wgimprovschool.com or https://paypal.me/WGImprovSchool. Due by the start of class. Confirmation email for payment can take up to 12 hours to arrive because it's triggered manually by the stubborn human who built this web site.</p>";
			}
			if ($wk['cost'] == 1) {
				$body .= "<p>PAYMENT:<br>\n--------<br>This is a PAY WHAT YOU CAN class. Full price is usually $40USD, but pay anything from zero to $40USD, whatever you like. If you are going to pay something, venmo @wgimprovschool (it's a business, not a person) or paypal payments@wgimprovschool.com or https://paypal.me/WGImprovSchool</p>";
			}
			
			

			$body .= 
"<p>CLASS INFO<br>\n----------<br>\nDescription and times/dates are both in this email and also listed here:<br>\n{$trans}</p>\n
<p>DROPPING THE CLASS<br>\n------------------<br>\nYou can drop out by going to class's web page (the link is just above this paragraph). If you're dropping within ".LATE_HOURS." of the start, please pay anyway.</p>
<p>SHOWS AND JAMS<br>\n
------------------<br>\n
There are online shows and jams that you can play in, if you wish! See the shows/jams page on the web site for more info:<br>\n
http://www.wgimprovschool.com/shows</p>";

			$send_faq = false;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['title']}";
			$body = $textpoint = "You are on the WAIT LIST for \"{$wk['title']}\", spot {$e->fields['rank']}";
			$body = "<p>$body</p>";
			
			
			$body .= "<p>WHAT DOES 'WAIT LIST' MEAN?<br>
---------------------------<br>
It means if someone drops the class, you'll get an email inviting you to join. At that point, you can accept or decline the spot.</p>

DROPPING OUT:<br>
------------------<br>
If you know you're not going to want a spot, you can drop the class on the web site here: <br>
{$trans}<br><br>
That way if a spot opens up, we won't be waiting for you to tell us you don't want the spot.</p>";
			break;
			
		case INVITED:
			$sub = "INVITED: {$wk['title']} -- PLEASE RESPOND";
			$body = $textpoint = "A spot opened in ({$wk['title']}. Want it?";
			$body = "<p>$body</p>";
			
			$body .= "<p>DO YOU WANT THE SPOT? - PLEASE CLICK AND ANSWER<br>
----------------------<br>
Please GO TO THIS LINK where you can click ACCEPT OR DECLINE the spot.<br>
{$trans}<br
Others might be waiting for the spot if you don't want it.</p>";

			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['title']}";
			$body = $textpoint = "You have DROPPED out of {$wk['title']}";
			$body = "<p>$body</p>";
			
			if ($e->fields['while_soldout'] == 1) {
				$body .= "<br><i>".get_dropping_late_warning()."</i>";
			}
			$body .= "If you change your mind, re-enroll here:\n{$trans}";
			
			// tell webmaster if this person needs a refund
			if ($e->fields['paid'] == 1) {
				centralized_email(WEBMASTER, "refund requested", "<p>{$u->fields['nice_name']} just dropped from the class '{$wk['title']}', and had already paid</p><p>See workshop info: ".URL."admin_edit2.php?wid={$wk['id']}</p>");
			}
			
			break;
		default:
			$statuses = $lookups->statuses;
			$sub = "{$statuses[$status_id]}: {$wk['title']}";
			$body = $textpoint = "You have a status of '{$statuses[$status_id]}' for {$wk['title']}";
			$body = "<p>$body</p>";
			
			break;
	}

	$text = '';
	if ($u->fields['send_text']) {
		$last_bitly = shorten_link($trans);
		$textmsg = $textpoint.' '.$last_bitly;
		send_text($u, $textmsg);
	}
	
	


	$body .= "<p>CLASS INFORMATION<br>
--------------------------------<br>
<b>Title:</b> {$wk['title']}<br>
<b>Teacher:</b> {$wk['teacher_info']['nice_name']}".($wk['co_teacher_id'] ?  ", {$wk['co_teacher_info']['nice_name']}" : '')."<br>
<b>When:</b> {$wk['full_when']} (".TIMEZONE." - California time)<br>
<b>Cost:</b> {$wk['costdisplay']}<br>".
($status_id == ENROLLED ? "<b>Zoom link:</b> {$wk['online_url']}" : "<b>Zoom link</b>: We'll email you the zoom link if/once you are enrolled.")."<br>
<b>Description:</b> {$wk['notes']}</p>
<p>Web page for this class:<br>\n{$trans}</p>";	

if (!$u->fields['send_text']) {
	$body .= "<p>Would you want to be notified via text? You can set text preferences:<br>".$textpref."</p>";
}

	return centralized_email($u->fields['email'], "{$sub}", $body);
}


function send_text($u, $msg) {
	
	global $lookups;
	
	if (!$u->fields['send_text'] || !$u->fields['carrier_id'] || !$u->fields['phone'] || strlen($u->fields['phone']) != 10 || (!trim($msg))) {
		return false;
	}
	
	$carriers = $lookups->carriers;
	$to = $u->fields['phone'].'@'.$carriers[$u->fields['carrier_id']]['email'];	
	$mail_status = centralized_email($to, '', $msg);
	//echo "<pre>".print_r($u, true)."<br>to: $to<br>mail status: $mail_status<br>msg: $msg</pre>\n";
	return $mail_status;
}


function shorten_link($link) {
	
	// bit.ly registered token is: 5d58679014e86b8b31cd124ed31185fa799980e7
	// under whines@gmail.com / meet1962
	
	$link = preg_replace('/localhost:8888/', 'www.willhinesimprov.com', $link);
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
	return "NOTE: You are dropping within ".LATE_HOURS." hours of the start. Please still pay for it! If someone takes your spot and pays, you'll be refunded. Questions to payments@wgimprovschool.com";
	
}




function email_footer($faq = false) {

	$faqadd =  $faq ? get_faq() : '';

	return "
$faqadd

<p>Thanks!</p>

<p>World's Greatest Improv School<br>
<a href='http://www.wgimprovschool.com/'>http://www.wgimprovschool.com/</a></p>";
}

function get_faq() {
	
return "<h2>Some Things To Know</h2>
<dl>
<dt>How does online work?</dt>
<dd>You need the Zoom app, which is free. On the day of the workshop, or maybe the day before you'll get a link to a Zoom meeting.<br>
Zoom available at: http://www.zoom.us/</dd>

<dt>Can I drop out?</dt>
<dd>Yes, use the link in your confirmation email to go to the web site, where you can drop out. If you drop within ".LATE_HOURS." of the start of class, you must still pay for your spot.</dd>

<dt>How should I pay?</dt>
<dd>Venmo @wgimprovschool, or paypal payments@wgimprovschool.com.</dd>

<dt>What if I'm on a waiting list?</dt>
<dd>You'll get an email the moment a spot opens up, with a link to ACCEPT or DECLINE.</dd>

<dt>What's the late policy? Or the policy on leaving early?</dt>
<dd>Arriving late or leaving early is fine. If you're late I might ask you to wait to join in until I say so.</dd>

<dt>What levels?</dt>
<dd>Each workshop/course has a reccomended pre-requiste. But I won't really check. Take the ones you think you can contribute to and get something from.</dd>
</dl>";
}	

function get_workshop_summary($wk) {
	
		return "<br>
<p>-----------------------------<br>
<b>Class information:</b><br>
<b>Title:</b> {$wk['title']}<br>
<b>Teacher:</b> {$wk['teacher_info']['nice_name']}".($wk['co_teacher_id'] ?  ", {$wk['co_teacher_info']['nice_name']}" : '')."<br>
<b>When:</b> {$wk['full_when']} (".TIMEZONE." - California time)";

}




function get_smtp_object() {
	global $smtp;
	
	if (isset($smtp) && is_object($smtp)) {
		return $smtp;
	}
	
	$params = array();
	if (LOCAL) {
		$params["host"] = "mail.willhines.net";
		$params["port"] = '26';
		$params["auth"] = "PLAIN";
		$params["username"] = 'will@willhines.net';
		$params["password"] = EMAIL_PASSWORD_LOCAL;
	} else { // out of date
		$params["host"] = "ssl://premium44.web-hosting.com";
		$params["port"] = '465';
		$params["auth"] = "PLAIN";
		$params["username"] = 'will@willhinesimprov.com';
		$params["password"] = EMAIL_PASSWORD_PRODUCTION;
	}
	$smtp = \Mail::factory('smtp', $params); // should now be set globally
	return $smtp;
	
}
