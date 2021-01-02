<h1>Class Shows</h1>


<div class="row">
	<div class="col-md-4">
<h3><?php echo $cs->fields['id'] ? 'Edit' : 'Add A New'; ?>  Class Show</h3>
<form action="admin_shows.php" method="post">
<?php
echo 
	\Wbhkit\texty('start', \Wbhkit\business_when($cs->fields['start'], true), null, null, null, 'Required', ' required ').
	\Wbhkit\texty('end', \Wbhkit\business_when($cs->fields['end'], true), null, null, null, 'Required', ' required ').
	\wbhkit\texty('online_url', $cs->fields['online_url'], null, null, null, 'Required', ' required ').
	\Wbhkit\drop('teacher_id', \Teachers\teachers_dropdown_array(true), $cs->fields['teacher_id'], 'Teacher', null, 'Required', ' required ').
	\Wbhkit\texty('actual_pay', $cs->fields['actual_pay']).
	\Wbhkit\texty('when_teacher_paid', \Wbhkit\business_when($cs->fields['when_teacher_paid'])).
	\Wbhkit\checkbox('reminder_sent', 1, 'Reminder sent?', $cs->fields['reminder_sent']).
	\Wbhkit\hidden('ac', $cs->fields['id'] ? 'up' : 'ad').
	($cs->fields['id'] ? \Wbhkit\hidden('show_id', $cs->fields['id']) : '').
	\Wbhkit\submit($cs->fields['id'] ? 'update' : 'add'.' show');
?>
</form>
</div>
<?php if ($cs->fields['id']) { ?>
<div class="col-md-8">
<h4>Associated Classes</h4>	
<ul>
<?php
foreach ($cs->wks as $w) {
	echo "<li><a href='admin_edit.php?wid={$w['workshop_id']}'>{$w['title']}</a> - <a href='admin_shows.php?ac=rem&show_id={$w['show_id']}&wid={$w['workshop_id']}'>remove</a></li>\n";
}
?>
</ul>

<h5>Associate A Class</h5>
<form action="admin_shows.php" method="post">
<?php
echo \Wbhkit\drop('wid', \Workshops\get_recent_workshops_dropdown(), null, 'Workshop').	
\Wbhkit\hidden('show_id', $cs->fields['id']).
\Wbhkit\hidden('ac', 'asc').
\Wbhkit\submit('associate workshop');
?>	
</form>
	
</div>
<?php } ?>
</div>

<hr>

<div class="row"><div class="col-md-6">
<h3>Upcoming Class Shows</h3>
<ul>
<?php
foreach ($shows as $upcoming_cs) {
	echo "<li class='m-2'><a href='admin_shows.php?ac=ed&show_id={$upcoming_cs->fields['id']}'>".$upcoming_cs->fields['friendly_when']."</a> (<a href='admin_shows.php?ac=del&show_id={$upcoming_cs->fields['id']}'>delete</a>)";
	echo "<ul>\n";
	if ($upcoming_cs->teacher->fields['id']) {
		echo "<li>Teacher: {$upcoming_cs->teacher->fields['nice_name']}</li>";
	}
	echo "<li>Link: <small>{$upcoming_cs->fields['online_url']}</small></li>\n";
	if (count($upcoming_cs->wks) > 0) {
		foreach ($upcoming_cs->wks as $w) {
			echo "<li><a href='admin_edit.php?wid={$w['workshop_id']}'>{$w['title']}</a></li>\n";
		}
	}
	echo "</ul>\n";
	echo "</li>\n";
}
?>
</ul>
</div></div>
