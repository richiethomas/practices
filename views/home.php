<?php if ($link_email_sent_flag) { ?>	
<script type="text/javascript">
    $(window).on('load',function(){
        $('#checkYourEmail').modal('show');
    });
</script>	
<?php } ?>

<?php if (Users\logged_in() && !$u['display_name']) { ?>	
		<div class="alert alert-info" role="alert">
		<p>Would you mind entering a name? Nickname is fine.</p>
	<?php echo Users\edit_display_name($u); ?>
		</div>
<?php 		}  ?>
	

<main role="main">
  <!-- Main jumbotron for a primary  message -->
  <div class="jumbotron">
	<div class="container-lg container-fluid">
	  <div class="row align-items-center justify-content-center">
		<p class="col-12 col-sm-10 col-md-8">We teach online classes in <span class="color-long-form-improv">long-form improv</span>. <!--<span class="color-character">character</span> and <span class="color-sketch">sketch</span>.--></p>
	  </div>
	  <div class="row news-summary pb-4">
		<div class="col-12 d-flex align-items-start justify-content-start ">
		  <span class="badge badge-pill badge-primary h6"><a href="news.php">News</a></span>
		  <span class="h6 news-item pl-1">Online jams every Friday for current/former students! <a href="news.php">See details</a>.</span>
		</div>
	  </div>
	</div>
  </div>
  <div id="classes">
	<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid"><a href="/classes">Current & Upcoming Classes</a></h3></div>
	<h4 class="text-center class-time-announcement mt-5 mb-5 col-12">All Class Dates and Times are California Time (PST)</h4>
	<div class="container-lg container-fluid" id="classes-listings">
	  <div class="row justify-content-between">
		  
		  
		<?php
			foreach ($upcoming_workshops as $wk) {
		?>
			<div class="col-md-6 classes-listings-class mb-5">
			  <h3 class="mb-3"><?php echo $wk['title'];?></h4>
			  <p><?php echo $wk['notes']; ?></p>
			  <p class="class-time-info">Starting <?php echo $wk['showstart']; ?> for <?php echo $wk['total_sessions'];?> weeks</p>
			  <div class="class-meta d-flex justify-content-between align-items-center mt-4">
				<div class="d-flex class-teacher col-7 mr-0 px-0 align-items-center">
				  <img class="mr-3 teacher-image align-self-center" src="<?php echo \Teachers\get_teacher_photo_src($wk['teacher_user_id']);?>" alt="Teacher Name">
				  <div class="">
					<h6 class="mt-0 mb-0 teacher-label">Teacher</h6>
					<h5 class="mt-0 mb-0 teacher-name"><?php echo $wk['teacher_name'];?></h5>
				  </div>
				</div>
				<span class="class-price">
				  <?php echo $wk['cost']; ?> USD
				</span>
				<span class="class-enroll"><a class="btn btn-primary" href="workshop.php?wid=<?php echo $wk['id']; ?>" role="button">Sign Up</a></span>
			  </div>
			</div>
			<?php
		}
		?>
  </div><!-- Upcoming Classes -->
  
  <div id="newsletter-signup" class="pt-4 pb-4 mb-5">
	<div class="container">
	  <h3 class="http://eepurl.com/R2Ytz">Mailing List</h3>
	  <div class="row">
		<div class="col-lg-12 col-sm-12">
		  <p>If you want to know about classes the minute the go online, <a class="text-light" href="http://eepurl.com/R2Ytz">join the mailing list</a>.</p>
			<p>You are NOT automatically put on the mailing list when you take a workshop.</p>
		  </div>
		  
		 
	  </div>
	</div>
	
  <div id="buy-the-book" class="container mb-5">
	<h3 class="mb-3">Buy the Book</h3>
<div class="row">
  <img src="images/htbtgioe_cover.jpg" class="col-sm-12 col-md-3 align-self-start mb-2" />
	<p class="col-sm-12 col-md-9">If the workshops are sold out, you could buy "How to Be the Greatest Improviser on Earth" written by Will Hines, the founder of this school. Print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>. You could also buy a much prettier digital version from Will's <a href="http://www.improvnonsense.com/">personal online bookstore</a>.</p></div>
  </div>
</main>




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
