<?php
	
// Function to allow SVG code to be displayed inline
function svg_code($svg_name){
	switch($svg_name){
		case 'chevron_lightgray_left':
		?>
		<svg width="6px" height="8px" viewBox="0 0 6 8" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g id="Left-Chevron-Admin-White" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="Path-2" transform="translate(2.000000, 1.000000)" stroke="#FFFFFF" stroke-width="2">
							<polyline id="Path" transform="translate(1.500000, 3.000000) rotate(-180.000000) translate(-1.500000, -3.000000) " points="0 8.8817842e-15 3 3.0181399 0 6"></polyline>
						</g>
					</g>
				</svg>
		<?php
		break;
	}
}

// Display an Admin Box.
// $title - Admin Box Title
// $render_callback_function - function key for the function to render the content area for the admin box.
// $title_toolbar_items (optional) - Array of HTML elements to display in the toolbar area
function admin_box($title, $render_callback_function, $title_toolbar_items = null){ 
	?>
	<div class="admin-box" id="admin-box-<?php  echo trim(strtolower($title), '-'); ?>">
			<div class="admin-box-title">
				<h5><?php echo $title; ?></h5>
				<?php if (!empty($title_options)){
					echo '<div class="admin-box-title-toolbar-items">';
					foreach($title_toolbar_items as $title_toolbar_item){
						echo $title_toolbar_item;
					}
					echo "</div>";
				}?>
			</div>
			<div class="admin-box-content">
				<?php if (!empty($render_callback_function)) call_user_func($render_callback_function, $title)?>
			</div>
		</div>
	<?php	
}

function admin_box_teacher_callback_function($admin_box_title){
	$faculty = new Faculty();
	echo '<div class="row">';
	foreach($faculty->teachers as $teacher){
		?>
		<div class="col teachers_listings-teacher">
			<a href="#" class="teacher-info mb-4">
				<img class="teacher-image align-self-center" src="<?php echo $teacher->get_teacher_image_filename();?>" alt="Teacher Name">
				<h5 class="mt-0 mb-0 teacher-name"><?php echo $teacher->name;?></h5>
			</a>
		</div>
		<?php
	}
	echo '</div>';
}



function get_nav_items(){
	$nav_items = array();
	//$nav_items[] = array('title' => "Classes", "href" => "classes.php");
	//$nav_items[] = array('title' => "Home", "href" => "index.php");
	$nav_items[] = array('title' => "Calendar", "href" => "calendar.php");
	$nav_items[] = array('title' => "Teachers", "href" => "teachers.php");
	$nav_items[] = array('title' => "About", "href" => "about_school.php", 'children' => array(

		array('title' => "School", "href" => "about_school.php"),
		array('title' => "Catalog", "href" => "about_catalog.php"),
		array('title' => "How It Works", "href" => "about_works.php")
	));
	$nav_items[] = array('title' => "Shows / Jams", "href" => "news.php");
	return $nav_items;
}

?>