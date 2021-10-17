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

	  $body = wordwrap($body, 100, "\n");

	  if (LOCAL) {
	  	// connect to SMTP
		$smtp = get_smtp_object();
		$headers['To'] = $to = 'whines@gmail.com'; // everything to me on local
		$sent = true;
 		//$sent = $smtp->send($to, $headers, $body);  // laptop can use the SMTP server on willhines.net
		
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
		if (DEBUG_MODE && !LOCAL) {
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
	$body = '';
	$notifications = '';	
		
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
			$sub = "ENROLLED: {$wk['title']}";
			$body = "<p>You are ENROLLED in the workshop \"{$wk['title']}\".</p>";

			if ($wk['location_id'] == ONLINE_LOCATION_ID) {
				$body .= "<p>ZOOM LINK:<br>\n----------<br>\nThe Zoom link to your workshop is: {$wk['online_url']}.<br><br> \nTry to show up 5 minutes early if you can so we can get started right away.  If your class is multiple sessions, that link should work for all of them. We'll send you an email if the link changes.</p>";
			}
			
			$body .= payment_text($wk);
						
			$body .= 
"<p>CLASS INFO<br> \n----------<br> \nDescription and times/dates are both in this email and also listed here:<br>\n{$trans}</p>\n";
			
			$body .= email_boilerplate();

			$send_faq = false;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk['title']}";
			$body = "<p>You are on the WAITING LIST for \"{$wk['title']}.\" which starts {$wk['showstart']}</p>";
			
			
			$body .= "<p>WHAT DOES 'WAIT LIST' MEAN?<br>
---------------------------<br>
It means if someone drops the class, you'll get an email notifying you that there is an open spot.</p>

DROPPING OUT:<br>
------------------<br>
If you no longer want to be notified of open spots, you can drop out here: <br>
{$trans}</p>";			
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk['title']}";
			$body = "<p>You have DROPPED out of {$wk['title']}</p>";
			
			if ($e->fields['while_soldout'] == 1) {
				$body .= "<br><i>".get_dropping_late_warning()."</i>";
			}
			$body .= "If you change your mind, re-enroll here:\n{$trans}";
			
			// tell webmaster if this person needs a refund
			if ($e->fields['paid'] == 1) {
				centralized_email(WEBMASTER, "refund requested", "<p>{$u->fields['nice_name']} just dropped from the class '{$wk['title']}', and had already paid</p><p>See workshop info: ".URL."admin_edit2.php?wid={$wk['id']}</p>");
			}
			
			break;
			
		case APPLIED:
			$sub = "APPLIED: {$wk['title']}";
			$body = "<p>You have APPLIED for {$wk['title']}</p>";
			
			$body .= "<p>Your email has be added to the list and you'll be notified soon if you got in or not. Generally preference is given to new students unless it says otherwise in the class description.";
			break;
			
		default:
			$statuses = $lookups->statuses;
			$sub = "{$statuses[$status_id]}: {$wk['title']}";
			$body = "<p>$body</p>";
			
			break;
	}

	if ($status_id == ENROLLED) {
		$body .= "<p>CLASS INFORMATION<br>
		--------------------------------<br>
		<b>Title:</b> {$wk['title']}<br>
		<b>Teacher:</b> {$wk['teacher_info']['nice_name']}".($wk['co_teacher_id'] ?  ", {$wk['co_teacher_info']['nice_name']}" : '')."<br>
		<b>When:</b> {$wk['full_when']} (".TIMEZONE." - California time)<br>
		<b>Cost:</b> {$wk['costdisplay']}<br>".
		($status_id == ENROLLED ? "<b>Zoom link:</b> {$wk['online_url']}" : "<b>Zoom link</b>: We'll email you the zoom link if/once you are enrolled.")."<br>
		<b>Description:</b> {$wk['notes']}</p>
		<p>Web page for this class:<br>\n{$trans}</p>";	
	}

	return centralized_email($u->fields['email'], "{$sub}", $body);
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


function payment_text($wk, $reminder = 0) {
	
	$pt = "<p>PAYMENT:<br>\n--------<br>\n"; // payment text
	
	
	if ($reminder == 1) {
		
		if ($wk['cost'] > 1) {
			$pt .= "Our records show you have not yet paid. That's fine! This is just a reminder: payment is due by the start of class. ";
		}
		if ($wk['cost'] == 1) {
			$pt .= "Our records show you have not yet paid. That's fine! This is a PAY WHAT YOU CAN class, so paying is optional. ";
		}
	}
	
	if ($wk['cost'] > 1) {
		$pt .= "Class costs \${$wk['cost']} (USD). ";
	}
	if ($wk['cost'] == 1) {
		$pt .= "This is a PAY WHAT YOU CAN class. Full price is usually $40USD (or a suggested donation in the description), but pay anything you like including nothing. ";
	}	
	if ($wk['cost'] == 0) {
		$pt .= "This is a free class. ";
	}	
	
	if ($wk['cost'] != 0) {
		
		if ($wk['cost'] == 1) {
			$pt .= "<br><br>\n\nIf you're going to pay something, ";
		} else {
			$pt .= "<br><br>\n\nPay via ";
		}
		
		
		$pt .= "Venmo @wgimprovschool (business),<br>\n or PayPal payments@wgimprovschool.com<br>\n or https://paypal.me/WGImprovSchool.<br><br>\n\n";
		
		// get teacher last name
		$tnames = explode(' ', $wk['teacher_info']['nice_name']);
		$t_last_name = array_pop($tnames);

		// start date
		$wdate = date('n/j', (strtotime($wk['start'])));		
		$wnames = explode(' ', $wk['title']);
		
		$pt .= "Put '".strtoupper("{$wdate} {$t_last_name} {$wk['short_title']}")."' in your payment.<br><br>\n\nDue by the start of class. You'll get a confirmation email within 12 hours of paying.<br><br>\n\n";

		$pt .= "If you can't do Venmo or Paypal, contact payments@wgimprovschool.com";

	}

	$pt .= "</p>\n";
	
	return $pt;
	
}

function email_boilerplate() {
	
	return "<p>BE ON TIME<br> \n-----------<br> \nWe know sometimes lateness can't be helped but please do your best to be on time! Log into zoom five minutes before class starts! These classes are short so being even a few minutes late really disrupts things!</p>\n
<p>DROPPING THE CLASS<br> \n------------------<br> \nYou can drop out by going to class's web page (the link is just above this paragraph). If you're dropping within ".LATE_HOURS." of the start, please pay anyway.</p>\n\n
<p>SHOWS AND JAMS<br>\n
------------------<br>\n
There are online shows and jams that you can play in, if you wish! See the shows/jams page on the web site for more info:<br>\n
http://www.wgimprovschool.com/community</p>\n\n
<p>FACEBOOK AND CHAT<br>\n
---------------------\n
If you want to meet other students, check out our Facebook group or Discord chat server:
Facebook: http://www.facebook.com/groups/wgimprovschool<br>\n
Discord chat server: https://discord.gg/GXbP3wgbrc</p>";
	
}

