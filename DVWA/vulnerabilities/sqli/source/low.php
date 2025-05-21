<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];
	
	// SECURE VERSION: Input validation
	if (!is_numeric($id)) {
		die("Invalid input - ID must be a number");
	}

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// VULNERABLE VERSION (commented out)
			/*
			// Check database
			$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
			$result = mysqli_query($GLOBALS["___mysqli_ston"],  $query ) or die( '<pre>' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) . '</pre>' );

			// Get results
			while( $row = mysqli_fetch_assoc( $result ) ) {
				// Get values
				$first = $row["first_name"];
				$last  = $row["last_name"];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}
			*/

			// SECURE VERSION: Using prepared statement
			if ($stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?")) {
				// Bind parameters
				mysqli_stmt_bind_param($stmt, "i", $id);
				
				// Execute query
				mysqli_stmt_execute($stmt);
				
				// Bind result variables
				mysqli_stmt_bind_result($stmt, $first, $last);
				
				// Fetch results and display securely
				while (mysqli_stmt_fetch($stmt)) {
					// Use htmlspecialchars to prevent XSS
					$html .= "<pre>ID: " . htmlspecialchars($id) . 
							"<br />First name: " . htmlspecialchars($first) . 
							"<br />Surname: " . htmlspecialchars($last) . "</pre>";
				}
				
				// Close statement
				mysqli_stmt_close($stmt);
			}
			
			mysqli_close($GLOBALS["___mysqli_ston"]);
			break;
		case SQLITE:
			global $sqlite_db_connection;
			
			// VULNERABLE VERSION (commented out)
			/*
			#$sqlite_db_connection = new SQLite3($_DVWA['SQLITE_DB']);
			#$sqlite_db_connection->enableExceptions(true);

			$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id';";
			#print $query;
			try {
				$results = $sqlite_db_connection->query($query);
			} catch (Exception $e) {
				echo 'Caught exception: ' . $e->getMessage();
				exit();
			}

			if ($results) {
				while ($row = $results->fetchArray()) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
			} else {
				echo "Error in fetch ".$sqlite_db->lastErrorMsg();
			}
			*/

			// SECURE VERSION: Using prepared statement for SQLite
			try {
				$stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id");
				$stmt->bindParam(':id', $id, SQLITE3_INTEGER);
				$results = $stmt->execute();
				
				if ($results) {
					while ($row = $results->fetchArray()) {
						// Get values and escape output
						$first = htmlspecialchars($row["first_name"]);
						$last  = htmlspecialchars($row["last_name"]);
						
						// Feedback for end user
						$html .= "<pre>ID: " . htmlspecialchars($id) . 
								"<br />First name: {$first}<br />Surname: {$last}</pre>";
					}
				}
			} catch (Exception $e) {
				echo 'Caught exception: ' . htmlspecialchars($e->getMessage());
				exit();
			}
			break;
	} 
}

?>
