<?php
namespace Wbhkit;

// version 1.1 - added checkbox, option to remove colon from label
// 1.2 - added blank option to the top of all drop-downs
// 1.3 - adapted for bootstrap 3RC1
// 1.31 - minor fixes 4/2/14
// 1.4 - adding smart urls functions
// 1.5 - error messages for texty, drop 5/2015
// 1.5.1 - tweaking radio 1/2016
// 1.6 - removed 'mres', renamed file, added namespace - 11/2017
// 2.0 - upgraded to bootstrap v4.0 - 11/2017
// 2.1 - added html5 form validation, modal sub - 11/2017


function texty($key, $value = '', $label = null, $placeholder = null, $help = null, $error = null, $validation = null, $ttype = null) {
	
	if (!$ttype) { $ttype = 'text'; }
	$l = label($label, $key);
	$texty = form_element_start();		
	$texty .= "{$l}<input class='form-control mx-md-1' type=\"{$ttype}\" id=\"$key\" name=\"$key\" value=\"$value\" ".($placeholder ? "placeholder='$placeholder'" : '');

	if ($validation) { $texty .= $validation; }

	$texty .= figure_aria_attribute($key, $help);
	$texty .= form_help_block($key, $help, $error);		
	$texty .= "</div>\n";
	
	return $texty;
}


function fileupload($key, $label = null) {
	
	$l = label($label, $key);
	$fu = form_element_start();		
	$fu .= "{$l}<input class='form-control-file mx-md-1' type=\"file\" id=\"$key\" name=\"$key\">\n";
	$fu .= "</div>\n";
	
	return $fu;
}

function textarea($key, $value = null, $label = null, $rows = 5, $cols = 40, $help = null, $error = null, $validation = null) {
	$l = label($label, $key);
	$ta = form_element_start();
	$ta .= "{$l} <textarea class='form-control' id='{$key}' name='{$key}' cols='{$cols}' rows='{$rows}'";
	if ($validation) { $ta .= $validation; }
	$ta .= figure_aria_attribute($key, $help, $error);
	$ta .= "{$value}</textarea>";
	$ta .= form_help_block($key, $help, $error);		
	$ta .= "</div>\n";	
	return $ta;
}
 
function hidden($key, $value = '') {
	return "<input type='hidden' name='$key' value='$value'>\n";
}

function submit($value = 'Submit') {
	return "<button type=\"submit\" class=\"btn btn-primary\">{$value}</button>\n";
}

function drop($name, $opts, $selected = null, $label = null, $help = null, $error = null, $validation = null) {
	$l = label($label, $name);	
	$select = form_element_start();	
	$select .= "{$l} <select class='form-control' name='$name' id='$name'";
	if ($validation) { $select .= $validation; }
	$select .= figure_aria_attribute($name, $help, $error);
	$select .= "<option label=' ' value=''></option>\n";
	foreach ($opts as $key => $show) {
		$select .= "<option value='$key'";
		if ($key == $selected) { $select .= " SELECTED "; } 
		$select .= ">$show</option>\n";
	}
	$select .= "</select>";
	$select .= form_help_block($key, $help, $error);		
	$select .= "</div>\n";
	return $select;
}

function multi_drop($name, $opts, $selected = null, $label = null, $size = 10, $help = null, $error = null, $validation = null) {
	$l = label($label, $name);
	
	$select = form_element_start();	
	$select .= "{$l} <select size='$size' multiple class='form-control' name='{$name}".'[]'."' id='$name'";
	if ($validation) { $select .= $validation; }
	$select .= figure_aria_attribute($name, $help, $error);
	//$select .= "<option label=' ' value=''></option>\n";
	foreach ($opts as $key => $show) {
		$select .= "<option value=\"$key\"";
		if (is_array($selected)) {
			foreach ($selected as $sel) {
				if ($key == $sel) { $select .= " SELECTED "; } 
			}
		} else {
			if ($key == $selected) { $select .= " SELECTED "; } 
		}
		$select .= ">$show</option>\n";
	}
	$select .= "</select>";
	$select .= form_help_block($name, $help, $error);	
	$select .= "</div>";

	return $select;
}


function radio($name, $opts, $selection = null) {
	$i = 1;
	$b = "<div class='form-check form-check-inline'>";
	foreach ($opts as $key => $label) {
		$ch = ($key == $selection && !is_null($selection) ? 'checked' : '');
		$b .= '<label class="form-check-label"><input class="form-check-input" type="radio" id="'.$name.'" name="'.$name.'" id="radio'.$i.'" value="'.$key.'" '.$ch.'> '.$label."</label>\n";
		$i++;
	}
	$b .= "</div>\n";
	return $b;
}

