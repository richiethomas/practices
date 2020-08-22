
<?php if ($link_email_sent_flag) { ?>
	
<script type="text/javascript">
    $(window).on('load',function(){
        $('#checkYourEmail').modal('show');
    });
</script>	

	
<?php } ?>
	
	<div class="row justify-content-center m-3 ">	
		<div class="col-sm-3 p-3 border border-dark text-center workshop-danger">	
			<h3 class="workshop-danger p-3"><a href="news.php">News</a></h3>
			<h4>Jams!</h4>
				<p>Friday August 28</p>
				<p>Jams 11am and 5pm PDT</p>
			<p>Come play. Details on the <a href="news.php">news page</a></p>
		</div>			
		<div class="col-sm-8 p-3"> 
<p class="lead">Welcome to the World's Greatest Improv School. We teach online classes in long-form improv, character and sketch. 
	
	<p>Learn more about...
	<ul>
		<li>the <a href="about_school.php">school</a></li>
		<li>the <a href="about_catalog.php">courses</a> we offer</li>
		<li><a href="about_works.php">how it works</a>: signing up, privacy, paying</li>
	</ul></p>
	
		</div>
	</div>

<?php if (Users\logged_in() && !$u['display_name']) { ?>	
		
			<div class="alert alert-info" role="alert">
			<p>Would you mind entering a name? Nickname is fine.</p>
		<?php echo Users\edit_display_name($u); ?>
			</div>
<?php 		}  ?>
	


<!-- Log in section -->


<?php 		

if (Users\logged_in()) { ?>		
<div id= "login_prompt" class='row mb-md-4 bg-info p-3'>
	<div class='col-md-12'>
		<p>Welcome, you are logged in as <strong><?php echo $u['nice_name']; ?></strong>.
   
   <?php if (Users\check_user_level(2)) { ?>
   
      	<a class='btn btn-outline-light m-2' href='admin.php'><span class="oi oi-clipboard" title="clipboard" aria-hidden="true"></span> admin site</a> 
	  
   <?php } // end of check user level 2
   ?> 
   
	<a class='btn btn-outline-light m-2' href='you.php'><span class="oi oi-person" title="person" aria-hidden="true"></span> edit your info</a>
	<a class='btn btn-outline-light m-2' href="<?php echo $sc; ?>?ac=lo"><span class="oi oi-account-logout" title="account-logout" aria-hidden="true"></span> log out of willhinesimprov.com</a></p>				
	</div>

</div> <!--// end of logged in -->


<?php } else { ?>


<script type="text/javascript">
	function onSignIn(googleUser) {
		console.log('inside onSignIn');
	  var id_token = googleUser.getAuthResponse().id_token;
	  var xhr = new XMLHttpRequest();
	  xhr.open('POST', 'https://wgimprovschool.com/gsign.php');
	  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	  xhr.onload = function() {
	    console.log('Signed in as: ' + xhr.responseText);
		createInput(xhr.responseText);
		$("#google-signinbutton").hide();
		$("#google-signout").show();
	  };
	  xhr.send('idtoken=' + id_token);
	}

	function createInput(key){
	    var $input = $('<p>Connected to Google! <a class="btn btn-primary" href="index.php?key='+key+'">Log in to wgimprovschool.com</a></p>');
	    $input.appendTo($("#google-authenticated"));
	}

  function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('User signed out.');
	  window.location.reload(false); 
    });
  }
</script>

<div id= "login_prompt" class='row bg-info p-3'>
	<div class="col-sm-12">
		<h2 class="text-center">Two Ways to Log In</h2>
	</div>
</div>
<div id= "login_prompt" class='row bg-info p-3'>
	<div class="col p-3 bg-warning">
		<h2>We Email You A Link</h2>
		<?php echo \Wbhkit\form_validation_javascript('log_in'); ?>
	
		<form id='log_in' action='<?php echo $sc; ?>' method='post' novalidate>
		<?php echo \Wbhkit\hidden('ac', 'link').
		\Wbhkit\texty('email', $email, 'Email', 'something@something.com', 'We will send you a email, click the link there.', 'Must be a valid email you have access to', ' required ', 'email').
		\Wbhkit\submit('Log In'); ?>
		</form>
	</div>
	<div class="col-sm-1 align-self-center">
		<h3>Or</h3>
	</div>
	
	<div class="col p-3 bg-warning">
		<h2>Sign in Via Google</h2>
		<div id="google-signinbutton" class="g-signin2" data-onsuccess="onSignIn"></div> 
		<p id="google-signout" class="my-3">Want to sign-out of Google? <a class="text-dark" href="#" onclick="signOut();">(Click here)</a></p>
		<div id="google-authenticated"></div>
	</div>
  
</div> <!--// end of login prompt-->
  
<?php } // end of "is user logged in?"
?>



<?php
if (count($unavailable_workshops) > 0) {
	include 'unavailable_workshops.php';	
} 
?>



		<div class='row mb-md-4'><div class='col'>
		<h2>Current / Upcoming Classes</h2> 
		<p class='mx-4''>Join the mailing list (bottom of page) to be notified on new classes first.</p>
		<?php echo $upcoming_workshops; ?>
		</div></div> <!-- end of col and row -->
	
	
			<div class="row border-top border-bottom py-3 bg-light">					
				<div class="col"> 
					<h2>Buy This Book</h2>
	<p class="lead">If the workshops are sold out, you could buy "How to Be the Greatest Improviser on Earth" written by Will Hines, the founder of this school. Print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>. You could also buy a much prettier digital version from Will's <a href="http://www.improvnonsense.com/">personal online bookstore</a>.</p>
				</div>
				<div class="col-md-3">
					<a href="https://www.amazon.com/dp/0982625723"><img src="assets/htbtgioe_cover.jpg" class="img-fluid" alt="How to Be The Greatest Improviser on Earth"></a>
				</div>
			</div>

		<div class="row justify-content-center mb-md-4">
	
					
			<div class="col-sm-6 my-4">
			<div class="card text-center text-white bg-success">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-envelope-closed" title="envelope-closed" aria-hidden="true"></span><br>Mailing List</h2>
			        <p class="card-text text-dark">If you want to know about classes the minute the go online, join my mailing list. You are NOT automatically put on my mailing list when you take a workshop. You have to explicitly join by clicking the link/button below.</p>
			        <a href="http://eepurl.com/R2Ytz" class="btn btn-outline-light">Join Mailing List</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->
			
			

			</div> <!-- end of row -->


<!-- check your email modal -->
<div class="modal" tabindex="-1" role="dialog" id="checkYourEmail">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Check your email, <?php echo $email; ?>!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<p>We JUST sent an email to you at <b><?php echo $email; ?></b>. In that email is a link to log in. Click that link!</p>
			<p>If you didn't get an email, refresh the home page and try again.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

