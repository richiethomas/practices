<?php
switch ($v) {
	
	
	case 'faq':
	
		$body .= "<div class='row'><div class='col'>\n";
		$body .= Emails\get_faq();
		$body .= "<p>Just <a href='$sc'>go back to the main page <span class=\"oi oi-home\" title=\"home\" aria-hidden=\"true\"></span></a>.</p>";
		$body .= "</div></div>\n";
		break;	
	
	case 'winfo':
		$body .= "<div class='row'><div class='col'>\n";
		if (Users\logged_in()) {
			$e = Enrollments\get_an_enrollment($wk, $u);
			
			$enroll_link = "$sc?ac=enroll&wid={$wk['id']}";
			
			switch ($e['status_id']) {
				case ENROLLED:
					$point = "You are ENROLLED in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case WAITING:
					$point = "You are spot number {$e['rank']} on the WAIT LIST for the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=drop&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>drop</a> it?";
					break;
				case INVITED:
					$point = "A spot opened up in the practice listed below. Would you like to <a class='btn btn-primary' href='$sc?ac=accept&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>accept</a> it, or <a class='btn btn-primary' href='$sc?ac=decline&wid={$wk['id']}&uid={$u['id']}&key={$key}&v=winfo'>decline</a> it?";
					break;
				case DROPPED:
					$point = "You have dropped out of the practice listed below. Would you like to <a class='btn btn-primary'  href='$enroll_link'>re-enroll</a>?";
					break;
				default:
				
					$point = "You are not currenty signed up for the practice listed below. ".
						($wk['type'] == 'soldout' 
						? "It is full. Want to <a class='btn btn-primary' href='$enroll_link'>join the wait list</a>?"
						: "Want to <a class='btn btn-primary' href='$enroll_link'>enroll</a>?");
				
					break;
			}
			if ($wk['type'] == 'past') {
				$point = "This workshop is IN THE PAST.";
			}
			$body .= "<p class='alert alert-info'>$point</p>\n";
			$body .= "<p>Click here to <a href='$sc'> <span class=\"oi oi-home\" title=\"home\" aria-hidden=\"true\"></span> return to the main page</a>.</p>\n";
			$body .= "<hr>";
			$body .= Workshops\get_workshop_info_tabled($wk);
		
		} else {
			$body .= Users\login_prompt();
		}
		$body .= "</div></div> <!-- end of col and row -->\n";
		break;
			
	default:
	
	
		if (Users\logged_in() && !$u['display_name']) {
			$body .= '
				<div class="alert alert-info" role="alert">
				<p>Would you mind entering a real human name? It\'s helpful for people to see who is signed up. Your email isn\'t shown, just this name.</p>
			'.Users\edit_display_name($u).'
				</div>';
		}
		
	
		$body .= "<div class='row mb-md-4'><div class='col-md-12'>
			<div id='login_prompt' class='card border-info bg-light'>
			<div class='card-body'>\n";
	
		if (Users\logged_in()) {
			
			$body .= "<h2 class='card-title'>Welcome";
			if ($u['display_name']) {
				$body .= ", {$u['display_name']}";
			}
			$body .="</h2>\n";
			$body .= "<p>You are logged in as <strong>".
				($u['display_name'] 
				? "{$u['display_name']} ({$u['email']})"
				: "{$u['email']}").
			"</strong></p>";
			
			$body .= '
				  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#nameEmailModal"><span class="oi oi-person" title="person" aria-hidden="true"></span> update name and email</button>
				  <button type="button" class="btn btn-info" data-toggle="modal" data-target="#textModal"><span class="oi oi-phone" title="phone" aria-hidden="true"></span> update text notifications </button>				  
				  <a href="'.$sc.'?ac=lo" class="btn btn-info"><span class="oi oi-account-logout" title="account-logout" aria-hidden="true"></span> log out</a>
				  
				  <div class="modal fade" id="textModal" tabindex="-1" role="dialog" aria-labelledby="textModalLabel" aria-hidden="true">
				    <div class="modal-dialog" role="document">
				      <div class="modal-content">
				        <div class="modal-header">
				          <h5 class="modal-title" id="exampleModalLabel"><span class="oi oi-phone" title="phone" aria-hidden="true"></span> Text Notifications</h5>
				          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				            <span aria-hidden="true">&times;</span>
				          </button>
				        </div>
				        <div class="modal-body">';
						
					$body .= Users\edit_text_preferences($u);

				      $body .= '</div>
				        <div class="modal-footer">
				          <button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="oi oi-circle-x" title="circle-x" aria-hidden="true"></span> Close</button>
				        </div>
				      </div>
				    </div>
				  </div>

				  
				  <div class="modal fade" id="nameEmailModal" tabindex="-1" role="dialog" aria-labelledby="nameEmailModalLabel" aria-hidden="true">
				    <div class="modal-dialog" role="document">
				      <div class="modal-content">
				        <div class="modal-header">
				          <h5 class="modal-title" id="exampleModalLabel"><span class="oi oi-person" title="person" aria-hidden="true"></span> Name and Email</h5>
				          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				            <span aria-hidden="true">&times;</span>
				          </button>
				        </div>
				       	 <div class="modal-body">';
						
						$body .= '<div class="row mb-md-4"><div class="col">'.
								Users\edit_display_name($u).
								'</div></div> <!-- end of col and row -->

								<div class="row mb-md-4"><div class="col">'.
							Users\edit_change_email($u).
								'</div></div> <!-- end of col and row -->	
								</div>
					        <div class="modal-footer">
				          <button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="oi oi-circle-x" title="circle-x" aria-hidden="true"></span> Close</button>
						  	</div>
				      </div>
				    </div>
				  </div>';

		} else {
			$body .= "<h2 class='card-title'>Log In To This Site</h2>\n";
			$body .= "<p>First you must log in. We do that via email.</p>";
			$body .= Users\login_prompt();
		}
		$body .= "</div></div></div></div>\n"; // end two card divs, then column, then row


		// common info
		$body .= '
			<div class="row justify-content-center mb-md-4">
		
			<div class="col col-4">
			<div class="card text-center text-white bg-success">
		      <div class="card-body">
		        <h2 class="card-title"><span class="oi oi-dollar" title="dollar" aria-hidden="true"></span><br>Paying</h2>
		        <p class="card-text">
				Pay in person or with Venmo to whines@gmail.com. On the day of the workshop is fine.</p>
		        <a href="http://venmo.com/willhines?txn=pay&share=friends&amount=25&note=improv%20workshop"  class="btn btn-outline-light">Venmo whines@gmail.com</a>
		      </div> <!-- end of card body -->
		    </div> <!-- end of card -->
		</div> <!-- end of col -->

		<div class="col col-4">
		<div class="card text-center text-white bg-danger">
		      <div class="card-body">
		        <h2 class="card-title"><span class="oi oi-ban" title="ban" aria-hidden="true"></span><br>No Late Drops!</h2>
		        <p class="card-text">Please don\'t drop out late! I mean, if you gotta you gotta, but.. try not to?<br><br><br>&nbsp;</p>
		      </div> <!-- end of card body -->
		    </div> <!-- end of card -->
		</div> <!-- end of col -->
		
		</div> <!-- end of row -->
		
		
		<div class="row justify-content-center mb-md-4">
			
			<div class="col col-4">
			<div class="card text-center text-white bg-info">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-envelope-closed" title="envelope-closed" aria-hidden="true"></span><br>Mailing List</h2>
			        <p class="card-text">You are NOT automatically put on my mailing list for these workshops. If you WANT to be on that mailing list, sign up right here.</p>
			        <a href="http://eepurl.com/c6-T-H" class="btn btn-outline-light">Join Mailing List</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->
			
			
			<div class="col col-4">
			<div class="card text-center text-white bg-warning">
			      <div class="card-body">
			        <h2 class="card-title"><span class="oi oi-question-mark" title="question-mark" aria-hidden="true"></span><br>Common Questions</h2>
			        <p class="card-text">You can be late. You can leave early. Pre-reqs are not enforced. Click below to see other common questions. Or else email Will Hines at w.hines@gmail.com</p>
			        <a href="$sc?v=faq" class="btn btn-outline-light">More Common Questions</a>
			      </div> <!-- end of card body -->
			    </div> <!-- end of card -->
			</div> <!-- end of col -->

			</div> <!-- end of row -->';

			// upcoming workshops
		$body .= "<div class='row mb-md-4'><div class='col'>\n";
		$body .= "<h2>Enroll In A Workshop</h2>\n"; 
		$body .= Workshops\get_workshops_list(0);
		$body .= "</div></div> <!-- end of col and row -->\n";
		
		
		// past workshops
		$body .= "<div class='row mb-md-4'><div class='col'>";
		$body .= "<h2>Your Current/Past Workshops</h2>";
		if (Users\logged_in()) {
			$body .= Enrollments\get_transcript_tabled($u, 0, $page);  
		} else {
			$body .= "<p>You're not logged in, so I can't list your workshops. Log in further up this page.</p>";
		}
		$body .= "</div></div> <!-- end of col and row -->\n";	
		
		break;
}
	
?>