function checkbox($name, $value, $label = null, $checked = false, $multiple = false) {
	$label = figure_label($label, $name, false);
	if ($multiple) { $name = "{$name}[]"; }
	return "<div class='form-check form-check-inline'><label class=\"form-check-label\">
	  <input class='form-check-input' type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" ".($checked ? 'checked' : '').">
	  {$label}
	</label></div>";
}

function label($label, $key, $colon = false) {
	$l = figure_label($label, $key, $colon = false);
	if ($l === 0) { 
		return '';
	} else {
		return "<label for='{$key}'>{$l}</label>\n";
	}
}

function figure_label($label, $key, $colon = false) {
	if ($label === 0) { 
		return '';
	} else {
		return ($label ? $label : ucwords(str_replace("_"," ",$key))).($colon ? ': ' : '');
	}	
}

// next four subs deal with error messages
function form_element_start() {
	return "<div class='form-group'>";
} 

function form_help_block($id, $help = null, $error = null) {
	$id = figure_help_id($id);
	$element = '';
	if ($help) { $element = "<small id ='$id' class='form-text'>$help</small>"; }
	if ($error) { $element .= "<div class='invalid-feedback'>{$error}</div>\n"; }
	return $element;
}

function figure_help_id($key) {
	return "{$key}HelpBlock";
}

function figure_aria_attribute($key, $help = null, $error = null) {
	if ($help || $error) {
		return " aria-describedby='".figure_help_id($key)."'>";
	} else {
		return ">";
	}	
}

function set_vars($vars) {
	foreach ($vars as $va) {
		global $$va;
		$$va = isset($_REQUEST[$va]) ? $_REQUEST[$va] : '';
	}
}

function query_to_array($rows) {
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


function form_validation_javascript($form_id) {
	return "
		<script>
	// Example starter JavaScript for disabling form submissions if there are invalid fields
	(function() {
	  'use strict';

	  window.addEventListener('load', function() {
	    var form = document.getElementById('{$form_id}');
	    form.addEventListener('submit', function(event) {
	      if (form.checkValidity() === false) {
	        event.preventDefault();
	        event.stopPropagation();
	      }
	      form.classList.add('was-validated');
	    }, false);
	  }, false);
	})();
	</script>
";
}


function get_modal($id, $title, $body) {

  return '<div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog" aria-labelledby="'.$id.'Label" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="'.$id.'Label">'.$title.'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">'.$body.'</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><span class="oi oi-circle-x" title="circle-x" aria-hidden="true"></span> Close</button>
        </div>
      </div>
    </div>
  </div>';
  
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


/*
* time date functions
*/

function friendly_time($time_string) {
	$ts = strtotime($time_string);
	$minutes = date('i', $ts);
	if ($minutes == 0) {
		return date('ga', $ts);
	} else {
		return date('g:ia', $ts);
	}
}

function friendly_date($time_string) {
	
	$ts = strtotime($time_string);
	
	$now_doy = date('z'); // current day of year
	$wk_doy = date('z', strtotime($time_string)); // workshop day of year


	if (date('Y', $ts) != date('Y')) {  
		return date('D M j, Y', $ts);
	} else {
		return date('D M j', $ts);
	}
}	

function friendly_when($time_string) {
	return friendly_date($time_string).' '.friendly_time($time_string);
}

function is_future($time_string) {
	if (strtotime($time_string) > strtotime('now')) {
		return 1;
	} else {
		return 0;
	}
}

function binary_yesno($field) {
	if ($field == 1) { return 'yes'; }
	return 'no';
}


function admin_log($st) {
	global $sc;
	if (isset($sc) && strpos($sc,'admin') !== false) {
		echo "$st<br>\n";
	}
}

// take array $part
// add missing keys with their default values
// based on the keys/values in $default
function fill_out($part, $default) {
	
	foreach ($default as $k => $v) {
		if (!$part[$k]) { $part[$k] = $v; }
	}
	return $part;
	
}

// make array of params
// for pdo_query
function make_params($data, $default) {
	$params = array();
	foreach ($default as $k => $v) {
		$params[":{$k}"] = $data[$k];
	}
	return $params;
}

function create_update_sql($default) {
	$sql = '';
	foreach ($default as $k => $v ) {
		if ($k == 'id') { continue; }
		if ($sql) { $sql .= ', '; }
		$sql .= "{$k} = :$k ";
	}
	return $sql;
}

// for adding 'empty x' keys to vars_to_set
function add_empty_fields($varlist, $fields) {
	foreach ($fields as $k => $v) {
		if ($k == 'id') { continue; }
		$varlist[] = $k;
	}
	return $varlist;
}