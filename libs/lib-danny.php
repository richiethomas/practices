<?php
	
// Declare the global School class to help with displaying the teacher and workshop info for this demo.
$school = new School();

// Helper Functions

// Layout and Presentation






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

// Data Set Up Functions //

function get_classes(){
	
	$faculty = new Faculty();
		
	$classes_array = array();
	$classes_array[] = new Workshop( "Getting Out of Your Head About Game", "Friday, August 14 at 11am", "6", "$220.USD", $faculty->teachers['brandon'] , 'The Game of the Scene should come naturally out of improvisers listening, making choices and committing hard to their realities. By focusing on these fundamentals students will come away from this class with habits that make finding and playing a game feel less effortful and more spontaneous.');
		$classes_array[] = new Workshop( "Create A Character and Film It", "Friday, August 14 at 11am", "6", "$220.USD", $faculty->teachers['kat'] ,'Two weeks of two hour classes, plus a character monologue/scene screening. Show up with an idea for a character or two, we’ll find the fun, expand the world, and heighten your idea. You’ll have some homework. Then in class two, we’ll work out your character monologue or short scene. Then you’ll have a week or two to shoot your monologue or scene (on your phone is fine!) and we’ll do a screening.' );	
		$classes_array[] = new Workshop( "Create A Character", "Thursday, August 27 at 6pm", "6", "$220.USD", $faculty->teachers['kat'] , 'The Game of the Scene should come naturally out of improvisers listening, making choices and committing hard to their realities. By focusing on these fundamentals students will come away from this class with habits that make finding and playing a game feel less effortful and more spontaneous.');	
		$classes_array[] = new Workshop( "The Improvised Play", "Tuesday, September 8 at 3pm", "6", "$220.USD", $faculty->teachers['brandon'] , 'This class will work towards creating fully improvised 30 minute plays. Rather than focusing on the tropes of specific genres we will focus on acting and basic dramatic structure. As in a monoscene the comedy will come from clear character Games but these multi-scene plays will also be built around the characters’ objectives, obstacles and actions. Although the plays will almost always be funny we’ll make emotional honesty and commitment the priority. The class will culminate with us improvising two mind-blowing one-act plays live on YouTube. Some outside of class preparation will be required.');
	return $classes_array;	
}

function get_teachers(){
	$teachers_array = array();
	$teachers_array['beth'] = new Teacher('Beth Appel', 'Beth Appel is a performer, writer, and director living in Los Angeles. Beth spent several years as Artistic Director of UCBTLA and is a proud member of the improv team CARDINAL REDBIRD. Beth wrote and starred in the long-running solo show, BETH APPEL: SEXIEST WOMAN ALIVE, and she has written, directed and performed in countless other sketch and improv shows at UCB on both coasts (East and West). You may have seen her on IFC, MTV, TruTV, in various movies and web series, or, if you live in Germany or Switzerland, in a Volkswagen commercial with fellow heartthrob Robbie Williams.');
	$teachers_array['brandon'] = new Teacher('Brandon Gardner', 'Brandon is a writer, performer and improv teacher in Los Angeles. He has been an instructor at UCB since 2009 where he holds the record for most Intro to Game classes taught (116 and counting). The dude loves teaching Game. DAVID, a short film he co-wrote with Zach Woods, was an Official Selection of the 2020 Cannes Film Festival. He has been trying to get a manager for the last eight years.');
	$teachers_array['kat'] = new Teacher('Kat Palardy', "Kat most recently used her characters to voice animated roles for Nickelodeon, Amazon, Dreamworks and several Netflix shows. She was named one of &quot;Just For Laughs: New Faces of comedy&quot;  for her Characters, and loves doing stand up and songs as her trashy 70’s lounge act: Jan Salami! \n She came up doing sketch, improv, and musical theatre in Chicago, NYC, and in LA she had the time of her life on UCB Maude sketch team Bombardier. This got her started with teaching and coaching characters which has really satisfied her maternal instincts. Additional acting credits include &quot;New Girl,&quot;  &quot;Superstore,&quot; &quot; Brooklyn 99,&quot; &quot;The Pete Holmes Show,&quot; &quot;Adam Ruins Everything,&quot; Rob Heubel and Paul Scheer’s &quot; Drive Share,&quot; and more.");
	$teachers_array['ronnie'] = new Teacher('Ronnie Adrian', 'Originally from South Carolina, Ronnie is now a performer based out of Los Angeles. He performs in cities all around the country and you can often catch him with his various groups most notably The Dragons and White Women. He does other things besides comedy but this is a comedy bio so he’ll keep it to himself.');
	$teachers_array['will'] = new Teacher('Will Hines','Will has been teaching long-form improv since 2004 at the Upright Citizens Brigade Theatre, at both its New York and Los Angeles branches. He’s published a best-selling book on improv called How to Be The Greatest Improviser on Earth. He’s also a working comedic actor and has appeared on many terrific television programs. Pretty cool! He’s also the founder of this web site.');
	return $teachers_array;
}

class School {
	
	public $teachers;
	public $workshops;
	
	function __construct(){
		$this->teachers = get_teachers();
		$this->workshops = get_classes();
	}	
}


class Workshop {

	public $title;
	public $starting_date;
	public $duration_in_weeks;
	public $price;
	public $teacher;
	public $description;
	
	function __construct($title, $starting_date, $duration_in_weeks, $price, $teacher, $description) {
				$this->title = $title;
				$this->starting_date = $starting_date;
				$this->duration_in_weeks = $duration_in_weeks;
				$this->price = $price;
				$this->teacher = $teacher;
				$this->description = $description;
				$this->enrollment = array('capacity' => 8, 'enrolled' => '8','paid'=>'7', 'waiting'=>1 );
	}
	
	public function get_workshop_time_duration(){
		return date( "h:i:s A" , strtotime($this->starting_date));
	}


	public function get_workshop_date_duration(){
		return date( "F j, Y" , strtotime($this->starting_date));
	}
	
	public function get_workshop_enrollment_info_for_table(){
		$return_array = array();
		$return_array[] = $this->enrollment['paid'] . " Paid";
		$return_array[] = $this->enrollment['paid'] . "/" . $this->enrollment['capacity'] .  " Enrolled";
		$return_array[] = $this->enrollment['waiting'] . " Waiting";
		return implode("<br/>",$return_array);
	}
}

class Faculty {
	public $teachers;
	
	function __construct(){
		$this->teachers = get_teachers();
	}
	
}

class Teacher {
	
	public $name;
	public $bio;
	
	function __construct($name, $bio){
				$this->name = $name;
				$this->bio = $bio;
	}
	
	public function get_teacher_image_filename(){
		$first_name_lowercase = strtolower( strtok($this->name, " ") );
		return $filename = "teacher_images/teacher_$first_name_lowercase.jpg";
	}
	
}

function get_nav_items(){
	$nav_items = array();
	//$nav_items[] = array('title' => "Classes", "href" => "classes.php");
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