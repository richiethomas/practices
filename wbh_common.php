<?
// version 1.1 - added checkbox, option to remove colon from label
// 1.2 - added blank option to the top of all drop-downs
// 1.3 - adapted for bootstrap 3RC1
// 1.31 - minor fixes 4/2/14
// 1.4 - adding smart urls functions
// 1.5 - error messages for texty, drop 5/2015


function wbh_texty($key, $value = '', $label = null, $placeholder = null, $help = null, $error = null) {
	$l = wbh_figure_label($label, $key);
	
	$texty = wbh_form_start($error);		
	$texty .= ($l ? "<label for='$key'>{$l}</label>" : '')."<input class='form-control' type='text' id=\"$key\" name=\"$key\" value=\"$value\" ".($placeholder ? "placeholder='$placeholder'" : '').">";
	$texty .= wbh_form_help_block($help, $error);		
	$texty .= "</div>\n";
	
	return $texty;
}

function wbh_textarea($key, $value = null, $label = null, $rows = 5, $cols = 40, $help = null, $error = null) {
	$l = wbh_figure_label($label, $key);
	$ta = wbh_form_start($error);
	$ta .= "<label for='{$key}'>{$l}</label> <textarea class='form-control' id='{$key}' name='{$key}' cols='{$cols}' rows='{$rows}'>{$value}</textarea>";
	$ta .= wbh_form_help_block($help, $error);		
	$ta .= "</div>\n";	
}
 
function wbh_hidden($key, $value = '') {
	return "<input type='hidden' name='$key' value='$value'>\n";
}

function wbh_submit($value = 'Submit') {
	return "<button type=\"submit\" class=\"btn btn-primary\">{$value}</button>\n";
}

function wbh_drop($name, $opts, $selected = null, $label = null, $help = null, $error = null) {
	$label = wbh_figure_label($label, $name);

	$select = wbh_form_start($error);	
	$select .= "<label for='$name'>{$label}</label><select class='form-control' name='$name' id='$name'><option value=''></option>\n";
	foreach ($opts as $key => $show) {
		$select .= "<option value='$key'";
		if ($key == $selected) { $select .= " SELECTED "; } 
		$select .= ">$show</option>\n";
	}
	$select .= "</select>";
	$select .= wbh_form_help_block($help, $error);		
	$select .= "</div>\n";
	return $select;
}

function wbh_multi_drop($name, $opts, $selected = null, $label = null, $size = 10, $help = null, $error = null) {
	$label = wbh_figure_label($label, $name);
	
	$select = wbh_form_start($error);	
	$select = "<label for='$name'>{$label}</label><select size='$size' multiple class='form-control' name='{$name}".'[]'."' id='$name'><option value=''></option>\n";
	foreach ($opts as $key => $show) {
		$select .= "<option value=\"$key\"";
		if (is_array($selected)) {
			foreach ($selected as $sel) {
				if ($key == $sel) { $select .= " SELECTED "; } 
			}
		} else {
			if ($key == $sel) { $select .= " SELECTED "; } 
		}
		$select .= ">$show</option>\n";
	}
	$select .= "</select>";
	$select .= wbh_form_help_block($help, $error);	
	$select .= "</div>";

	return $select;
}


function wbh_radio($name, $opts, $selection = null) {
	$b = '';
	$i = 1;
	$b = "<div class='radio-inline'>";
	foreach ($opts as $key => $label) {
		$ch = ($key == $selection && !is_null($selection) ? 'checked' : '');
		$b .= '<label for="'.$name.'"><input class="form-control" type="radio" id="'.$name.'" name="'.$name.'" id="radio'.$i.'" value="'.$key.'" '.$ch.'> '.$label.'</label> ';
		$i++;
	}
	$b .= "</div>\n";
	return $b;
}

function wbh_checkbox($name, $value, $label = null, $checked = false, $multiple = false) {
	$label = wbh_figure_label($label, $name, false);
	if ($multiple) { $name = "{$name}[]"; }
	return "<div class='checkbox-inline'><label class=\"checkbox inline\">
	  <input type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" ".($checked ? 'checked' : '').">
	  {$label}
	</label></div>";
}

function wbh_figure_label($label, $key, $colon = false) {
	if ($label === 0) { 
		return '';
	} else {
		return ($label ? $label : ucwords($key)).($colon ? ': ' : '');
	}	
}

// next two subs deal with error messages
function wbh_form_start($error = null) {
	if ($error) {
		return "<div class='form-group has-error'>";
	} else {
		return "<div class='form-group'>";
	}
} 

function wbh_form_help_block($help = null, $error = null) {
	if ($error) {
		return "<p class='help-block text-danger'>$error</p>";
	}
	if ($help) {
		return "<p class='help-block'>$help</p>";
	}
	
}

function mres($thing) {
	$db = wh_set_db_link();
	return mysqli_real_escape_string($db, $thing);
}

function wbh_set_vars($vars) {
	foreach ($vars as $va) {
		global $$va;
		$$va = isset($_REQUEST[$va]) ? $_REQUEST[$va] : '';
	}
}

function wbh_query_to_array($rows) {
	$numfields = mysql_num_fields($rows);

	for ($x = 0;  $x < $numfields; $x++) {
		$fields[] = mysql_field_name($rows, $x);
	}
	$grid = array();
	$grid[] = $fields;
	while ($r = mysql_fetch_assoc($rows)) {
		$grid[] = $r;
	}
	return $grid;
}

function parse_path() {
  $path = array();
  if (isset($_SERVER['REQUEST_URI'])) {
    $request_path = explode('?', $_SERVER['REQUEST_URI']);
	if (!isset($request_path[1])) {
		$request_path[1] = '';
	}
	if (!isset($request_path[2])) {
		$request_path[2] = '';
	}

    $path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
    $path['call_utf8'] = substr(urldecode($request_path[0]), strlen($path['base']) + 1);
    $path['call'] = utf8_decode($path['call_utf8']);
    if ($path['call'] == basename($_SERVER['PHP_SELF'])) {
      $path['call'] = '';
    }
    $path['call_parts'] = explode('/', $path['call']);

    $path['query_utf8'] = urldecode($request_path[1]);
    $path['query'] = utf8_decode(urldecode($request_path[1]));
    $vars = explode('&', $path['query']);
    foreach ($vars as $var) {
      $t = explode('=', $var);
	  	if (!isset($t[1])) {
	  		$t[1] = '';
	  	}
      $path['query_vars'][$t[0]] = $t[1];
    }
  }
return $path;
}

