<?php
namespace Emails;	

define('MUTE_LOCAL_EMAIL', true);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//require_once 'Mail.php';
//require_once 'Mail/mime.php';	 // Mail_mime did weird things to encoding of mail


function centralized_email($to, $sub, $body, $realname = null) {
		
	global $logger, $lookups;	
	
	$mail = get_phpmailer_object();
	$mail->setFrom(WEBMASTER);
	if (LOCAL) {
		$mail->addAddress('whines@gmail.com', 'Will Hines'); // all local mail to me!
	} else {
		$mail->addAddress($to, $realname);
	}
		
	$mail->Subject = $sub;
	
	$mail->msgHTML($body);

	//send the message, check for errors
	$sent = false;
	if (LOCAL && MUTE_LOCAL_EMAIL) {
		$sent = true;
	} else {
		try {
			$sent = $mail->send();
		} catch (Exception $e) {
		    $logger->error($e->errorMessage());
		} catch (\Exception $e) { 
		    $logger->error($e->getMessage()); 
		}
	}
	
	if ($sent) {
		$logger->debug("emailed '$to', '$sub'");
		
		$stmt = \DB\pdo_query(
		"insert into email_log (to_email, from_email, subject, when_sent) 
			VALUES (:to_email, :from_email, :subject, :when_sent)", 
		array(':to_email' => $to, ':from_email' => WEBMASTER, ':subject' => $sub, ':when_sent' => date(MYSQL_FORMAT))); 
		
		
		return true;
	} else {
		$logger->error($mail->ErrorInfo);
		return $mail->ErrorInfo;
	}
	
}

function confirm_email(\Enrollment $e, $status_id = ENROLLED) {

	$wk = $e->wk;
	$u = $e->u;	
	$trans = URL."workshop/view/{$wk->fields['id']}";
	$body = '';
	$notifications = '';	
		
	$send_faq = false;
	switch ($status_id) {
		case 'already':
		case ENROLLED:
		
			$sub = "ENROLLED: {$wk->fields['title']}";
			$body = "<p>You are ENROLLED in the workshop \"{$wk->fields['title']}.\"<br>Starts: {$wk->fields['showstart']}</p>";

			if ($wk->fields['location_id'] == ONLINE_LOCATION_ID) {
				$body .= "<p>ZOOM LINK:<br>\n----------<br>\nThe Zoom link to your workshop is: {$wk->url['online_url_display']}<br>\n";
				$body .= "<br>\nTry to show up 5 minutes early if you can so we can get started right away.  If your class is multiple sessions, that link should work for all of them. We'll send you an email if the link changes.</p>\n";
			} else {
				$body .= "<p>LOCATION:<br>\n---------<br>\n{$wk->location['place']}<br>\n{$wk->location['address']}<br>\n{$wk->location['city']}, {$wk->location['state']} {$wk->location['zip']}";

				if ($wk->location['notes']) { $body.= "<br>{$wk->location['notes']}"; }
				
				$body .= "</p>\n";

			}
			
			$body .= payment_text($wk);
						
			$body .= 
"<p>CLASS INFO<br>\n----------<br>\nDescription and times/dates are both in this email and also listed here:<br>\n{$trans}</p>\n";
			
			$body .= $wk->email_teacher_info();

			$body .= email_boilerplate();
			
			
			$send_faq = false;
			break;
		case WAITING:
			$sub = "WAIT LIST: {$wk->fields['title']}";
			$body = "<p>You are on the WAITING LIST for \"{$wk->fields['title']}.\" which starts {$wk->fields['showstart']}</p>";
			
			
			$body .= "<p>WHAT DOES 'WAIT LIST' MEAN?<br>
---------------------------<br>
It means if someone drops the class, you'll get an email notifying you that there is an open spot.</p>

DROPPING OUT:<br>
------------------<br>
If you no longer want to be notified of open spots, you can drop out here: <br>
{$trans}</p>";			
			break;
		case DROPPED:
			$sub = "DROPPED: {$wk->fields['title']}";
			$body = "<p>You have DROPPED out of {$wk->fields['title']}</p>";			
			
			$hours_left = (strtotime($wk->fields['start']) - strtotime('now')) / 3600;
			if ($hours_left > 0 && $hours_left < LATE_HOURS) {
				$body .= "<br><i>".get_dropping_late_warning()."</i>";
			}
			$body .= "If you change your mind, re-enroll here:\n{$trans}";
			
			// tell webmaster if this person needs a refund
			if ($e->fields['paid'] == 1) {
				centralized_email(WEBMASTER, "refund requested", "<p>{$u->fields['nice_name']} just dropped from the class '{$wk->fields['title']}', and had already paid</p><p>See workshop info: ".URL."admin-workshop/view/{$wk->fields['id']}</p>");
			}
			
			break;
			
		case APPLIED:
			$sub = "APPLIED: {$wk->fields['title']}";
			$body = "<p>You have APPLIED for '{$wk->fields['title']}' starting on {$wk->fields['showstart']}.</p>\n";
			
			$body .= "<p>Your email has be added to the list and you'll be notified soon if you got in or not. Generally preference is given to new students unless it says otherwise in the class description.</p>\n";
			
			$body .= "<p>Full info on the class is here: {$trans}</p>\n";
			
			break;
			
		default:
			$statuses = $lookups->statuses;
			$sub = "{$statuses[$status_id]}: {$wk->fields['title']}";
			$body = "<p>$body</p>";
			
			break;
	}

	if ($status_id == ENROLLED) {
		$body .= "<p>CLASS INFORMATION<br>
		--------------------------------<br>
		<b>Title:</b> {$wk->fields['title']}<br>
		<b>Teacher:</b> {$wk->fields['teacher_name']}<br>
		<b>When:</b> {$wk->fields['full_when']}<br>
		<b>Cost:</b> {$wk->fields['costdisplay']}<br>";
		
		
		if ($wk->fields['location_id'] == ONLINE_LOCATION_ID) {
			if ($status_id == ENROLLED) {
					$body .= "<b>Zoom link:</b> {$wk->url['online_url_display']}<br>";
			} else {
				$body .= "<b>Zoom link</b>: We'll email you the zoom link if/once you are enrolled.<br>";
			}
		} else {
			$body .= "<b>Where:</b> {$wk->location['lwhere']}<br>";
		}
		$body .= "<b>Description:</b> {$wk->fields['notes']}</p>
		<p>Web page for this class:<br>\n{$trans}</p>";	
	}

	return centralized_email($u->fields['email'], "{$sub}", $body);
}


