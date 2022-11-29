			<?php
			// unpaid students
			if (count($unpaid) > 0) {
				echo "<h5>Unpaid</h5>\n<ul>\n";
				$last_wk = null;
				foreach ($unpaid as $up) {
					if ($last_wk != $up['workshop_id']) {
						if ($last_wk) { echo "</ul></li>"; } // close last workshop list if there was one
						echo "<li><b><a href='/admin-workshop/view/{$up['workshop_id']}'>{$up['title']}</a> - ".date('D M j', strtotime($up['start']))." - {$up['cost']}</b><ul>\n";
						$last_wk = $up['workshop_id'];
					}
					echo "<li>{$up['nice_name']} - {$up['email']}</li>\n";
				}
				echo "</ul></li>"; // close last workshop list
				echo "</ul>\n"; // close this section
			}


			// not full, 15 days out
			$ts_now = strtotime('now');
			$ts_then = strtotime('+15 days');
			$nsohtml = '';
			foreach ($workshops as $wk) {
				if (!$wk['hidden'] && $wk['xtra'] == 0) {
					if ($wk['enrollments']['enrolled'] < $wk['capacity']) {
						$ts = strtotime($wk['course_start']);
						
						if ($ts >= $ts_now && $ts <= $ts_then) {
						
							$nsohtml .= "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a> ({$wk['enrollments']['enrolled']}/{$wk['capacity']})";
							if ($wk['enrollments']['applied']) { $nsohtml .= " <span class='text-primary'>- {$wk['enrollments']['applied']}</span>"; }
							$nsohtml .= "</li>\n";
						}
					}
				}
			}
			if ($nsohtml) {
				echo "<h5>Not Full, 15 Days Out</h5>
			<ul>$nsohtml</ul>\n";
			}	
			
			
			// hidden classes
			$hiddenhtml = '';
			foreach ($workshops as $wk) {
				if ($wk['hidden'] == 1 && $wk['xtra'] == 0) {
					$ts = strtotime($wk['course_start']);
					
					$hiddenhtml .= "<li>".\Wbhkit\figure_year_minutes($ts).": <a href='/admin-workshop/view/{$wk['id']}'>{$wk['title']}</a>, {$wk['teacher_name']}</li>\n";
					
				}
			}
			if ($hiddenhtml) {
				echo "<h5>Hidden</h5>\n
					<ul>$hiddenhtml</ul>";
			}
			 
			//bitness
			if (count($bitnesses) > 0) {
				echo "<h5>Recent Bitnesses</h5>\n<ul>\n";
				foreach ($bitnesses as $b) {
					echo "<li><a href='/admin-workshop/view/{$b->fields['id']}'>{$b->fields['title']}</a> ({$b->fields['enrolled']}/{$b->fields['capacity']})</li>\n";
				}
				echo "</ul>\n";
			}
			
			
			?>