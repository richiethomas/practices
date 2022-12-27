<?php


echo "<h1>Your Profile <small><a href='/'>(return to front)</a></small></h1>\n";
echo "<p>Here you can change your email, display name and see what workshops you've taken.</p>";

echo "<div class='row justify-content-center'><div class='col-sm-6'>\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Change Your Email</h3>\n";
echo $userhelper->edit_change_email($u);
echo "</div></div> <!-- end of card -->\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Update Your Profile</h3>\n";
echo $userhelper->edit_public_attributes($u);
echo "</div></div> <!-- end of card -->\n";

echo "<div class='card my-5 bg-light'><div class='card-body'>\n";
echo "<h3>Log Out</h3>\n";
echo "<a class='btn btn-outline-primary' href=\"/home/lo\"><i class='bi-box-arrow-left'></i> log out of wgimprovschool.com</a>";
echo "</div></div> <!-- end of card -->\n";

echo "</div></div> <!-- end of 6 col row -->\n";

echo "<div class='row mb-4'><div class='col m-3'>\n";
echo "<h2>Your Workshops</h2>\n";
echo $transcript;
echo "</div></div> <!-- end of transcript col and row -->\n";

?>
