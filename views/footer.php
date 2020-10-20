<footer class="pt-4 pb-4"><div class="container-lg container-fluid">
	<h3 class="mb-3">The World's Greatest Improv School</h3>
	<ul class="nav mb-3">
	   <?php foreach( get_nav_items() as $nav_item ){ ?>
	   <li class="nav-item"> <a class="nav-link" href="<?php echo $nav_item['href'] ?>"><?php echo $nav_item['title'] ?></a> </li>
	   <?php } ?>
	</ul>
	<div class="footer-colophon row justify-content-between mb-3">
		<div class="col-lg-7 col-sm-12">
		Send questions to Will Hines - <a href="mailto:whines@gmail.com">whines@gmail.com</a> | <a href="privacy.php">Privacy Policy</a>
		</div>
		<div class="col-lg-5 col-sm-12">
		Site uses: <a href="http://www.php.net/">PHP</a> / <a href="http://www.mysql.com/">MySQL</a> / <a href="http://www.getbootstrap.com/">Bootstrap</a> / <a href="http://useiconic.com/open">Open Iconic</a>
		</div>
	</div>
</div></footer>
<!-- Login Modal -->
<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="Login Modal Dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title">Two Ways to Login</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
	   <div>
		 <h6>We email you a link...</h6>
		 <form id="log_in" action="/index.php" method="post" novalidate="">
					  <input type="hidden" name="ac" value="link">
				  <div class="form-group form-inline"><label for="email">Email</label>
				  <input class="form-control" type="email" id="email" name="email" value="" placeholder="something@something.com" required="" aria-describedby="emailHelpBlock"><small id="emailHelpBlock" class="form-text">We will send you a email, click the link there.</small>
				  </div>
				  <button type="submit" class="btn btn-primary">Log In</button>
		  </form>
	  </div>
	  
	  <hr />
		<div>
		<h6>Sign in Via Google...</h6>
		
		<div id="google-signinbutton" class="g-signin2" data-onsuccess="onSignIn" data-gapiscan="true" data-onload="true"><div style="height:36px;width:120px;" class="abcRioButton abcRioButtonLightBlue"><div class="abcRioButtonContentWrapper"><div class="abcRioButtonIcon" style="padding:8px"><div style="width:18px;height:18px;" class="abcRioButtonSvgImageWithFallback abcRioButtonIconImage abcRioButtonIconImage18"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="18px" height="18px" viewBox="0 0 48 48" class="abcRioButtonSvg"><g><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"></path><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"></path><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"></path><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"></path><path fill="none" d="M0 0h48v48H0z"></path></g></svg></div></div><span style="font-size:13px;line-height:34px;" class="abcRioButtonContents"><span id="not_signed_in6mrttf79lf8l">Sign in</span><span id="connected6mrttf79lf8l" style="display:none">Signed in</span></span></div></div></div>
		
	   <a href="#"> Want to sign-out of Google? (Click here)</a>
	   
		</div>

	  </div>
	  <div class="modal-footer">
	  </div>
	</div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="dist/js/vendor/jquery.slim.min.js"><\/script>')</script><script src="dist/js/bootstrap.bundle.min.js"></script>
</html>