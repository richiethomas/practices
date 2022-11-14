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

function teacher_link($tinfo) {
  return "
	  <a href='/teachers/view/{$tinfo['id']}'><img style='width: 50px; height: 50px' class='clearfix float-start mx-2 teacher-image align-self-center' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."' alt='Teacher Name'></a>
		<p class='mt-2 mb-0 teacher-name'><a class='text-decoration-none text-muted' href='/teachers/view/{$tinfo['id']}'> {$tinfo['nice_name']}</a></p>";	
}	
	

function class_row(Workshop $wk) {

	global $u;
	
	$html = '';
	
	$html .= '<div class="row justify-content-between my-2 py-2 border-bottom">';
	$html .= '<div data-classid="'.$wk->fields['id'].'" class="col-md-11 classes-listings-class">';
		
		$html .= $wk->print_tags();

		$html .= '<h2 class="mt-3"><a class="text-decoration-none text-dark" href="/workshop/view/'. $wk->fields['id'] .'">'. $wk->fields['title'] . '</a></h2>';
			
		// class meta info
		$html .= "<div class='d-flex row mb-2 text-muted'>";

			// teacher
			$html .= "<div class='col-sm-3'>".teacher_link($wk->teacher);
			if ($wk->fields['co_teacher_id']) { $html .= "<br>".teacher_link($wk->coteacher); } 
			$html .= "</div>";
	
			
			//time, sessions, money
			$html .= "<div class='col-sm-3'><i class='bi-calendar text-primary'></i> ".date('D M j', strtotime($wk->fields['start_tz'])).', '.\Wbhkit\friendly_time($wk->fields['start_tz'])." ({$u->fields['time_zone_friendly']})</div>
			<div class='col-sm-2'><i class='bi-calendar-range text-primary'></i> {$wk->fields['total_sessions']} ".(($wk->fields['total_sessions'] == 1) ? 'session ': 'sessions')."</div>
			<div class='col-sm-2'><i class='bi-cash text-primary'></i> ".format_cost_display($wk->fields['costdisplay'])."</div>";
			
			// enroll button
			$html .= "<div class='col-sm-2'>";
			if ($wk->fields['soldout']) { 
				$html .= '<span class="text-danger">Sold Out!</span> <a class="btn btn-outline-primary" href="/workshop/view/'.$wk->fields['id'].'" role="button">Wait List</a>';
			} elseif ($wk->fields['application']) { 
				$html .= '<a class="btn btn-primary" href="/workshop/view/'.$wk->fields['id'].'" role="button">Apply</a>';
			} else { 
				$html .= '<a class="btn btn-primary" href="/workshop/view/'.$wk->fields['id'].'" role="button">Enroll</a>';
			}
			$html .= '</div>'; // end of enroll button
			
		$html .= "</div>"; // end of class meta

	$html .= "</div>\n"; // end of classes-listing-class
	$html .= "</div>\n"; // end of whole class row

	return $html;

}
	
	
	
	
?>	

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
		<p>First and last name, as you like to be called</p>
	<?php echo $userhelper->edit_display_name($u); ?>
		</div>
<?php 		}  ?>	


