<div class="row">
	<div class="col">
		<h2>All Teachers</h2>
		<ul>
			<?php
			foreach ($teachers as $teach) {
				echo "<li><a href=\"admin_teachers.php?tid={$teach['id']}\">{$teach['nice_name']}</a>".($teach['active'] ? '' : ' (inactive)')."</li>\n";
			}
			?>
		</ul>
	</div>
	<div class="col">
		
		<?php if (isset($t['id']) && $t['id']) { ?>
			<h2><?php echo $t['nice_name']; ?> Teacher Info</h2>
			<div class="card"><div class="card-body">
			<h3>Basic Info</h3>
			<?php  echo \Teachers\get_teacher_form($t); ?>
			</div></div>
			<div class="card"><div class="card-body">
				<h3>Photo</h3>
				<?php
					if ($p = \Teachers\get_teacher_photo_src($t['user_id'])) {
						echo "<img class='img-fluid' src=\"$p\">\n";
					}
					echo \Teachers\upload_teacher_photo_form($t);
				?>
			</div></div> <!-- end of card -->	
		<?php } ?>
</div>
</div>