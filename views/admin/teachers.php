

<div class="row">
	<div class="col-sm-3">
		<h1>Teachers Info</h1>
		<h2>All Teachers</h2>
		<ul>
			<?php
			$emails = '';
			foreach ($teachers as $teach) {
				echo "<li><a href=\"/admin-teachers/view/{$teach['id']}\">{$teach['nice_name']}</a>".($teach['active'] ? '' : ' (inactive)')."</li>\n";
				if ($teach['active']) { $emails .= "{$teach['email']},\n"; }
			}
			?>
		</ul>
		
		<?php echo Wbhkit\textarea('emails', $emails, 'Cut and paste emails', 10, 80); ?>
		
		
	</div>
	<div class="col-sm-9">
		
		<?php if (isset($t['id']) && $t['id']) { ?>
			<h2><?php echo $t['nice_name']; ?> Teacher Info</h2>
			
			<p>See <a href="/admin-users/view/<?php echo $t['user_id']; ?>">user info</a> for <?php echo $t['nice_name']; ?></p>
			
			<div class="card"><div class="card-body">
			<h3>Basic Info</h3>
			<form id='update_teacher' action='/admin-teachers/up/<?php echo $t['id'] ?>' method='post'>
			<?php  echo \Teachers\get_teacher_fields($t); ?>
			</form>
			</div></div> <!-- end of card-->
			<div class="card"><div class="card-body">
				<h3>Photo</h3>
				<?php
					if ($p = \Teachers\get_teacher_photo_src($t['user_id'])) {
						echo "<img class='img-fluid' src=\"$p\">\n";
					}
					echo "<form action=\"/admin-teachers/photo/{$t['id']}\" method=\"post\" enctype=\"multipart/form-data\">".
					\Wbhkit\hidden ('MAX_FILE_SIZE', USER_PHOTO_MAX_BYTES).
					\Wbhkit\fileupload('teacher_photo', 'Upload/Replace Teacher Photo (JPG file type only)').
					\Wbhkit\submit ('Upload Photo').
					"</form>\n";
				?>
				
				</div></div> <!-- end of card-->

				<div class="card"><div class="card-body">
				<h3>Classes</h3>
				<table class="table table-striped">
					<thead><tr>
						<th>name</th>
						<th>when</th>
						<th># sessions</th>
					</tr></thead>
					<tbody>
				<?php
				foreach ($t_classes as $tc) {
					echo "<tr>
								<td><a href=\"/admin-workshop/view/{$tc->fields['id']}\">{$tc->fields['title']}</a></td>
						 		<td>{$tc->fields['showstart']}</td>
								<td>{$tc->fields['total_class_sessions']} session".\Wbhkit\plural($tc->fields['total_class_sessions']);
						if ($tc->fields['total_show_sessions']) {
							echo ",<br>{$tc->fields['total_show_sessions']} show".\Wbhkit\plural($tc->fields['total_show_sessions']);	
						}
						echo "</td>
							</tr>\n";
				}
				?>
			</tbody></table>
				
			</div></div> <!-- end of card -->	
		<?php } ?>
</div>
</div>

