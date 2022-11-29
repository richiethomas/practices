<?php
if (!isset($sort)) { $sort = null; }
if (!isset($needle)) { $needle = null; }

$form_action = isset($form_action) ? $form_action :  '/admin-search/search';
echo "<form action ='$form_action' method='post'>\n";
$search = 1;
include 'assets/ajax/search_box.php';
?>
	<div class="form-group">
	<label for="search-box" class="form-label">Email address</label>
	<input type="text" class="form-control" id="search-box" name="needle" autocomplete="off" value="<?php if ($needle) { echo $needle; } ?>">
	<div id="suggesstion-box"></div>
	</div>
<?php
echo \Wbhkit\hidden('sort', 'n');
//$search_opts = array('n' => 'by name', 't' => 'by total classes', 'd' => 'by date registered');
//echo "Sort by: ".Wbhkit\radio('sort', $search_opts, $sort);
?>
<div ><?php echo Wbhkit\submit('search'); ?></div>
</form>
