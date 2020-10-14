<div class="row">
	<div class="col-sm-6">
<h1>Streaming to YouTube</h1>
<p>Back to <a href="admin_teachers.php">teachers page</a></p>

<h2 class="mt-4">Set Up Zoom</h2>
<ol>
	<li>You only have to do this once. </li>
	<li>Log in to Zoom at http://www.zoom.us/</li>
	<li>On the left, under "Personal," pick "Settings"</li>
	<li>Scroll way down. Turn on "Allow live streaming meetings"</li>
	<li>Under that, check "YouTube"</li>
	<?php echo img_link("assets/ay_stream/ay_zallow.png"); ?>
</ol>

<h2 class="mt-4">Streaming A Zoom Session to YouTube</h2>
<ol>
	<li>This part, you have to do every time.</li>
	<li>In your zoom meeting, in the toolbar at the bottom, click "More"</li>
	<li>A menu pops up, click "Live on YouTube"</li>
	<?php echo img_link("assets/ay_stream/ay_startstream.png"); ?>	
	<li>Now, a "Broadcast Zoom" screen will appear in your web browser. Make sure you're connected to WG Improv School (see pics below)</li>
	<?php echo img_link("assets/ay_stream/ay_broadcast.png"); ?>
	<?php echo img_link("assets/ay_stream/ay_settitle.png"); ?>
	<li>If you're NOT connected to WG Improv School -- click "Not me"
		<ul>
			<li>You'll go to a "choose an account" screen. Pick YOUR google account.</li>
			<?php echo img_link("assets/ay_stream/ay_ytaccount.png"); ?>
			<li>Now you'll go to "pick an account or brand account" -- pick "WG Improv School" - you will return to "Broadcast Zoom" page connected to WG Improv School</li>
			<?php echo img_link("assets/ay_stream/ay_pickbrand.png"); ?>
		</ul></li>
		
	</li>
	<li>Once you ARE on the "Broadcast Zoom Meeting" page AND connected to WG Improv School - you're set! Title the stream, leave it as "Public" and click "Go Live." It will start streaming.</li>
	<?php echo img_link("assets/ay_stream/ay_starting.png"); ?>
	<?php echo img_link("assets/ay_stream/ay_started.png"); ?>
	
	
<h2 class="mt-4">Ending the Stream</h2>
<ol>
	<li>Go to the YouTube page showing your video.</li>
	<li>Upper right: click the "create" button (with a little camera icon). A drop down menu appears. Select "Go Live."</li>
<li>You'll see a list of videos. The top one should be your video that was streaming. Click it (title or thumbnail).</li>
<li>On the next screen, at the top right, you'll see "End stream" -> click that.</li>
	<?php echo img_link("assets/ay_stream/ay_endstream.png"); ?>
	<li>That's it!</li>
	
</ol>	
	
</div></div>


<?php
	
function img_link($src) {
	return "<a href=\"$src\"><img class=\"img-fluid border m-3\" src=\"$src\"></a>";
}
	
?>
