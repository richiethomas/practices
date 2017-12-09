<?php if (Users\logged_in() && !$u['display_name']) { ?>	
			
				<div class="alert alert-info" role="alert">
				<p>Would you mind entering a real human name? It's helpful for people to see who is signed up. Your email isn't shown, just this name.</p>
			<?php echo Users\edit_display_name($u); ?>
				</div>
<?php 		}  ?>
		
	
<div class='row mb-md-4'><div class='col-md-12'>
			<div id='login_prompt' class='card border-info bg-success'>
			<div class='card-body'>
	
<?php 		if (Users\logged_in()) { 
			
			echo "<h2 class='card-title text-light'>Welcome";
			if ($u['display_name']) {
				echo ", {$u['display_name']}";
			}
			echo "</h2>\n";
			echo "<p>You are logged in as <strong>{$u['nice_name']}</strong></p>";
?>			
				  <button type="button" class="btn btn-warning m-2" data-toggle="modal" data-target="#nameEmailModal"><span class="oi oi-person" title="person" aria-hidden="true"></span> update name and email</button>
				  <button type="button" class="btn btn-warning m-2" data-toggle="modal" data-target="#textModal"><span class="oi oi-phone" title="phone" aria-hidden="true"></span> update text notifications </button>				  
				  <a href="index.php?ac=lo" class="btn btn-warning m-2"><span class="oi oi-account-logout" title="account-logout" aria-hidden="true"></span> log out</a>
				  
				  <?php echo Wbhkit\get_modal('textModal', '<span class="oi oi-phone" title="phone" aria-hidden="true"></span> Text Notifications', Users\edit_text_preferences($u)); ?>

				  <?php echo Wbhkit\get_modal('nameEmailModal', '<span class="oi oi-person" title="person" aria-hidden="true"></span> Name and Email', Users\edit_display_name($u).'<br><br>'.Users\edit_change_email($u)); ?>
				  	  
			
<?php 		} else { ?>
			<h2 class='card-title'>Log In To This Site</h2>
			<p>First you must log in. We do that via email.</p>
			<?php echo Users\login_prompt(); ?>
<?php		} ?>
		</div></div></div></div> <!--// end two card divs, then column, then row-->


		<div class="row justify-content-center mb-md-4">
		
			<div class="col-md-4">
			<div class="card text-center text-white bg-warning">
		      <div class="card-body">
		        <h2 class="card-title"><span class="oi oi-dollar" title="dollar" aria-hidden="true"></span><br>Paying</h2>
		        <p class="card-text text-dark">
				Pay in person or with Venmo to whines@gmail.com. On the day of the workshop is fine.</p>
		        <a href="http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop"  class="btn btn-outline-light">Venmo whines@gmail.com</a>
		      </div> <!-- end of card body -->
		    </div> <!-- end of card -->
		</div> <!-- end of col -->		
					
			<div class="col-md-4">
			<div class="card text-center text-white bg-danger">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-envelope-closed" title="envelope-closed" aria-hidden="true"></span><br>Mailing List</h2>
			        <p class="card-text text-dark">You are NOT automatically put on my mailing list for these workshops. If you WANT to be on that mailing list, sign up right here.</p>
			        <a href="http://eepurl.com/c6-T-H" class="btn btn-outline-light">Join Mailing List</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->
			
			
			<div class="col-md-4">
			<div class="card text-center text-white bg-info">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-question-mark" title="question-mark" aria-hidden="true"></span><br>Common Questions</h2>
			        <p class="card-text text-dark">You can arrive late and leave early. Pre-reqs are not enforced. Click below to see other common questions.</p>
			        <a href="<?php echo $sc; ?>?ac=faq" class="btn btn-outline-light">More Common Questions</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->

			</div> <!-- end of row -->

			<div class='row mb-md-4'><div class='col'>
		<h2>Enroll In A Workshop</h2> 
		<?php echo $upcoming_workshops; ?>
		</div></div> <!-- end of col and row -->
		
		
		<div class='row mb-md-4'><div class='col'>
		<h2>Your Current/Past Workshops</h2>
		<?php if (Users\logged_in()) {
			echo $transcript; 
		} else {
			echo "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		?>
		</div></div> <!-- end of col and row -->
