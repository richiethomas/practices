<script>
.feature-icon {
  width: 4rem;
  height: 4rem;
  border-radius: .75rem;
}

.icon-link > .bi {
  margin-top: .125rem;
  margin-left: .125rem;
  fill: currentcolor;
  transition: transform .25s ease-in-out;
}
.icon-link:hover > .bi {
  transform: translate(.25rem);
}

.icon-square {
  width: 3rem;
  height: 3rem;
  border-radius: .75rem;
}

.text-shadow-1 { text-shadow: 0 .125rem .25rem rgba(0, 0, 0, .25); }
.text-shadow-2 { text-shadow: 0 .25rem .5rem rgba(0, 0, 0, .25); }
.text-shadow-3 { text-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .25); }

.card-cover {
  background-repeat: no-repeat;
  background-position: center center;
  background-size: cover;
}

.feature-icon-small {
  width: 3rem;
  height: 3rem;
}
</script>
<?php
function format_cost_display(string $cd) {
	if (substr($cd, 0 ,1) == '$') {
		$cd = substr($cd,1); // remove leading $
	}
	if ($cd == 'Pay what you can') {
		$cd = 'donation';
	}
	return $cd;
}

function teacher_link_minimal($tinfo) {

    return "<div class='clearfix'><a href='/teachers/view/{$tinfo['id']}'><img style='width: 40px; height: 40px; border-radius: 50%' class='clearfix float-start mx-2 align-self-center' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."' alt='".htmlspecialchars($tinfo['display_name'], ENT_QUOTES)."'></a> <a class='text-decoration-none text-muted' href='/teachers/view/{$tinfo['id']}'> {$tinfo['nice_name']}</a></div>";	
	
	
}
	
function class_row_minimal(Workshop $wk) {
	
	global $u;
	
	$html = "     <div class='row my-3'>\n";
		
	$html .= "          <div class='col-3'><a href='/workshop/view/". $wk->fields['id'] ."'>". $wk->fields['title'] . "</a><br><span class='text-muted'><small>{$wk->fields['time_summary']}</small></span></div>\n";
		
	$html .= "          <div class='col-3'>".teacher_link_minimal($wk->teacher);
	if ($wk->fields['co_teacher_id']) { $html .= teacher_link_minimal($wk->coteacher); } 
	$html .= "</div>\n";
	
	$html .= "          <div class='col-3'>".date("D M j", strtotime($wk->fields['start_tz'])).' '.Wbhkit\friendly_time($wk->fields['start_tz'])."</div>\n";
	$html .= "          <div class='col-3'>".format_cost_display($wk->fields['costdisplay']).($wk->fields['soldout'] ? " - <span class='text-danger'>Sold Out</span>" : '')."</div>\n";
	$html .= "     </div>\n";
	return $html;
}
	
	
?>	


<main>

<?php if ($link_email_sent_flag) { ?>	
<script type="text/javascript">
	
document.addEventListener('DOMContentLoaded', () => {
	const em = new bootstrap.Modal('#checkYourEmail');
	em.show();
});

</script>	
<?php } ?>

<?php if ($u->logged_in() && !$u->fields['display_name']) { ?>	
		<div class="alert alert-info" role="alert">
		<p>Please enter your first and last name:</p>
	<?php echo $userhelper->edit_display_name($u); ?>
		</div>
<?php }  ?>	

