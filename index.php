<?php // Comments are denoted with //, code that's been commented out with # and sections with /* */.

/* Include the settings file, in which the following variables are set:
$MimeType
$Charset
$DBHost
$DBUser
$DBPassword
$DBName
$Users (array mapping usernames to passwords)
*/ 

require ("settings.php");

$User = $_GET['User'];
$Admin= $_POST['Admin'];
$NewColumn1 = $_POST['NewColumn1'];
$NewColumn2 = $_POST['NewColumn2'];
$NewColumn3 = $_POST['NewColumn3'];
$NewFormat = $_POST['NewFormat'];
$NewYear = $_POST['NewYear'];
$NewNew = $_POST['NewNew'];
$EditColumn1 = $_POST['EditColumn1'];
$EditColumn2 = $_POST['EditColumn2'];
$EditColumn3 = $_POST['EditColumn3'];
$EditFormat = $_POST['EditFormat'];
$EditYear = $_POST['EditYear'];
$EditNew = $_POST['EditNew'];
$Edit = $_POST['Edit'];
$Delete = $_POST['Delete'];

// Include the functions files.

require("functions.php");
require("../include/functions.php");

$Column1 = "Artist";
$Column2 = "Title";
$Column3 = "Notes";

// Set the MIME type for this XHTML 1.0 Strict document to the correct application/xhtml+xml if the browser is capable of displaying it then, and to text/html if the browser is IE. The W3C XHTML validator supports application/xhtml+xml but doesn't advertise it, so we have to force it for that particular user agent.
// NOTE: As of now, the page is also served as text/html if the user is an admin, because the form and table nesting that is used in admin mode is malformed XML. This will be fixed some day, but I'm probably the only person who'll ever be an admin.

if (!stristr($_SERVER["HTTP_USER_AGENT"],"W3C_Validator")) { 
	if (!stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml") OR is_admin()) {
		$MimeType = "text/html";
	}
}

// Send the HTTP headers that define the MIME type and charset.

header("Content-Type: $MimeType; charset=$Charset");
header("Vary: Accept");

if ($User AND checkarray($Users, $User)) {
	// Database connection
	$DBConnection = mysql_connect($DBHost, $DBUser, $DBPassword) or die(mysql_error());
	mysql_select_db($DBName) or die(mysql_error());

	// Make the first column the default sorting column in the table further down the line.
	if ($SortBy != $Column2 AND $SortBy != "Format") {
		$SortBy = $Column1;
	}
	if ($SortDirection != "ASC" AND $SortDirection != "DESC") {
		$SortDirection = "ASC";
	}

	// If user is admin, check if new data has been added or if an entry has been edited/deleted.
	if (is_admin()) {
#		$DBCreateQuery = "CREATE table $List (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, $Column1 TEXT, $Column2 TEXT";
#		if ($List == "DVD") {
#			$DBCreateQuery = $DBCreateQuery . ")";
#		} else {
#			$DBCreateQuery = $DBCreateQuery . ", $Column3 TEXT)";
#		}
#		mysql_query ($DBCreateQuery);

		// If the admin has submitted a new entry, a variable will be filled.
		if ($NewColumn2) {
			$New = "1";
		}                      

		// New
		if ($New) {
			$DBNewQuery = "INSERT into $User values ('0', '$NewColumn1', '$NewColumn2', '$NewFormat', '$NewColumn3', '$NewYear', '$NewNew')";
			if (mysql_query ($DBNewQuery)) {
				$NewSuccessful = "1";
			}
		}
		// Delete
		if ($Delete) {
			$DBDeleteQuery = "DELETE FROM $User WHERE id = '$Delete'";
			if (mysql_query ($DBDeleteQuery)) {
				$DeleteSuccessful = "1";
			}
		}
		// Edit
		if ($Edit) {
			$DBEditQuery = "UPDATE $User SET $Column1 = '$EditColumn1', $Column2 = '$EditColumn2', Format = '$EditFormat', $Column3 = '$EditColumn3', Year = '$EditYear', New = '$EditNew' WHERE id = '$Edit'";
			if (mysql_query ($DBEditQuery)) {
				$EditSuccessful = "1";
			}
		}
	}

	// Read from the selected list table.
	$DBReadQuery = "SELECT * FROM $User ORDER BY $SortBy $SortDirection";
	if ($SortBy == $Column1) {
		$DBReadQuery = $DBReadQuery.", Year $SortDirection";
	}
	$DBReadResult = mysql_query($DBReadQuery);

}


// Behold, the start of the XHTML! Here there be monsters.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title><?php
// Print title
if ($User AND checkarray($Users, $User)) {
	echo ("$User's CD collection");
}
?></title>
<meta http-equiv="Content-Type" content="<?php print ("$MimeType"); ?>; charset=<?php print ("$Charset"); ?>" />
<link rel="stylesheet" href="cd.css" type="text/css" />
<link rel="icon" href="/favicon.ico" />
<link rel="shortcut icon" href="/favicon.ico" />
</head>

