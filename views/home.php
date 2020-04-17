
		<div class="row">					
			<div class="col-md-3">
				<img src="assets/headshot20194.jpg" class="img-fluid" alt="Will Hines photo"><br><small>Photo by: <a href="http://www.claylarsen.net/">Clay Larsen</a></small>
			</div>
			
			<div class="col"> 
<p class="lead">Hi, I'm Will! I'm one of the top improv teachers in the whole world, can you believe it? I've taught all experience levels for 20 years, have written a <a href="https://www.amazon.com/dp/0982625723">best-selling book</a> on the subject, and am a <a href="https://www.imdb.com/name/nm2654402/">working comedic actor</a>.</p>
			</div>
		</div>

<?php if (Users\logged_in() && !$u['display_name']) { ?>	
			
				<div class="alert alert-info" role="alert">
				<p>Would you mind entering a name? Nickname is fine.</p>
			<?php echo Users\edit_display_name($u); ?>
				</div>
<?php 		}  ?>
		
	
<div class='row mb-md-4'><div class='col-md-12'>
			<div id='login_prompt' class='card bg-info'>
			<div class='card-body'>
	
<?php 		if (Users\logged_in()) { 
			
			echo "<p>Welcome, you are logged in as <strong>{$u['nice_name']}</strong>.</p>";
?>		
<ul class="nav">
  <li class="nav-item">
    <a class='nav-link btn btn-outline-light m-2' href='' data-toggle="modal" data-target="#nameEmailModal"><span class="oi oi-person" title="person" aria-hidden="true"></span> update name and email</a>
  </li>
  <li class="nav-item">
    <a class='nav-link btn btn-outline-light m-2' href='' data-toggle="modal" data-target="#textModal"><span class="oi oi-phone" title="phone" aria-hidden="true"></span> update text notifications</a>
  </li>
  <li class="nav-item">
    <a class='nav-link btn btn-outline-light m-2' href="index.php?ac=lo"><span class="oi oi-account-logout" title="account-logout" aria-hidden="true"></span> log out</a>
  </li>
</ul>	
							  	  
			
<?php 		} else { ?>
			<h2 class='card-title'>Log In To This Site</h2>
			<p>First you must log in. We do that via email.</p>
			<?php echo Users\login_prompt(); ?>
<?php		} ?>
		</div></div></div></div> <!--// end two card divs, then column, then row-->


<div class="row justify-content-center"><div class="col-md-6 border border-info">
<h2>How This Works</h2>
<ul>
	<li>All class times are California local time.</li>
	<li>You log in with your email, a link gets emailed to you, you click it. Then you can enroll in classes, drop out, join waiting lists</li>
	<li>Pay with Venmo or Paypal</li>
	<li>Classes are held over <a href="http://www.zoom.us/">Zoom</a></li>
	<li>Wear headphones during the workshop.</li>
	<li><b>LATE DROP POLICY: If you drop within <?php echo LATE_HOURS; ?> hours of the start of the workshop, you still must pay.</b></li>
</ul>
</div>
</div>




<?php
if (count($unavailable_workshops) > 0) {
	
echo "<div class=\"row justify-content-center my-3\">\n";
echo "<div class=\"col-md-6 border border-info\">\n";
echo "<h2>Classes Going Live Soon</h2>\n";

$current_date = null;
foreach ($unavailable_workshops as $wk) {

	// update date?
	$next_date = Workshops\friendly_date($wk['when_public']).' '.Workshops\friendly_time($wk['when_public']);
	
	if ($next_date != $current_date) {
		
		if ($current_date) {
			echo "</ul>\n";
		}
		
		echo "<h6>Going live: $next_date</h6>\n<ul>";
		$current_date = $next_date;
	}
	
	$wkdate = date("l F j", strtotime($wk['start']));
	$start = Workshops\friendly_time($wk['start']);
	$end = Workshops\friendly_time($wk['end']);	
	echo "<li>$wkdate: {$wk['title']}, $start-$end \${$wk['cost']} (USD)</li>\n";	
}	

echo "</ul>\n";
echo "<p class=\"font-weight-light\">(All times PDT - California time)</p></div></div>\n";
} // end 'if unavailable workhops > 0'
?>



			<div class='row mb-md-4'><div class='col'>
		<h2>Available Workshops</h2> 
		<?php echo $upcoming_workshops; ?>
		</div></div> <!-- end of col and row -->
	
	
			<div class="row border-top border-bottom py-3 bg-light">					
				<div class="col"> 
					<h2>Buy My Book</h2>
	<p class="lead">If the workshops are sold out, you could buy my improv book, sensibly titled "How to Be the Greatest Improviser on Earth." It's consistently amongst the bestselling books on Amazon in the "acting and auditioning" category, and has sold all over the world. Tips on being present, being authentic, being funny and being healthy. There are print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>. You could also buy a much prettier digital version from my <a href="http://www.improvnonsense.com/">personal online bookstore</a>.</p>
				</div>
				<div class="col-md-3">
					<a href="https://www.amazon.com/dp/0982625723"><img src="assets/htbtgioe_cover.jpg" class="img-fluid" alt="How to Be The Greatest Improviser on Earth"></a>
				</div>
			</div>
	
		
		
		<div class='row mb-md-4'><div class='col'>
		<h2>Your Workshops</h2>
		<?php if (Users\logged_in()) {
			echo $transcript; 
		} else {
			echo "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		?>
		</div></div> <!-- end of col and row -->


		<div class="row justify-content-center mb-md-4">
		
			<div class="col-md-4">
			<div class="card text-center text-white bg-warning">
		      <div class="card-body">
		        <h2 class="card-title"><span class="oi oi-dollar" title="dollar" aria-hidden="true"></span><br>Paying</h2>
		        <p class="card-text text-dark">
				Venmo to @willhines, or Paypal to whines@gmail.com</p>
		        <a href="http://venmo.com/willhines?txn=pay&share=friends&amount=30&note=improv%20workshop"  class="btn btn-outline-light">Venmo @willhines</a>
		      </div> <!-- end of card body -->
		    </div> <!-- end of card -->
		</div> <!-- end of col -->		
					
			<div class="col-md-4">
			<div class="card text-center text-white bg-danger">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-envelope-closed" title="envelope-closed" aria-hidden="true"></span><br>Mailing List</h2>
			        <p class="card-text text-dark">If you want to know about classes the minute the go online, join my mailing. You are NOT automatically put on my mailing list for these workshops. You have to explicitly join by clicking the link/button below.</p>
			        <a href="http://eepurl.com/R2Ytz" class="btn btn-outline-light">Join Mailing List</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->
			
			
			<div class="col-md-4">
			<div class="card text-center text-white bg-success">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-question-mark" title="question-mark" aria-hidden="true"></span><br>Common Questions</h2>
			        <p class="card-text text-dark">We use the <a class="text-dark font-weight-bold" href="http://www.zoom.us/">Zoom app</a>. Please note the <b>time zone ()</b>!</p>
			        <a href="<?php echo $sc; ?>?ac=faq" class="btn btn-outline-light">More Common Questions</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->

			</div> <!-- end of row -->
			
			
		
<?php if (Users\logged_in()) { ?>		
				  <?php echo Wbhkit\get_modal('textModal', '<span class="oi oi-phone" title="phone" aria-hidden="true"></span> Text Notifications', Users\edit_text_preferences($u)); ?>

				  <?php echo Wbhkit\get_modal('nameEmailModal', '<span class="oi oi-person" title="person" aria-hidden="true"></span> Name and Email', Users\edit_display_name($u).'<br><br>'.Users\edit_change_email($u)); ?>

<?php } ?>

