<div class="row">
	<div class="col-sm-6">

<h1>Shows</h1>
	

<h2>Existing Shows</h2>
<ul>
<?php
foreach ($shows as $sh) {
	echo "<li><a href=\"admin_shows.php?show_id={$sh['id']}\">{$sh['nice_start']} - {$sh['title']}</a></li>\n";
}
?>
</ul>

<?php if ($s['id']) { ?>
<h2>A particular show</h2>
<?php } ?>


</div>

<div class="col-sm-6">
<?php 
echo Shows\get_show_form_fields($s);
?>	

</div>


</div>