<!-- heading -->
<div class="container-lg container-fluid mt-3" id="news">
  <div class="container col-xxl-12 px-4 py-2">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
      <div class="col-lg-6">
		<figure class="figure">
		  <a href="/teams"><img src="/images/live_cf_jump.jpg" class="figure-img img-fluid rounded" alt="Improv Shenanigans"></a>
		  <figcaption class="figure-caption text-end">Clubhouse Fridays / photo by Andrew Sproge</figcaption>
		</figure>
      </div>
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold lh-1 mb-3">World's Greatest Improv School</h1>
		<h2>Come Get Good</h2>
        <p class="lead">WGIS (the World's Greatest Improv School) loves long-form improv, and wants you to be the best improviser you can be. Get in the flow and find your voice.<br><br>Now featuring 6-week core courses (in-person and online) to get you to improv mastery fast.</p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
			
			<a class="btn btn-primary btn-lg px-4 me-md-2" href="/classes" role="button">See Classes</a>
			
          <a class="btn btn-outline-secondary btn-lg px-4" href="/about-works" role="button">How It Works</a>
        </div>
      </div>
    </div>
  </div>		
</div>

<!-- in person show cards -->
  <div class="container px-4 py-2" id="custom-cards">
      <h2 class="pb-2 border-bottom">In-Person Shows</h2>
	  
    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
      <div class="col">
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('/images/live_aj2.jpg');">
          <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
            <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><a href='/shows#fridays' class='link-light text-decoration-none'>Club House Fridays</a></h3>
			<h4>2 house teams + teacher set</h4>
            <ul class="d-flex list-unstyled mt-auto">
              <li class="d-flex align-items-center me-3">
				  <i class="bi-geo-fill me-2"></i>
                <small><a href='https://clubhouseimprov.com/' class='link-light'>The Clubhouse</a></small>
              </li>
              <li class="d-flex align-items-center">
				  <i class="bi-calendar3 me-2"></i>
                <small>7pm Fridays</small>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('/images/live_playbyplay2.jpg');">
          <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
            <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><a href='/shows#tuesdays' class='link-light text-decoration-none'>Broad Water Tuesdays</a></h3>
			<h4>Teams, Tourneys, Jams</h4>
            <ul class="d-flex list-unstyled mt-auto">
              <li class="d-flex align-items-center me-3">
				  <i class="bi-geo-fill me-2"></i>
                <small><a href='https://www.thebroadwaterla.com/second-stage' class='link-light'>Broadwater Second Stage</a></small>
              </li>
              <li class="d-flex align-items-center">
				  <i class="bi-calendar3 me-2"></i>
                <small>7, 8:30, 10pm Tuesdays</small>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('/images/cf_partyhorses.png');">
          <div class="d-flex flex-column h-100 p-5 pb-3 text-shadow-1">
            <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold"><a href='/shows#teams' class='link-light text-decoration-none'>House Teams</a></h3>
			<h4>Every Friday</h4>
            <ul class="d-flex list-unstyled mt-auto">
              <li class="d-flex align-items-center me-3">
				  <i class="bi-geo-fill me-2"></i>
                <small><a href='https://clubhouseimprov.com/' class='link-light'>The Clubhouse</a></small>
              </li>
              <li class="d-flex align-items-center">
				  <i class="bi-calendar3 me-2"></i>
                <small>7pm Fridays</small>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>



<!-- class stuff -->
  <div class="container px-4 pt-5">
      <h2 class="pb-2 border-bottom">WGIS Core Classes</h2>
	  
    <div class="row row-cols-1 row-cols-md-2 align-items-md-center g-5 py-5">
      <div class="d-flex flex-column align-items-start gap-2">
        <h3 class="fw-bold">6 Weeks, 2+ Shows</h3>
        <p class="text-muted">Our four level program gets you to improv expertise fast. Each level is now 6 weeks, plus at least 2 shows. Get on your feet and get good. Prices range from $240-300, pending on show number.</p>
        <a href="/classes" class="btn btn-primary btn-lg">See upcoming classes</a>
      </div>
      <div class="row row-cols-1 row-cols-sm-2 g-4">
        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-emoji-smile"></i>
          </div>
          <h4 class="fw-semibold mb-0">1: Intro to Improv</h4>
          <p class="text-muted">Learn the basics: saying yes, be real, get in the flow.</p>
        </div>

        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-dice-3"></i>
          </div>
          <h4 class="fw-semibold mb-0">2: Game Of The Scene</h4>
          <p class="text-muted">Play the comedy, not the plot. Learn to pull ideas from an opening.</p>
        </div>

        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-diagram-3"></i>
          </div>
          <h4 class="fw-semibold mb-0">3: Second Beats</h4>
          <p class="text-muted">Find the game, do it again. Unlock the power of knowing the game of the scene.</p>
        </div>

        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-diagram-3-fill"></i>
          </div>
          <h4 class="fw-semibold mb-0">4: Harold</h4>
          <p class="text-muted">Learn group games and third beats to complete your knowledge of the Harold.</p>
        </div>
		
      </div>
    </div>
  </div>

  
 <a id="allclasses"></a>
  <div id="classes">
	
<?php
	include 'unavailable_workshops.php';	
	
	$inperson_html = '';
	$online_html = '';
	foreach ($upcoming_workshops as $wk) {
		
		if (!Wbhkit\is_future($wk->fields['start']) && 
		(!strpos(strtolower($wk->fields['title']), 'glendale'))
		) {
			continue; // skip ones that already started
		}

		if (in_array('inperson', $wk->fields['tags_array'])) {
			$inperson_html .= class_row_minimal($wk);
		} else {
			$online_html .= class_row_minimal($wk);
		}
	}	
	
	
?>
	<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Upcoming Online Classes</h3></div>

	<div class="container-lg container-fluid">
		<?php

		echo "<div class='row m-2'><div class='col-6'>&nbsp;</div><div class='col-3 fw-bold'>Times in (".$u->fields['time_zone_friendly'].")</div><div class='col-3'>&nbsp;</div></div>";

		echo $online_html ? $online_html : "<h3 class='m-5'>No upcoming online classes right now!</h3>\n";

?>
	</div> <!-- end of 'classes listings' div-->

		<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Upcoming In Person Los Angeles Classes</h3></div>
		<div class="container-lg container-fluid">

		<?php 

		echo "<div class='row m-2'><div class='col-6'>&nbsp;</div><div class='col-3 fw-bold'>Times in (".$u->fields['time_zone_friendly'].")</div><div class='col-3'>&nbsp;</div></div>";
		echo $inperson_html ? $inperson_html : "<h3 class='m-5'>No upcoming in person Los Angeles classes right now!</h3>\n"; ?>
		
		</div>

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