function get_dropping_late_warning() {
	return "NOTE: You are dropping within ".LATE_HOURS." hours of the start. Please still pay for it! If someone takes your spot and pays, you'll be refunded. Questions to payments@wgimprovschool.com";
	
}



function email_footer() {

	return "
<p>Thanks!</p>

<p>World's Greatest Improv School<br>
<a href='http://www.wgimprovschool.com/'>http://www.wgimprovschool.com/</a></p>";
}


function get_workshop_summary(\Workshop $wk) {
	
		$summary = "<br>
<p>-----------------------------<br>
<b>Class information:</b><br>
<b>Title:</b> {$wk->fields['title']}<br>
<b>Teacher:</b> {$wk->teacher['nice_name']}".($wk->fields['co_teacher_id'] ?  ", {$wk->coteacher['nice_name']}" : '')."<br>
<b>When:</b> {$wk->fields['full_when']}";

	if ($wk->fields['location_id'] != ONLINE_LOCATION_ID) {
		$summary .= "<br>
<b>Where:</b> {$wk->location['lwhere']}";
	}
	
	$summary .= "<br>
<b>Drop / re-enroll / more info: ".URL."workshop/view/{$wk->fields['id']}</p>";
	
	return $summary;
}


function get_phpmailer_object() {
	
	global $mail;
	
	if (isset($mail) && is_object($mail)) {
		$mail->clearAddresses();
		return $mail;
	} else {
		
		$mail = new PHPMailer(true);
		$mail->WordWrap = 80; 
		
		if (LOCAL) {
			$mail->isSMTP();
			$mail->SMTPKeepAlive = true; 
			$mail->SMTPAuth = true;
			//SMTP::DEBUG_OFF = off (for production use)
			//SMTP::DEBUG_CLIENT = client messages
			//SMTP::DEBUG_SERVER = client and server messages
			$mail->SMTPDebug = SMTP::DEBUG_OFF;
			$mail->Port = 26;
			$mail->Host = 'mail.willhines.net';
			$mail->Username = 'will@willhines.net';
			$mail->Password = EMAIL_PASSWORD_LOCAL;
		}
		return $mail;
		
		/*
		
		// SMTP settings production
		$mail->Port = 465;
		$mail->Host = 'ssl://premium130.web-hosting.com';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  
		$mail->Username = 'classes@wgimprovschool.com';
		$mail->Password = EMAIL_PASSWORD_PRODUCTION;
		*/
		
	}

}


