<?php
	
echo "<table class=\"table table-striped table-bordered\">
		<tbody>
		<tr><td scope=\"row\"><span class='oi oi-people' title='people' aria-hidden='true'></span> Workshop:</td><td>{$wk['title']}</tr>
		<tr><td scope=\"row\"><span class='oi oi-book' title='book' aria-hidden='true'></span> Description:</td><td>{$wk['notes']}</td></tr>
		<tr><td scope=\"row\"><span class='oi oi-calendar' title='calendar' aria-hidden='true'></span> When:</td><td>{$wk['when']}</tr>
		<tr><td scope=\"row\"><span class='oi oi-map title='map' aria-hidden='true'></span> Where:</td><td>{$wk['place']} {$wk['lwhere']}</tr>
		<tr><td scope=\"row\"><span class='oi oi-dollar' title='dollar' aria-hidden='true'></span> Cost:</td><td>{$wk['costdisplay']}</td></tr>
		<tr><td scope=\"row\"><span class='oi oi-clipboard' title='clipboard' aria-hidden='true'></span> Open Spots:</td><td>{$wk['open']} (of {$wk['capacity']})</td></tr>
		<tr><td scope=\"row\"><span class='oi oi-clock' title='clock' aria-hidden='true'></span> Waiting:</td><td>".($wk['waiting']+$wk['invited'])."</td></tr>
		$names_list
		
		</tbody>
		</table>\n";
		
?>