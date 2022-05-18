<?php
function teacher_link($tinfo) {
  return "
	  <a href='/teachers/view/{$tinfo['id']}'><img style='width: 50px; height: 50px' class='clearfix float-start mx-2 teacher-image align-self-center' src='".\Teachers\get_teacher_photo_src($tinfo['user_id'])."' alt='Teacher Name'></a>
		<p class='mt-2 mb-0 teacher-name'><a class='text-decoration-none text-muted' href='/teachers/view/{$tinfo['id']}'> {$tinfo['nice_name']}</a></p>";	
}	
	

function class_row(array $wk) {

	global $u;
	
	$html = '';
	
	$html .= '<div class="row justify-content-between my-2 py-2 border-bottom">';
	$html .= '<div data-classid="'.$wk['id'].'" class="col-md-11 classes-listings-class">';
		
		$html .= \Workshops\print_tags($wk);

		$html .= '<h2 class="mt-3"><a class="text-decoration-none text-dark" href="/workshop/view/'. $wk['id'] .'">'. $wk['title'] . '</a></h2>';
			
		// class meta info
		$html .= "<div class='d-flex row mb-2 text-muted'>";

			// teacher
			$html .= "<div class='col-sm-3'>".teacher_link($wk['teacher_info']);
			if ($wk['co_teacher_id']) { $html .= teacher_link($wk['co_teacher_info']); } 
			$html .= "</div>";
	
			
			//time, sessions, money
			$html .= "<div class='col-sm-3'><i class='bi-calendar text-primary'></i> ".date('D M j', strtotime($wk['start_tz'])).', '.\Wbhkit\friendly_time($wk['start_tz'])." ({$u->fields['time_zone_friendly']})</div>
			<div class='col-sm-2'><i class='bi-calendar-range text-primary'></i> {$wk['total_sessions']} ".(($wk['total_sessions'] == 1) ? 'session ': 'sessions')."</div>
			<div class='col-sm-2'><i class='bi-cash text-primary'></i> ".\Workshops\format_cost_display($wk['costdisplay'])."</div>";
			
			// enroll button
			$html .= "<div class='col-sm-2'>";
			if ($wk['soldout']) { 
				$html .= '<span class="text-danger">Sold Out!</span> <a class="btn btn-outline-primary" href="/workshop/view/'.$wk['id'].'" role="button">Wait List</a>';
			} elseif ($wk['application']) { 
				$html .= '<a class="btn btn-primary" href="/workshop/view/'.$wk['id'].'" role="button">Apply</a>';
			} else { 
				$html .= '<a class="btn btn-primary" href="/workshop/view/'.$wk['id'].'" role="button">Enroll</a>';
			}
			$html .= '</div>'; // end of enroll button
			
		$html .= "</div>"; // end of class meta

	$html .= "</div>\n"; // end of classes-listing-class
	$html .= "</div>\n"; // end of whole class row

	return $html;

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
		<p>What's your name? Something like 'Jane Smith' or 'Jane S'.</p>
	<?php echo $userhelper->edit_display_name($u); ?>
		</div>
<?php 		}  ?>	


<!--<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Latest News</h3></div>-->
	
	<div class="container-lg container-fluid mt-3" id="news">



  <div class="container col-xxl-12 px-4 py-5 bg-light">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
      <div class="col-lg-6">
		<figure class="figure">
		  <a href="/teams"><img src="/images/wgis_chaircircle.png" class="figure-img img-fluid rounded" alt="Improv Chair"></a>
		  <figcaption class="figure-caption text-end">art by Nikki Rodriguez</figcaption>
		</figure>
      </div>
      <div class="col-lg-6">
        <h1 class="display-5 fw-bold lh-1 mb-3">Welcome to WGIS</h1>
        <p class="lead">WGIS is a mostly online improv grad school, for people who have learned the basics and want more. Below is a list of <b class='text-primary'>multi-week courses</b> and <b class='text-success'>one-time workshops</b> you can join. </p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
			
			<a class="btn btn-primary btn-lg px-4 me-md-2" href="/classes" role="button">See Classes</a>
			
          <a class="btn btn-outline-secondary btn-lg px-4" href="/about-works" role="button">How It Works</a>
        </div>
      </div>
    </div>
  </div>		
		

  	  
	</div>


  
 <a id="allclasses"></a>
  <div id="classes">

	  
	<!--<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Current & Upcoming Classes</h3></div>-->

	
<?php
	include 'unavailable_workshops.php';	
?>

		<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">Multi-Week Classes</h3></div>

	<div class="container-lg container-fluid" id="classes-listings">
		
		<p class='my-5'>Here's a <a class='text-muted' href='/classes'>compact list</a> of upcoming classes.</p>
		
		
		<div class="my-3 py-3" id="filter-by-container" style="display:none;"> 
		    <h4 class="mt-3" style="display: inline-block;">Filtering By: </h4>
		    <span data-tag="" class="classtag badge bg-light text-dark rounded-pill me-3 border" id="filter-by"></span>
		</div>
		
		<?php
		$mwhtml = '';
		$wkhtml = '';
		foreach ($upcoming_workshops as $wk) {
			
			if (!Wbhkit\is_future($wk['start'])) {
				continue; // skip ones that already started
			}

			if ($wk['total_sessions'] == 1) {
				$wkhtml .= class_row($wk);
			} else {
				$mwhtml .= class_row($wk);
			}
		}
		echo $mwhtml ? $mwhtml : "<h3 class='m-5'>No upcoming multi-week classes right now!</h3>\n";




		?>
	</div> <!-- end of 'classes listings' div-->

		<div class="container-fluid classes-header container-header-banner"><h3 class="container-lg container-fluid">One-Session Workshops</h3></div>
		<div class="container-lg container-fluid" id="classes-listings">

		<?php echo $wkhtml ? $wkhtml : "<h3 class='m-5'>No upcoming workshops right now!</h3>\n"; ?>
		
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




