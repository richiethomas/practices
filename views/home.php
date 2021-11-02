<?php
function teacher_link($tinfo) {
  echo "
	  <a href='/teachers/view/{$tinfo['id']}'><img class='mx-2 teacher-image align-self-center' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."' alt='Teacher Name'></a>
	<div class=''>
		<h6 class='mt-0 mb-0 teacher-label'>Teacher</h6>
		<h5 class='mt-0 mb-0 teacher-name'><a href='/teachers/view/{$tinfo['id']}'> {$tinfo['nice_name']}</a></h5>
	</div>";	
}	
	
?>	

<main role="main">

<?php if ($link_email_sent_flag) { ?>	
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



<!--
<div class="jumbotron">
	<div class="container-lg container-fluid">
	  <div class="row align-items-center justify-content-center">
		<p class="col-12 col-sm-10 col-md-8">We teach online classes in <span class="color-long-form-improv">long-form improv</span> <span class="color-character">character</span> and <span class="color-sketch">sketch</span>.</p>
	  </div>

	  <div class="row news-summary pb-4">
		<div class="col-12 d-flex align-items-start justify-content-start ">
		  <span class="badge rounded-pill bg-primary h6 mx-2"><a class="text-light" href="shows.php">Shows and Jams</a></span>
		  <span class="h6 news-item pl-1"> See the <a href="shows.php">shows/jams</a> page for details!</span>
		</div>
	  </div>
	  
	</div>
  </div>
-->

<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Latest News</h3></div>
	
	<div class="container-lg container-fluid mt-3" id="news">
		
		<div class="row align-items-center">
			<div class="col-sm-5">
				
				<figure class="figure">
				  <a href="/teams"><img src="/images/teams.png" class="figure-img img-fluid rounded" alt="2021 Winter Teams"></a>
				  <figcaption class="figure-caption text-end">art by Gareth O'Connor</figcaption>
				</figure>
			</div>
			<div class="col-sm-7">

			  <dl class="row my-5">
			    <dt class="col-sm-3">WGIS Teams</dt>
			    <dd class="col-sm-9">We have online house teams! Every Monday 11am and 5pm (California time, <?php echo TIMEZONE; ?>) <a href="/teams">See more info here</a>.</dd>
			  </dl>

			  <dl class="row my-5">
			    <dt class="col-sm-3">Community</dt>
			    <dd class="col-sm-9">We have a great community around our classes! Facebook groups, chat servers, Twitch channels -- <a href="/community">learn about all these things here</a>.</dd>
			  </dl>
  
  
		  </div>
	  </div>
  
  	  
</div>


  
  <div id="classes">
	<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Current & Upcoming Classes</h3></div>
	<h4 class="text-center class-time-announcement mt-5 mb-5 col-12">All Class Dates and Times are California Time (<?php echo TIMEZONE; ?>)</h4>
	
	
<?php
	include 'unavailable_workshops.php';	
?>
	
	
	<div class="container-lg container-fluid" id="classes-listings">
		  
		  
		<?php
			$classes_shown = 0;
			foreach ($upcoming_workshops as $wk) {
				
				if (!Wbhkit\is_future($wk['start'])) {
					continue; // skip ones that already started
				}
				//if ($wk['soldout']) {
				//	continue; // skip sold out classes
				//}
				
				
				$classes_shown++; // count how many classes we actually list
				
		?>
  	  <div class="row justify-content-between mt-3">
		
			<div class="col-md-11 classes-listings-class mb-5">
			  <h3 class="my-3 py-3 border-top"><a href="/workshop/view/<?php echo $wk['id']; ?>"><?php echo $wk['title'];?></a></h3>
			  <p><?php
				  if ($wk['soldout']) { echo "<span class=\"text-danger\">Sold Out!</span> - ";  } 
				  echo $wk['notes']; 
				  ?></p>
			  <p class="class-time-info">Starting <?php echo $wk['showstart']; ?> for <?php echo $wk['total_sessions'];?> <?php echo ($wk['total_sessions'] == 1) ? 'week': 'weeks'; ?></p>
				  <p><?php echo $wk['enrolled']; ?> of <?php echo $wk['capacity']; ?> signed up</p>
			  <div class="class-meta d-flex justify-content-between align-items-center mt-4">
				<div class="d-flex class-teacher col-7 mr-0 px-0 align-items-center">
					<?php echo teacher_link($wk['teacher_info']); ?>
					<?php if ($wk['co_teacher_id']) { ?>
						<?php echo teacher_link($wk['co_teacher_info']); ?>
					<?php } ?>  
				</div>			
				
				<span class="class-price">
				  <?php echo $wk['costdisplay']; ?> 
				</span>
				<span class="class-enroll">
					
					<?php if ($wk['soldout']) { ?>
						<span class="text-danger">Sold Out!</span> <a class="btn btn-primary" href="w/workshop/view/<?php echo $wk['id']; ?>" role="button">join wait list</a>
					<?php } elseif ($wk['application']) { ?>
						
						<a class="btn btn-primary" href="/workshop/view/<?php echo $wk['id']; ?>" role="button">Request A Spot</a>
						
					<?php } else { ?>
						<a class="btn btn-primary" href="/workshop/view/<?php echo $wk['id']; ?>" role="button">Enroll</a>
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
<div class="row"><img src="/images/htbtgioe_cover.jpg" class="col-sm-12 col-md-3 align-self-start mb-2" />
	<p class="col-sm-12 col-md-9">If the workshops are sold out, you could buy "How to Be the Greatest Improviser on Earth" written by Will Hines, the founder of this school. Print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>.</p></div>
  </div>
  
  
  

 <!-- check your email modal -->
 <div class="modal" tabindex="-1" role="dialog" id="checkYourEmail">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Check your email, <?php echo $email; ?>!</h5>
         		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
 			<p>We JUST sent an email to you at <b><?php echo $email; ?></b>. In that email is a link to log in. Click that link!</p>
 			<p>If you didn't get an email, refresh the home page and try again.</p>
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
       </div>
     </div>
   </div>
 </div> 
  
</main>