<body>

<?php

// Heading. Differs depending on whether or not a list has been chosen.
if (!$User OR !checkarray($Users, $User)) {
	print ("<h1>CD collections</h1>\n");
} else {
	print ("<h1>$User's CD collection</h1>\n");

	// If user is admin and has just performed an operation, print a message saying if it was successful or not.
	if (is_admin()) {
		// Add
		if ($New == "1" AND $NewSuccessful == "1") {
		        print ("<p class=\"new\">The new entry was successfully added!</p>");
		} elseif ($New == "1" AND $NewSuccessful != "1") {
		        print ("<p class=\"error\">The new entry could not be added. The SQL query was $DBNewQuery.</p>");
		}
		// Delete
		if ($Delete AND $DeleteSuccessful == "1") {
		        print ("<p class=\"new\">The entry, $Delete, was successfully deleted!</p>");
		} elseif ($Delete AND $DeleteSuccessful != "1") {
		        print ("<p class=\"error\">The entry, $Delete, could not be deleted.</p>");
		}
                // Edit
		if ($Edit AND $EditSuccessful == "1") {
			print ("<p class=\"new\">The entry, $Edit, was successfully updated!</p>");
		} elseif ($Edit AND $EditSuccessful != "1") {
			print ("<p class=\"error\">The entry, $Edit, could not be updated.</p>");
		}
	// If user isn't admin, just print a standard, boring info message.
	} else {
		// And also print an error message if a login was attempted but not succeeded.
		if ($Admin) {
			print ("<p class=\"error\">Password incorrect. You were not logged in. You wrote $Admin and you are $User</p>");
		}
		print ("<p>All albums I own. All albums are in jewel cases unless noted otherwise. Newly purchased albums marked like <span class=\"new\">this</span>. (A few live/tour DVDs are also included.)</p>\n");
	}
}

