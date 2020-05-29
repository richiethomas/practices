<?php
namespace Emails;	

require_once 'Mail.php';
//require_once 'Mail/mime.php';	


// Mail_mime did weird things to encoding of mail
// turned '=' into '=3D' and other things
function centralized_email($to, $sub, $body) {
		
	global $logger;	
		
	// connect to SMTP
	$smtp = get_smtp_object();
	
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

  	 $sent = $smtp->send($to, $headers, $body);  // laptop can use the SMTP server on willhines.net
	 /*
	  if (LOCAL) {
 	  } else {
		  unset($headers['Subject']);
		  unset($headers['To']);
	  	 $sent = mail($to, $sub, $body, $headers); // willhinesimprov.com uses local server
 	  }
	 */
	
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

function confirm_email($wk, $u, $status_id = ENROLLED) {
		
	$statuses = \Lookups\get_statuses();
	if (!isset($u['key']) || !$u['key']) {
		$key = \Users\get_key($u['id']);
	} else {
		$key = $u['key'];
	}
	$e = \Enrollments\get_an_enrollment($wk, $u); 
	$drop = URL."workshop.php?key=$key&ac=drop&wid={$wk['id']}";
	$trans = URL."workshop.php?key=$key&wid={$wk['id']}";
	$accept = URL."workshop.php?ac=accept&wid={$wk['id']}&key=$key";
	$decline = URL."workshop.php?ac=decline&wid={$wk['id']}&key=$key";
	$enroll = URL."workshop.php?key=$key&ac=enroll&wid={$wk['id']}";
	$textpref = URL."you.php?key=$key";
	$call = '';
	$late = '';
	$textpoint = '';
	$notifications = '';	
	
	//multiple sessions?
	$wk['when'] = \XtraSessions\add_sessions_to_when($wk['when'], $wk['sessions']);	
	
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['title']}";
			$point = "You are ENROLLED in the workshop \"{$wk['title']}\".";
			$textpoint = $point." ";

			if ($wk['location_id'] == ONLINE_LOCATION_ID) {
				$point .= "<p>The Zoom link to your workshop is: {$wk['online_url']}. That should start working about five minutes before your class starts.</p>";
			}
			
			if ($wk['cost'] > 0) {
				$point .= "<p>Please pay now! Send \${$wk['cost']} (USD) via Venmo @willhines or PayPal whines@gmail.com</p>";
			}

			$call = "To DROP, click here:\n{$drop}<br>Since you are enrolled, if you drop within ".LATE_HOURS." hours of the start, you must still pay for your spot. Before that, full refund available.";

			$send_faq = false;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['title']}";
			$point = "You are WAIT LIST spot {$e['rank']} for \"{$wk['title']}\".";
			$textpoint = $point." ";
			$call = "To DROP, click here:\n{$drop}";
			break;
		case INVITED:
			$sub = "INVITED: {$wk['title']} -- PLEASE RESPOND";
			$point = "You are INVITED to enroll in ({$wk['title']}.";
			$textpoint = $point." ACCEPT or DECLINE: ";
			$call = "To ACCEPT, click here:\n{$accept}<br><br>To DECLINE, click here:\n{$decline}";
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['title']}";
			$point = "You have DROPPED out of {$wk['title']}";
			$textpoint = $point." ";
			if ($e['while_soldout'] == 1) {
				$late .= "<br><i>".get_dropping_late_warning()."</i>";
			}
			$call = "If you change your mind, re-enroll here:\n{$enroll}";
			break;
		default:
			$sub = "{$statuses[$status_id]}: {$wk['title']}";
			$point = "You are a status of '{$statuses[$status_id]}' for {$wk['title']}";
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


	$body = "<p>$point $late</p>

<p>$call</p>

<p>Summary of class infomation:<br>
--------------------------------<br>
<b>Title:</b> {$wk['title']}<br>
<b>When:</b> {$wk['when']} (PDT - California time)<br>
<b>Cost:</b> \${$wk['cost']} USD<br>".
($status_id == ENROLLED ? "<b>Zoom link:</b> {$wk['online_url']}" : "<b>Zoom link</b>: We'll email you the zoom link if/once you are enrolled.")."<br>
<b>Description:</b> {$wk['notes']}</p>

$notifications

";	

	return centralized_email($u['email'], "{$sub}", $body);
}


