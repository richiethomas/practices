 <?php if ($page != 'home') {
	 echo "
	 	</article>	
	 	</div>  
	 </main>
";
 }
 ?>

<footer class="pt-4 pb-4"><div class="container-lg container-fluid">
	<h3 class="mb-3">The World's Greatest Improv School</h3>
	<ul class="nav mb-3">
	   <?php foreach( get_nav_items() as $nav_item ){ ?>
	   <li class="nav-item"> <a class="nav-link" href="<?php echo $nav_item['href'] ?>"><?php echo $nav_item['title'] ?></a> </li>
	   <?php } ?>
	</ul>
	<div class="footer-colophon row justify-content-between mb-3">
		<div class="col-lg-7 col-sm-12">
		Send questions to - <a href="mailto:classes@wgimprovschool.com">classes@wgimprovschool.com</a> | <a href="/privacy">Privacy Policy</a>
		</div>
		<div class="col-lg-5 col-sm-12">
		Site uses: <a href="http://www.php.net/">PHP</a> / <a href="http://www.mysql.com/">MySQL</a> / <a href="http://www.getbootstrap.com/">Bootstrap</a> / <a href="http://useiconic.com/open">Open Iconic</a>
		</div>
	</div>
</div></footer>

<!-- Login Modal -->
<div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="login_title" aria-hidden="true">

<script>
$( document ).ready(function(){
		$( "#email" ).on("focus", function() {
			$("#log_in").attr("action", "/home/link");
		});
});
</script>
	
  <div class="modal-dialog modal-dialog-centered" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		  
		<h5 id="login_title" class="modal-title">To Log In, We Email You A Link</h5>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
		<?php echo \Wbhkit\form_validation_javascript('log_in'); ?>
	
		<form id='log_in' action='baloney.php' method='post' novalidate>
		<?php echo \Wbhkit\hidden('when_login', date('c')).
		\Wbhkit\texty('email', '', 'Email', 'something@something.com', 'We will send you an email with a link to click.', 'Must be a valid email you have access to', ' required ', 'email').
		'<input type="checkbox" name="fax_only" value="1" style="display:none !important" tabindex="-1">'.
		\Wbhkit\submit('Send Me An Email'); ?>
		</form>  
	</div>
	</div>
  </div>
</div>
</html>

<?php
if (TIMER) {
	echo show_hrtime();
}
?>