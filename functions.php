<?php

function is_admin () {
	global $Admin, $User, $Users;
	$Valid = FALSE;
	foreach ($Users as $key => $value) {
		if (!$Valid) {
			if ($key == $User AND $value == $Admin) {
				$Valid = TRUE;
			}
		}
	}
	return $Valid;
}
/*
	global $Admin, $AdminPassword;
	if ($Admin == $AdminPassword) {
		return TRUE;
	} else {
		return FALSE;
	}
*/

// Checks if a string can be found as a key within an array.
function checkarray($Array, $String) {
	$Valid = FALSE;
	foreach ($Array as $key => $value) {
		if (!$Valid) {
			if ($String != $key) {
				$Valid = FALSE;
			} else {
				$Valid = TRUE;
			}
		}
	}
	return $Valid;
}

?>