function send_text($u, $msg) {
	if (!$u['send_text'] || !$u['carrier_id'] || !$u['phone'] || strlen($u['phone']) != 10 || (!trim($msg))) {
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
	return "NOTE: You are dropping within ".LATE_HOURS." hours of the start. Please still pay for your spot!";
	
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
	
return "<h2>Some Things To Know</h2>
<dl>
<dt>How does online work?</dt>
<dd>You need the Zoom app, which is free. On the day of the workshop, or maybe the day before you'll get a link to a Zoom meeting.<br>
Zoom available at: http://www.zoom.us/</dd>

<dt>Can I drop out?</dt>
<dd>Yes, use the link in your confirmation email to go to the web site, where you can drop out. If you drop within ".LATE_HOURS." of the start of class, you must still pay for your spot.</dd>

<dt>How should I pay?</dt>
<dd>Venmo @willhines, or paypal whines@gmail.com.</dd>

<dt>What if I'm on a waiting list?</dt>
<dd>You'll get an email the moment a spot opens up, with a link to ACCEPT or DECLINE.</dd>

<dt>What's the late policy? Or the policy on leaving early?</dt>
<dd>Arriving late or leaving early is fine. If you're late I might ask you to wait to join in until I say so.</dd>

<dt>What levels?</dt>
<dd>Each workshop/course has a reccomended pre-requiste. But I won't really check. Take the ones you think you can contribute to and get something from.</dd>
</dl>";
}	


function get_reminder_message_data($wk) {
	
	$subject = "REMINDER: {$wk['title']} {$wk['nextstart']}";
	if ($wk['location_id'] != ONLINE_LOCATION_ID) {
		$subject .= " at {$wk['place']}";	
	}
	$note = "Greetings. You're enrolled in this workshop. ";
	$note .= "It starts ".\TimeDifference\nicetime($wk['nextstart_raw']).". ";
	if ($wk['location_id'] == ONLINE_LOCATION_ID && $wk['online_url']) {
		$note .= "<p>Here's the link: {$wk['online_url']}</p>\n"; 
	}			
	$sms = "Reminder: {$wk['title']} workshop, {$wk['nextstart']}, ".URL;
	
	return array(
	 'subject' => $subject,
	 'note' => $note,
	 'sms' => $sms	
	);
	
}



function get_workshop_summary($wk) {
		return "<br>
<p>-----------------------------<br>
<b>Class information:</b><br>
<b>Title:</b> {$wk['title']}<br>
<b>When:</b> {$wk['when']} (California time)";

}

function remind_enrolled($wk) {
	$reminder = get_reminder_message_data($wk);
	$subject = $reminder['subject'];
	$note = $reminder['note'];
	$sms = $reminder['sms'];
	
	$stds = \Enrollments\get_students($wk['id'], ENROLLED);

	$base_msg =	$note.get_workshop_summary($wk);

	foreach ($stds as $std) {
		$key = \Users\get_key($std['id']);
		$trans = URL."index.php?key=$key";
		$msg = $base_msg."<p>Log in or drop out here:<br>$trans</p>\n";
		
		//admin_log("reminder for {$std['email']} -- $subject");
		
		if (!LOCAL) {
			\Emails\centralized_email($std['email'], $subject, $msg);
			\Emails\send_text($std, $sms); // routine will check if they want texts and have proper info
			//\Emails\centralized_email('whines@gmail.com', $subject, $msg); // for testing, i get everything
		}
	
	}
}

function check_reminder() {
	
	/*
delete from reminder_checks;
update workshops set reminder_sent = 0;
update xtra_sessions set reminder_sent = 0;
	*/

	// check reminder database -- has it been 6 hours?
	$stmt = \DB\pdo_query("select * from reminder_checks order by id desc limit 1"); // most recent check
	while ($row = $stmt->fetch()) {
		//admin_log("<p class='m-3'>hours since reminder check: ".((time() - strtotime($row['time_checked'])) / 3600))."</p>";
		if ((time() - strtotime($row['time_checked'])) / 3600 <= 6) { return false; } // checked less than six hours ago
		 
	}
	
	// if yes, get a list of all workshops that have yet to start within REMINDER_HOURS
	$workshops_to_remind = array();
	$mysqlnow = date("Y-m-d H:i:s");
	
	//admin_log("select id, start from workshops where start > '$mysqlnow' and reminder_sent = 0");
	$stmt = \DB\pdo_query("select id, start from workshops where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$workshops_to_remind[] = array($row['id'], 0); // first number id, second number xtra_session row id (0 if not an xtra session)
		}
	}
	
	//admin_log("select id, workshop_id, start from xtra_sessions where start > '$mysqlnow' and reminder_sent = 0");
	$stmt = \DB\pdo_query("select id, workshop_id, start from xtra_sessions where start > :now and reminder_sent = 0", array(':now' => $mysqlnow)); // workshops in the future
	while ($row = $stmt->fetch()) {
		if ((strtotime($row['start']) - time()) / 3600 < REMINDER_HOURS) {
			$workshops_to_remind[] = array($row['workshop_id'], $row['id']); // first number id, second number xtra_session row id (0 if not an xtra session)
		}
	}
	
	// go through each workshop thast start in that windew, and send remidners
	$wk = array();
	foreach ($workshops_to_remind as $wk_id_info) {
		$wk = \Workshops\get_workshop_info($wk_id_info[0]);
		remind_enrolled($wk);
		if ($wk_id_info[1] > 0) {
			$stmt = \DB\pdo_query("update xtra_sessions set reminder_sent = 1 where id = :id", array(':id' => $wk_id_info[1])); // most recent check
		} else {
			$stmt = \DB\pdo_query("update workshops set reminder_sent = 1 where id = :id", array(':id' => $wk_id_info[0])); // most recent check
		}
	}
	
	// add a row to reminder check
	$stmt = \DB\pdo_query("insert into reminder_checks (time_checked, reminders_sent) VALUES (:now, :rsent)", array(':now' => $mysqlnow, ':rsent' => count($workshops_to_remind))); // most recent check

}


function admin_log($st) {
	global $sc;
	if (isset($sc) && strpos($sc,'admin') !== false) {
		echo "$st<br>\n";
	}
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
	} else {
		$params["host"] = "ssl://premium44.web-hosting.com";
		$params["port"] = '465';
		$params["auth"] = "PLAIN";
		$params["username"] = 'will@willhinesimprov.com';
		$params["password"] = EMAIL_PASSWORD_PRODUCTION;
	}
	$smtp = \Mail::factory('smtp', $params); // should now be set globally
	return $smtp;
	
}
