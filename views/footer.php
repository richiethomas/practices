 <?php if ($page != 'home') {
	 echo "
	 	</article>	
	 	</div>  
	 </main>
";
 }
 ?>

<footer class="pt-4 pb-4"><div class="container-lg container-fluid">
	<h3 class="mb-3">The World's Greatest Improv School</h3>
	<ul class="nav mb-3">
	   <?php foreach( get_nav_items() as $nav_item ){ ?>
	   <li class="nav-item"> <a class="nav-link" href="<?php echo $nav_item['href'] ?>"><?php echo $nav_item['title'] ?></a> </li>
	   <?php } ?>
	</ul>
	<div class="footer-colophon row justify-content-between mb-3">
		<div class="col-lg-7 col-sm-12">
		Send questions to Will Hines - <a href="mailto:will@wgimprovschool.com">will@wgimprovschool.com</a> | <a href="privacy.php">Privacy Policy</a>
		</div>
		<div class="col-lg-5 col-sm-12">
		Site uses: <a href="http://www.php.net/">PHP</a> / <a href="http://www.mysql.com/">MySQL</a> / <a href="http://www.getbootstrap.com/">Bootstrap</a> / <a href="http://useiconic.com/open">Open Iconic</a>
		</div>
	</div>
</div></footer>

<!-- Login Modal -->
<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="Login Modal Dialog" aria-hidden="true">
	
  <div class="modal-dialog modal-dialog-centered" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		  
		<h5 class="modal-title">Two Ways to Login</h5>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
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
					window.location.reload(true); 
				  };
				  xhr.send('idtoken=' + id_token);
				}
				
				var initClient = function() {
				    gapi.load('auth2', function(){
				        /**
				         * Retrieve the singleton for the GoogleAuth library and set up the
				         * client.
				         */
				        auth2 = gapi.auth2.init({
				            client_id: '989168310652-al6inpe49ep29r9i2ppb0t8j58k1pt22.apps.googleusercontent.com'
				        });

				        // Attach the click handler to the sign-in button
				        auth2.attachClickHandler('signin-button', {}, onSuccess, onFailure);
				    });
				};

				/**
				 * Handle successful sign-ins.
				 */
				var onSuccess = function(user) {
					$("#google-signinbutton").hide();
					$("#google-signout").show();
				    console.log('Signed in as ' + user.getBasicProfile().getName());
				 };

				/**
				 * Handle sign-in failures.
				 */
				var onFailure = function(error) {
					$("#google-signinbutton").show();
					$("#google-signout").false();
				    console.log(error);
				};				

				function createInput(key){
				    var $input = $('<p>You are connected to Google! Now you can log in to this website: <a class="btn btn-primary" href="index.php?key='+key+'">Click here to log in to wgimprovschool.com</a></p>');
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


		<h2>1) We Email You A Link</h2>
		<?php echo \Wbhkit\form_validation_javascript('log_in'); ?>
	
		<form id='log_in' action='index.php' method='post' novalidate>
		<?php echo \Wbhkit\hidden('ac', 'link').
		\Wbhkit\texty('email', '', 'Email', 'something@something.com', 'We will send you an email with a link to click.', 'Must be a valid email you have access to', ' required ', 'email').
		\Wbhkit\submit('Send Me An Email'); ?>
		</form>

<hr>

		<h2>or 2) Sign in Via Google</h2>
		<div id="google-signinbutton" class="g-signin2" data-onsuccess="onSignIn"></div> 
		
		<p id="google-signout" class="my-3 hidden">Want to sign-out of Google? <a class="text-dark" href="#" onclick="signOut();">(Click here)</a></p>
		<div id="google-authenticated"></div>
  
	</div>
	  <div class="modal-footer">
	 
	 
	  </div>
	</div>
  </div>
</div>
</html>

<script>
$( document ).ready(function() {
  $('#google-signout').removeClass('hidden');
}
</script>

<?php
if (TIMER) {
	echo show_hrtime();
}
?>