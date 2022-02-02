<?php
function teacher_link($tinfo) {
  echo "
	  <a href='/teachers/view/{$tinfo['id']}'><img class='mx-2 teacher-image align-self-center' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."' alt='Teacher Name'></a>
	<div class=''>
		<!--h6 class='mt-0 mb-0 teacher-label text-muted'>Teacher</h6-->
		<p class='mt-0 mb-0 teacher-name'><a class='text-decoration-none text-muted' href='/teachers/view/{$tinfo['id']}'> {$tinfo['nice_name']}</a></p>
	</div>";	
}	
	
?>	

<main>

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
			    <dd class="col-sm-9">We have online house teams! Every <?php
					
					$ts1 = \Wbhkit\convert_tz("January 3 2022 11am", $u->fields['time_zone'], 'l ga');
					$ts2 = \Wbhkit\convert_tz("January 3 2022 5pm", $u->fields['time_zone'], 'l ga');
					echo "$ts1 and $ts2 ({$u->fields['time_zone_friendly']})";
					
					?> <a href="/teams">See more info here</a>.</dd>
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
	<!--<h4 class="text-center class-time-announcement mt-5 mb-5 col-12">All Class Dates and Times are California Time (<?php echo TIMEZONE; ?>)</h4>-->
	
<?php
	include 'unavailable_workshops.php';	
?>
	
	
	<div class="container-lg container-fluid" id="classes-listings">
		<div class="my-3 py-3" id="filter-by-container" style="display:none;"> 
		    <h4 class="mt-3" style="display: inline-block;">Filtering By: </h4>
		    <span data-tag="" class="classtag badge bg-light text-dark rounded-pill me-3 border" id="filter-by"></span>
		</div>
		
		
		<?php
		$classes_shown = 0;
		foreach ($upcoming_workshops as $wk) {
			
			if (!Wbhkit\is_future($wk['start'])) {
				continue; // skip ones that already started
			}
			$classes_shown++; // count how many classes we actually list
		?>
	
				
  	  <div class="row justify-content-between my-3 py-3 border-top">
			<div data-classid="<?php echo $wk['id']; ?>" class="col-md-11 classes-listings-class">
				
				<?php
				echo \Workshops\print_tags($wk);
				?>
				
				<h2 class="mt-3"><a class='text-decoration-none text-dark' href="/workshop/view/<?php echo $wk['id']; ?>"><?php echo $wk['title'];?></a></h2>
				<?php 
				
				if (substr($wk['costdisplay'], 0 ,1) == '$') {
					$wk['costdisplay'] = substr($wk['costdisplay'],1); // remove leading $
				}
					
				echo "
					
					<div class='row mb-4 text-muted'>
				
					<div class='col-sm-2'><i class='bi-cash text-primary'></i> {$wk['costdisplay']}</div>
					<div class='col-sm-2'><i class='bi-calendar text-primary'></i> ".date('D M j', strtotime($wk['start_tz']))."</div>
 					<div class='col-sm-2'><i class='bi-clock text-primary'></i> ".\Wbhkit\friendly_time($wk['start_tz'])." ({$u->fields['time_zone_friendly']})</div>
					<div class='col-sm-3'><i class='bi-calendar-range text-primary'></i> {$wk['total_sessions']} ".(($wk['total_sessions'] == 1) ? 'session ': 'sessions')."</div>";
					
					echo "</div>";
					
				?>


				<div class="class-meta d-flex justify-content-between align-items-center">
				<div class="d-flex class-teacher col-7 mr-0 px-0 align-items-center">
					<?php echo teacher_link($wk['teacher_info']); ?>
					<?php if ($wk['co_teacher_id']) { ?>
						<?php echo teacher_link($wk['co_teacher_info']); ?>
					<?php } ?>  
				</div>
				<span class="class-enroll">
					<?php if ($wk['soldout']) { ?>
						<span class="text-danger">Sold Out!</span> <a class="btn btn-outline-primary" href="/workshop/view/<?php echo $wk['id']; ?>" role="button">Join Wait List</a>
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

<script>
    function filterByTag(tag) {
        let classesDivs = document.getElementById('classes-listings').children;
        classesDivs = Array.from(classesDivs);
        classesDivs.forEach(classDiv => {
            // If tag is empty string or classDiv has a span with tag 
            if (tag === '' || classDiv.querySelector(`span[data-tag="${tag}"]`) !== null) {
                classDiv.style.display = '';
            } else {
                classDiv.style.display = 'none';
            }
        });
    }


    let xIcon = `<i class="bi bi-x"></i>`;

    function toggleFilter(tag) {
        let filterDiv = document.getElementById('filter-by-container');
        let filterButton = document.getElementById('filter-by');

        filterButton.innerHTML = `${xIcon} ${tag.toUpperCase()}`;
        if (tag === '') {
            filterDiv.style.display = 'none';
            return;
        }
        filterDiv.style.display = '';
    }


    document.addEventListener("click", function (e) {
        if (e.target.classList.contains("classtag")) {
            let tag = e.target.dataset.tag;
            filterByTag(tag);
            toggleFilter(tag);
        }
    }); 
</script>

  
	</div> <!-- end of 'classes listings' div-->
</div> <!-- end of 'classes' div -->
  
  <div id="newsletter-signup" class="pt-4 pb-4 mb-5">
	<div class="container">
	  <h3 class="">Mailing List</h3>
	  <div class="row">
		<div class="col-lg-6 col-sm-12">
		  <p>If you want to know about classes the minute the go online, join the mailing list.</p>
			<p>You are NOT automatically put on the mailing list when you take a workshop.</p>
		  </div>
		  <div class="col-lg-6 col-md-8 col-sm-12">
			 <form action="https://willhines.us8.list-manage.com/subscribe/post?u=881f841fbb8bf66576e6e66cf&amp;id=43b29422a0" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
				  
			  <div class="form-group">
				  
				<input type="email" class="form-control" id="mce-EMAIL" name="EMAIL" placeholder="Email">
								
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
<div class="row"><img src="/images/htbtgioe_cover.jpg" class="col-sm-12 col-md-3 align-self-start mb-2" alt="How To Be The Greatest Improviser On Earth" />
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