<!--<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Latest News</h3></div>-->
	
	<div class="container-lg container-fluid mt-3" id="news">

  <div class="container col-xxl-12 px-4 py-2">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
      <div class="col-lg-6">
		<figure class="figure">
		  <a href="/teams"><img src="images/live_cf_jump.jpg" class="figure-img img-fluid rounded" alt="Improv Shenanigans"></a>
		  <figcaption class="figure-caption text-end">Clubhouse Fridays</figcaption>
		</figure>
      </div>
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold lh-1 mb-3">World's Greatest Improv School</h1>
		<h2>Come Get Good</h2>
        <p class="lead">WGIS (the World's Greatest Improv School) loves long-form improv, and wants you to be the best improviser you can be. Get in the flow and find your voice.<br><br>In-person classes are in Los Angeles. Online classes are an improv grad school focusing on advanced subjects.</p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
			
			<a class="btn btn-primary btn-lg px-4 me-md-2" href="/classes" role="button">See Classes</a>
			
          <a class="btn btn-outline-secondary btn-lg px-4" href="/about-works" role="button">How It Works</a>
        </div>
      </div>
    </div>
  </div>		
	</div>



  <div class="container px-4 py-2" id="custom-cards">
      <h2 class="pb-2 border-bottom">In-Person Shows</h2>
	  
    <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
      <div class="col">
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('images/live_aj.jpg');">
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
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('images/live_playbyplay.jpg');">
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
        <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('images/cf_partyhorses.png');">
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



  <div class="container px-4 py-5">
      <h2 class="pb-2 border-bottom">Core Classes</h2>
	  
    <div class="row row-cols-1 row-cols-md-2 align-items-md-center g-5 py-5">
      <div class="d-flex flex-column align-items-start gap-2">
        <h3 class="fw-bold">The WGIS Program</h3>
        <p class="text-muted">Our four-level program will take you from total beginner to an well-rounded expert in long-form improv comedy. Our focus is on premise, game-of-the-scene improv but you'll also learn the principles of: agreement, playing it real and focusing on the unusual thing.</p>
        <a href="/classes" class="btn btn-primary btn-lg">New classes announced Thanksgiving</a>
      </div>
      <div class="row row-cols-1 row-cols-sm-2 g-4">
        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-emoji-smile"></i>
          </div>
          <h4 class="fw-semibold mb-0">1: Intro to Improv</h4>
          <p class="text-muted">Learn the basics: saying yes, protecting emotional reality and focusing on the unusual thing.</p>
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
          <h4 class="fw-semibold mb-0">3: Harold Structure</h4>
          <p class="text-muted">Learn the structure of the Harold, a demanding and fun improv form that makes you a complete improviser.</p>
        </div>

        <div class="d-flex flex-column gap-2">
          <div
            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
			<i class="bi-diagram-3-fill"></i>
          </div>
          <h4 class="fw-semibold mb-0">4: Advanced Harold</h4>
          <p class="text-muted">Find your own unique voice inside of the Harold structure.</p>
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
			$inperson_html .= class_row($wk);
		} else {
			$online_html .= class_row($wk);
		}
	}	
	
	
?>
		<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Upcoming Online Classes</h3></div>

	<div class="container-lg container-fluid" id="classes-listings">
		
		
		<div class="my-3 py-3" id="filter-by-container" style="display:none;"> 
		    <h4 class="mt-3" style="display: inline-block;">Filtering By: </h4>
		    <span data-tag="" class="classtag badge bg-light text-dark rounded-pill me-3 border" id="filter-by"></span>
		</div>
		
		<?php

		echo $online_html ? $online_html : "<h3 class='m-5'>No upcoming online classes right now!</h3>\n";




		?>
	</div> <!-- end of 'classes listings' div-->

		<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Upcoming In Person Los Angeles Classes</h3></div>
		<div class="container-lg container-fluid" id="classes-listings">

		<?php echo $inperson_html ? $inperson_html : "<h3 class='m-5'>No upcoming in person Los Angeles classes right now!</h3>\n"; ?>
		
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

 <!-- <div id="buy-the-book" class="container mb-5">
	<h3 class="mb-3">Buy the Book</h3>
<div class="row"><img src="/images/htbtgioe_cover.jpg" class="col-sm-12 col-md-3 align-self-start mb-2" alt="How To Be The Greatest Improviser On Earth" />
	<p class="col-sm-12 col-md-9">If the workshops are sold out, you could buy "How to Be the Greatest Improviser on Earth" written by Will Hines, the founder of this school. Print and digital versions <a href="https://www.amazon.com/dp/0982625723">on Amazon</a>.</p></div>
  </div> -->
 
  
  

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




