<main role="main">
	
	
<?php if ($link_email_sent_flag) { ?>	
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script type="text/javascript">
    $(window).on('load',function(){
        $('#checkYourEmail').modal('show');
    });
</script>	
<?php } ?>

<?php if ($u->logged_in() && !$u->fields['display_name']) { ?>	
		<div class="alert alert-info" role="alert">
		<p>Would you mind entering a name? Nickname is fine.</p>
	<?php echo $userhelper->edit_display_name($u); ?>
		</div>
<?php 		}  ?>	
	
  <!-- Main jumbotron for a primary  message -->
  <div class="jumbotron">
	<div class="container-lg container-fluid">
	  <div class="row align-items-center justify-content-center">
		<p class="col-12 col-sm-10 col-md-8">We teach online classes in <span class="color-long-form-improv">long-form improv</span> <!--<span class="color-character">character</span>--> and <span class="color-sketch">sketch</span>. New classes going up on January 4 9am PST!</p>
	  </div>
	  
	  <div class="row news-summary pb-4">
		<div class="col-12 d-flex align-items-start justify-content-start ">
		  <span class="badge badge-pill badge-primary h6"><a class="text-light" href="news.php">Shows and Jams</a></span>
		  <span class="h6 news-item pl-1"> EVERY FRIDAY -- see the <a href="news.php">shows/jams</a> page for details!</span>
		</div>
	  </div>
	  
	</div>
  </div>
  

  
  <div id="classes">
	<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Current & Upcoming Classes</h3></div>
	<h4 class="text-center class-time-announcement mt-5 mb-5 col-12">All Class Dates and Times are California Time (PST)</h4>
	
	
<?php
if (count($unavailable_workshops) > 0) {
	include 'unavailable_workshops.php';	
} 
?>
	
	
	<div class="container-lg container-fluid" id="classes-listings">
		  
		  
		<?php
			$classes_shown = 0;
			foreach ($upcoming_workshops as $wk) {
				
				if (!Wbhkit\is_future($wk['start'])) {
					continue; // skip ones that already started
				}
				$classes_shown++; // count how many classes we actually list
				
		?>
  	  <div class="row justify-content-between">
		
			<div class="col-md-11 classes-listings-class mb-5">
			  <h3 class="mb-3"><a href="workshop.php?wid=<?php echo $wk['id']; ?>"><?php echo $wk['title'];?></a></h3>
			  <p><?php
				  if ($wk['soldout']) { echo "<span class=\"text-danger\">Sold Out!</span> - ";  } 
				  echo $wk['notes']; 
				  ?></p>
			  <p class="class-time-info">Starting <?php echo $wk['showstart']; ?> for <?php echo $wk['total_sessions'];?> <?php echo ($wk['total_sessions'] == 1) ? 'week': 'weeks'; ?></p>
			  <div class="class-meta d-flex justify-content-between align-items-center mt-4">
				<div class="d-flex class-teacher col-7 mr-0 px-0 align-items-center">
				  <a href="teachers.php?tid=<?php echo $wk['teacher_id']; ?>"><img class="mr-3 teacher-image align-self-center" src="<?php echo \Teachers\get_teacher_photo_src($wk['teacher_user_id']);?>" alt="Teacher Name"></a>
				  <div class="">
					<h6 class="mt-0 mb-0 teacher-label">Teacher</h6>
					<h5 class="mt-0 mb-0 teacher-name"><a href="teachers.php?tid=<?php echo $wk['teacher_id']; ?>"><?php echo $wk['teacher_name'];?></a></h5>
				  </div>
				</div>
				<span class="class-price">
				  <?php echo $wk['cost']; ?> USD
				</span>
				<span class="class-enroll">
					
					<?php if ($wk['soldout']) { ?>
						<span class="text-danger">Sold Out!</span> <a class="btn btn-primary" href="workshop.php?wid=<?php echo $wk['id']; ?>" role="button">join wait list</a>
					<?php } else { ?>
						<a class="btn btn-primary" href="workshop.php?wid=<?php echo $wk['id']; ?>" role="button">Enroll</a>
					<?php } ?>
				</span>
			  </div>
			</div>
  </div>
			<?php
		}
		
		if ($classes_shown == 0) {
			echo "<h3 class='m-5'>No upcoming classes right now! Join the mailing list below to hear when new ones get posted.</h3>\n";
		}
		
		?>
  
  
  <div id="newsletter-signup" class="pt-4 pb-4 mb-5">
	<div class="container">
	  <h3 class="">Mailing List</h3>
	  <div class="row">
		<div class="col-lg-6 col-sm-12">
		  <p>If you want to know about classes the minute the go online, join my mailing list.</p>
			<p>You are NOT automatically put on my mailing list when you take a workshop.</p>
		  </div>
		  <div class="col-lg-6 col-md-8 col-sm-12">
			 <form action="https://willhines.us8.list-manage.com/subscribe/post?u=881f841fbb8bf66576e6e66cf&amp;id=43b29422a0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				  
			  <div class="form-group">
				  
				<input type="email" class="form-control" id="mce-EMAIL" name="EMAIL" aria-describedby="Email Address" placeholder="Email">
								
			  </div>
			  
			  <p>Powered by <a href="http://eepurl.com/hhR9pb" title="MailChimp - email marketing made easy and fun">MailChimp</a></p>
			  
			  <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_881f841fbb8bf66576e6e66cf_43b29422a0" tabindex="-1" value=""></div>
			  
			  <div class="form-group align-items-end">
				<button type="submit" class="btn btn-primary" name="subscribe" id="mc-embedded-subscribe" >Subscribe</button>
			  </div>
			  
			  <!-- mc spam protection i think -->
		  	<div id="mce-responses" class="clear">
		  		<div class="response" id="mce-error-response" style="display:none"></div>
		  		<div class="response" id="mce-success-response" style="display:none"></div>
		  	</div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
			  
		  </form>
			  </div>
		  </div>
	  </div>
	</div>
	
  <div id="buy-the-book" class="container mb-5">
	<h3 class="mb-3">Buy the Book</h3>
<div class="row">
  <img src="images/htbtgioe_cover.jpg" class="col-sm-12 col-md-3 align-self-start mb-2" />
	<p class="col-sm-12 col-md-9">If the workshops are sold out, you could buy "How to Be the Greatest Improviser on Earth" written by Will Hines, the founder of this school. Print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>.</p></div>
  </div>
  
  
  

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
  
</main>




