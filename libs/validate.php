<?php
namespace Validate;
/////////////////////////////////////////////////////////////////////
// make sure user is logged in
// 2.0 - requires two lines in source script, one to include, one to invoke
//     - had a redirect after validating so you're not going to first actual page
//     - off a form submission
// 2.1 - updated HTML for bootstrap 4, added namespace
/////////////////////////////////////////////////////////////////////

//session_start();
//validate_user() or die;





function get_submitted_password() {
	if (isset($_POST['talentPass102']) && $_POST['talentPass102'])
	{
	  $_SESSION['s_talentPass102'] = $_POST['talentPass102'];
	  setcookie('c_talentPass102', $_POST['talentPass102'], time() + 60*60*24*7);
	} elseif (isset($_COOKIE['c_talentPass102']) && $_COOKIE['c_talentPass102']) {
  	  $_SESSION['s_talentPass102'] = $_POST['talentPass102'] = $_COOKIE['c_talentPass102'];
	}
	
	return isset($_SESSION['s_talentPass102']) ? $_SESSION['s_talentPass102'] : null;
}


function is_validated() {
	
	if (get_submitted_password() == \DB\get_admin_password()) {
		//redirect if we have JUST validated, 
		//it avoids going to the page off a form submission
		if (isset($_REQUEST['validating']) && $_REQUEST['validating'] == 'true') {
			$script = $_SERVER['SCRIPT_NAME'] . ($_REQUEST['query'] ? "?{$_REQUEST['query']}" : '');
			header("Location: $script");
		}
		return true;
	} else {
		return false;
	}
}

function invalidate() {
	unset($_SESSION['s_talentPass102']);
	unset($_POST['talentPass102']);
  	setcookie('c_talentPass102', $_POST['talentPass102'], -60*60*24*7*52);
}

/////////////////////////////////////////////////////////////////////
// validate user -- user name and password stored in the code here
/////////////////////////////////////////////////////////////////////

function validate_user() {

	$pass102 = get_submitted_password();
	$password_to_use = \DB\get_admin_password();

	if ($pass102 != $password_to_use) {

    echo <<<VALIDFORM

    <form action="{$_SERVER['SCRIPT_NAME']}" method="post">
	<div class="form-group">
      <label for="talentPass102">Password:</label>
	  <input class="form-control" type="password" name="talentPass102">
  	</div>
      <button type="submit" class="btn btn-primary">Submit</button>
	  <input type="hidden" name="validating" value="true">
	  <input type="hidden" name="query" value="{$_SERVER['QUERY_STRING']}">
    </form>

VALIDFORM;

    return false;
  }

  if (isset($_POST['pass102']) && $_POST['pass102']) {
  	echo "<meta http-equiv=\"refresh\" content=\"0;url=http://{$_SERVER['SERVER_NAME']}{$_SERVER['SCRIPT_NAME']}\">";
  	echo "<p>If you're not redirected automatically, <a href=\"{$_SERVER['SCRIPT_NAME']}\">click here to continue</a></p>";
  	exit;
  }

  return true;
}

if (!is_validated()) {
	include 'views/header.php';
	validate_user() or die();
	include 'views/footer.php';
	exit;
}