function payment_text(\Workshop $wk, $reminder = 0) {
	
	$pt = "<p>PAYMENT:<br>\n--------<br>\n"; // payment text
	
	
	if ($reminder == 1) {
		
		if ($wk->fields['cost'] > 1) {
			$pt .= "Our records show you have not yet paid. That's fine! This is just a reminder: payment is due by the start of class. NOTE: if you paid within the last 12 hours or so, then you're all set. We just haven't processed that payment into the system yet. When we do you'll get an email letting you know we've received it. ";
		}
		if ($wk->fields['cost'] == 1) {
			$pt .= "Our records show you have not yet paid. That's fine! This is a PAY WHAT YOU CAN class, so paying is optional.  NOTE: if you DID pay soemthing but it was within the last 12 hours or so, we just haven't processed that payment into the system yet. When we do you'll get an email letting you know we've received it.";
		}
	}
	
	if ($wk->fields['cost'] > 1) {
		$pt .= "Class costs \${$wk->fields['cost']} (USD). ";
	}
	if ($wk->fields['cost'] == 1) {
		$pt .= "This is a PAY WHAT YOU CAN class. Full price is usually $40USD (or a suggested donation in the description), but pay anything you like including nothing. ";
	}	
	if ($wk->fields['cost'] == 0) {
		$pt .= "This is a free class. ";
	}	
	
	if ($wk->fields['cost'] != 0) {
		
		if ($wk->fields['cost'] == 1) {
			$pt .= "<br><br>\n\nIf you're going to pay something, ";
		} else {
			$pt .= "<br><br>\n\nPay via ";
		}
		
		
		$pt .= "Venmo @wgimprovschool (business),<br>\n or PayPal payments@wgimprovschool.com<br>\n or https://paypal.me/WGImprovSchool.<br><br>\n\n";
		
		// get teacher last name
		$tnames = explode(' ', $wk->teacher['nice_name']);
		$t_last_name = array_pop($tnames);

		// start date
		$wdate = date('n/j', (strtotime($wk->fields['start'])));		
		
		$pt .= "Put '".strtoupper("{$wdate} {$t_last_name} {$wk->fields['short_title']}")."' in your payment.<br><br>\n\nDue by the start of class. You'll get a confirmation email within 12 hours of paying.<br><br>\n\n";

		$pt .= "If you can't do Venmo or Paypal, contact payments@wgimprovschool.com";

	}

	$pt .= "</p>\n";
	
	return $pt;
	
}

function email_boilerplate() {
	
	return "<p>BE ON TIME<br> \n-----------<br> \nDo your best to be on time! Classes are short so being even a few minutes late really disrupts things!</p>\n
<p>DROPPING THE CLASS<br> \n------------------<br> \nYou can drop out by going to class's web page (the link is just above this paragraph). If you're dropping within ".LATE_HOURS." of the start, we may ask you to still pay.</p>\n\n
<p>SHOWS AND JAMS<br>\n
------------------<br>\n
There are shows and jams, if you wish!<br>\n
http://www.wgimprovschool.com/shows</p>\n\n
<p>FACEBOOK AND CHAT<br>\n
---------------------<br>\n
If you want to meet other students, check out:\n
Facebook group: http://www.facebook.com/groups/wgimprovschool<br>\n
Discord chat: https://discord.gg/GXbP3wgbrc</p>";
	
}

