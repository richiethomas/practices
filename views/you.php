<?php

echo "<h1>Your Profile <small><a href='index.php'>(return to front)</a></small></h1>\n";
echo "<p>Here you can change your email, display name, text preferences and see what workshops you've taken.</p>";

echo "<div class='row justify-content-center'><div class='col-sm-6'>\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Change Your Email</h3>\n";
echo Users\edit_change_email($u);
echo "</div></div> <!-- end of card -->\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Change Your Display Name</h3>\n";
echo Users\edit_display_name($u);
echo "</div></div> <!-- end of card -->\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";	
echo "<h3>Change Your Text Preferences</h3>\n";
echo Users\edit_text_preferences($u);	
echo "</div></div> <!-- end of card -->\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Log Out</h3>\n";
echo "<a class='btn btn-outline-primary' href=\"index.php?ac=lo\"><span class=\"oi oi-account-logout\" title=\"account-logout\" aria-hidden=\"true\"></span> log out of willhinesimprov.com</a>";
echo "</div></div> <!-- end of card -->\n";

echo "</div></div> <!-- end of 6 col row -->\n";
	
echo "<div class='row mb-4'><div class='col m-3'>\n";
echo "<h2>Your Workshops</h2>\n";
echo $transcript; 
echo "</div></div> <!-- end of transcript col and row -->\n";

?>
