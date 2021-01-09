

<div class="row">
	<div class="col-sm-3">
		<h2>Teachers Info</h2>
		<ul>
			<li>How to <a href="admin_youtube.php">Stream to Youtube</a></li>
		</ul>
		<h2>All Teachers</h2>
		<ul>
			<?php
			foreach ($teachers as $teach) {
				echo "<li><a href=\"admin_teachers.php?tid={$teach['id']}\">{$teach['nice_name']}</a>".($teach['active'] ? '' : ' (inactive)')."</li>\n";
			}
			?>
		</ul>
	</div>
	<div class="col-sm-9">
		
		<?php if (isset($t['id']) && $t['id']) { ?>
			<h2><?php echo $t['nice_name']; ?> Teacher Info</h2>
			<div class="card"><div class="card-body">
			<h3>Basic Info</h3>
			<?php  echo \Teachers\get_teacher_form($t); ?>
			</div></div> <!-- end of card-->
			<div class="card"><div class="card-body">
				<h3>Photo</h3>
				<?php
					if ($p = \Teachers\get_teacher_photo_src($t['user_id'])) {
						echo "<img class='img-fluid' src=\"$p\">\n";
					}
					echo \Teachers\upload_teacher_photo_form($t);
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
								<td><a href=\"admin_edit2.php?wid={$tc['id']}\">{$tc['title']}</a></td>
						 		<td>{$tc['showstart']}</td>
								<td>{$tc['total_class_sessions']} sessions</td>
							</tr>\n";
				}
				?>
			</tbody></table>
				
			</div></div> <!-- end of card -->	
		<?php } ?>
</div>
</div>

