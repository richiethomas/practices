

<div class="row">
	<div class="col-sm-3">
		<h1><a href='/admin-teams'>Teams</a></h1>
		<h2>All Teams</h2>
		<p class='fw-lighter'>lighter text = online team</p>
		<ul>
			<?php
			$emails = '';
			foreach ($teams as $t) {
				if ($t->fields['online']) {
					echo "<li class='fw-lighter'>";
				} else {
					echo "<li>";
				}
				echo "<a href=\"/admin-teams/view/{$t->fields['id']}\">{$t->fields['title']}</a>".($t->fields['active'] ? '' : ' (inactive)')."</li>\n";
			}
			?>
		</ul>
			</div>
	<div class="col-sm-9">
								
			<div class="card"><div class="card-body">
			<h2><?php echo $team->fields['id'] ? "Edit '{$team->fields['title']}'" : 'Add Team'; ?></h2>
			<form id='update_teacher' action='/admin-teams/adup/<?php echo $team->fields['id'] ?>' method='post'>
			<?php  echo $team->get_form_fields(); ?>
			</form>
			<?php if ($team->fields['id']) { ?>
			<p>Or <a class='btn btn-danger' href="/admin-teams/delete/<?php echo $team->fields['id']; ?>">delete this team</a></p>
			<?php } ?>
			</div></div> <!-- end of card-->

			<?php if (isset($team->fields['id']) && $team->fields['id']) { ?>
				<div class='row'>
					<div class='col'>
						<div class="card"><div class="card-body">
						<h3>Members</h3>
						<ul>
						<?php 
						$names = '';
						$emails = '';
						foreach ($team->users as $u) {
							$names .= $u->fields['nice_name']."\n";
							$emails .= $u->fields['email'].",\n";
							echo "<li>{$u->fields['nice_name']} (<a href='/admin-teams/removemember/{$team->fields['id']}/{$u->fields['id']}'>remove</a>)</li>\n";
						} 
						$txtarea = $names ."\n". $emails;
						?>
						</ul>
				
<?php	
	include 'assets/ajax/search_box.php';
	echo  "<h4>Add Team Member</h4><form id='add_member' class='form-inline' action='/admin-teams/addmember/{$team->fields['id']}' method='post' novalidate><fieldset name='new_member'>";
	echo "<div class='form-group'>
			<label for='search-box' class='form-label'>Email: </label>
			<input type='text' class='form-control' id='search-box' name='email' autocomplete='off'>
			<div id='suggesstion-box'></div>
			</div>\n".	
	Wbhkit\submit('add').
	"</fieldset></form>\n";
?>
						</div></div> <!-- end of card -->	
					</div>
					<div class='col'>
						<?php echo Wbhkit\textarea('team_info', $txtarea, 'Cut and paste info', 10, 60); ?>
						
					</div>
		<?php } ?>
</div>
</div>

