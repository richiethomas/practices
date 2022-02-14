</main>
</html>
<?php
if (TIMER) {
	echo "<!-- ";
	show_hrtime();
	echo "-->\n";
}
?>
<?php
echo "<!-- yep:<br>";
print_r($_COOKIE);
print_r($_SESSION);
echo "-->";
?>