// Create the table!
if ($User AND checkarray($Users, $User)) {
	print ("\n<table>\n\n");

	// Print table headers, with links to sort the list by specific columns.
	print ("<tr>\n<th");
	if ($SortBy == $Column1) {
		print (" class=\"sorted\">$Column1");
	} else {
		print (">$Column1 <a href=\"$PHP_SELF?");
		if (is_admin()) {
			print ("Admin=$Admin&amp;");
		}
		print ("User=$User&amp;SortBy=$Column1\" class=\"sort\">&darr;</a>");
	}
	print ("</th>\n<th");
	if ($SortBy == $Column2) {
		print (" class=\"sorted\">$Column2");
	} elseif ($SortBy != $Column2) {
		print (">$Column2 <a href=\"$PHP_SELF?");
		if (is_admin()) {
			print ("Admin=$Admin&amp;");
		}
		print ("User=$User&amp;SortBy=$Column2\" class=\"sort\">&darr;</a>");
	} else {
		print (">$Column2");
	}
	print ("</th>\n<th");
        if ($SortBy == "Format") {
                print (" class=\"sorted\">Format");
        } elseif ($SortBy != "Format") {
                print (">Format <a href=\"$PHP_SELF?");
                if (is_admin()) {
                        print ("Admin=$Admin&amp;");
		}
                print ("User=$User&amp;SortBy=Format\" class=\"sort\">&darr;</a>");
        } else {
                print (">Format");
        }
	print ("</th>\n");
	print ("<th>$Column3");
#	if ($SortBy != $Column3) {
#		print ("<a href=\"$PHP_SELF?List=$List&amp;SortBy=$Column3\" class=\"sort\">&darr;</a>");
#	}
	print ("</th>\n");
	if (is_admin()) {
		print ("<th>Sorting number</th>");
		print ("<th>New?</th><th>Action</th>");
	}
	print ("</tr>\n\n");

	// Check for admin status; if positive, create table with form elements, if negative, create a table with the list's contents.
	if (is_admin()) {
		echo("<form action=\"$php_self\" method=\"post\">");
		print ("<tr class=\"new\">\n<td><input type=\"text\" name=\"NewColumn1\" /></td>\n<td><input type=\"text\" name=\"NewColumn2\" /></td>\n");
		echo("<td><input type=\"text\" name=\"NewFormat\" size=\"10\" /></td>\n");
		print ("<td><input type=\"text\" name=\"NewColumn3\" /></td>\n");
		print ("<td><input type=\"text\" name=\"NewYear\" maxlength=\"3\" size=\"3\" /></td>");
		print ("<td><input type=\"hidden\" name=\"Admin\" value=\"$Admin\" /><input type=\"checkbox\" name=\"NewNew\" value=\"1\" /></td><td><input type=\"submit\" value=\"Add\" /></td></tr>\n</form>\n\n");
	}
	// Initiate counter
	$Count = 0;
	while ($Row = mysql_fetch_array($DBReadResult)) {
		// Convert HTML characters to entities
		$Row[$Column1] = htmlspecialchars($Row[$Column1]);
		$Row[$Column2] = htmlspecialchars($Row[$Column2]);
		$Row["Format"] = htmlspecialchars($Row["Format"]);
		// Output 
		if (is_admin()) {
			print ("<form action=\"$php_self\" method=\"post\">");
		}
		print ("<tr");
		if ($Row["New"]) {
			print (" class=\"new\"");
		}
		print (">\n<td");
		if ($SortBy == $Column1) {
			print (" class=\"sorted\"");
		}
		print (">");
		if (is_admin()) {
			print ("<input type=\"text\" name=\"EditColumn1\" value=\"");
		}
		print ("$Row[$Column1]");
		if (is_admin()) {
			print ("\" />");
		}
		print ("</td>\n<td");
		if ($SortBy == $Column2) {
			print (" class=\"sorted\"");
		}
		print (">");
		if (is_admin()) {
			print ("<input type=\"text\" name=\"EditColumn2\" value=\"");
		}
		print ("$Row[$Column2]");
		if (is_admin()) {
			print ("\" />");
		}
		print ("</td>\n<td");
                if ($SortBy == "Format") {
                        print (" class=\"sorted\"");
                }
                print (">");
                if (is_admin()) {
                        print ("<input type=\"text\" name=\"EditFormat\" size=\"10\" value=\"");
                }
                print ("$Row[Format]");
                if (is_admin()) {
                        print ("\" />");
                }
		print ("</td>\n");
		print ("<td>");
		if (!is_admin()) {
			if ($Row[$Column3]) {
				// Replace HTML special characters with entities
				$Row[$Column3] = htmlspecialchars($Row[$Column3]); // ereg_replace("&", "&amp;", $Row[$Column3]);
				print ("($Row[$Column3])");
			}
		} else {
			print ("<input type=\"text\" name=\"EditColumn3\" value=\"$Row[$Column3]\" />");
		}
		print ("</td>\n");
		if (is_admin()) {
			print ("<td><input type=\"text\" name=\"EditYear\" maxlength=\"3\" size=\"3\" value=\"$Row[Year]\" /></td>\n");
			print ("<td><input type=\"checkbox\" name=\"EditNew\" value=\"1\"");
			if ($Row["New"]) {
				print (" checked=\"checked\"");
			}
			print (" /></td>\n");
			print ("<td><input type=\"hidden\" name=\"Admin\" value=\"$Admin\" /><input type=\"hidden\" name=\"Edit\" value=\"$Row[id]\" /><input type=\"submit\" value=\"Edit\" /></form>");
			print ("<form action=\"$php_self\" method=\"post\"><input type=\"hidden\" name=\"Admin\" value=\"$Admin\" /><input type=\"hidden\" name=\"Delete\" value=\"$Row[id]\" /><input type=\"submit\" value=\"Delete\" /></form></td>\n");
		}
		print ("</tr>\n\n");
	// Increment counter
	$Count++;
	}
	print ("</table>\n");
	echo "<p>Entries: ".$Count."</p>";
}

// Print the list of the lists. Heh. The list will be printed no matter what list the user has chosen, but the current list will not be listed. If no list is chosen, all will be listed, as an index.
if (checkarray($Users, $User)) {
        print ("<p>Other people's lists:</p>\n<ul>\n");
} else {
	print ("<p>Please select the person whose list to view:</p>\n<ul>\n");
}
foreach ($Users as $key => $value) {
	if ($User != $key) {
		echo("<li><a href=\"");
		echo("?User=$key\">$key</a></li>");
	}
}
echo("</ul>");

echo("<ul>");
if (!is_admin()) {
	echo("<li><a href=\"http://validator.w3.org/check?uri=referer&amp;verbose=1\">Valid XHTML 1.0 Strict</a></li>");
}
?>
<li><a href="http://jigsaw.w3.org/css-validator/check/referer/">Valid CSS 2.1</a></li>
<li><a href="http://php.net">PHP</a></li>
<li><a href="http://skivsamling.nu">Skivsamling.nu</a></li>
</ul>

<?php
if ($User AND checkarray($Users, $User)) {
	if (!is_admin()) {
		print ("<form action=\"$php_self\" method=\"post\"><p><input type=\"password\" name=\"Admin\" /><input type=\"submit\" value=\"Log in\" /></p></form>");
	} else {
		print ("<form action=\"$php_self\" method=\"post\"><p><input type=\"hidden\" name=\"Admin\" value=\"\" /><input type=\"submit\" value=\"Log out\" /></p></form>");
	}
}
?>

</body>
</html>
<?php
if ($User AND checkarray($Users, $User)) {
	// Close database connection.
	mysql_close ($DBConnection);
}
?>
