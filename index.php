<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('includes/dbfunctions.php');
require('includes/user_auth.php');
session_start();

// check if session has expired or something else is different;
if(check_session())
{
    session_regenerate_id();
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>FSU-Panama Internal Application Control System</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="assets/style.css" />
		<script src="assets/queryupdater.js"></script>
	</head>
	<body>
        <script>GetAdmissionProcessors();</script>
		<div id="title-bar">
			<img src="assets/fsu-seal.png" id="logo" />
			<h1 id="title">FSU-Panama Internal Application Control System</h1>
            <?php getUserBox($appdb); ?>
		</div>
		<div id="list-wrapper">
			<div id="query_param">
				<h2>Search parameters:</h2>
				<label>Number of results:</label>
				<select id="query_limit" name="query_limit">
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="150">150</option>
					<option value="200">200</option>
				</select>
				<label>Status:</label>
				<select id="status_filter" name="query_filter">
					<option value="0">Any Status</option>
					<option value="1">Admitted</option>
					<option value="2">Denied</option>
					<option value="3">In Process</option>
					<option value="4">Not Processed</option>
					<option value="5">Pending Documents</option>
					<option value="6">Pending TOEFL</option>
				</select>
				<label>Term:</label>
				<select id="term_filter" name="term_filter">
					<option value="All">All</option>
					<option value="Spring">Spring</option>
					<option value="Summer A">Summer A</option>
					<option value="Summer B">Summer B</option>
					<option value="Fall">Fall</option>
				</select>
				<label>Year:</label>
				<input type="text" id="year_filter" maxlength="4" size="4" name="year_filter" />
				<label>EMPLID:</label>
				<input type="text" id="EMPLID_filter" maxlength="9" size="9" name="EMPLID_filter" />
				<button id="reset_filter">Reset filters</button>
			</div>
			<div id="bulk_actions">
				<p id="bulk_title">Bulk actions</p>
				<select id="bulk_action" disabled>
					<option value="">Select...</option>
					<option value="change_status">Change Processing Status to...</option>
					<option value="change_fee_status">Change Fee Status to...</option>
					<option value="assign_processor">Assign to...</option>
					<option value="download_app_pack">Download Application Package</option>
				</select>
			</div>
			<table id="app_table">
				<tbody>
					<tr id="head_row">
						<th id="bulk_all">
							<input type="checkbox" id="bulk_select_all" />
						</th>
						<th>
							<a id="EMPLID" class="sort_button" href="#" sort="">EMPLID
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>	
						</th>
						<th>
							<a href="#" id="last_name" class="sort_button" sort="">
							Last Name
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />	
							</a>
						</th>
						<th>
							<a id="first_name" class="sort_button" href="#" sort="">
								First Name
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th>
							<a id="date" class="sort_button" href="#" sort="">
								Date of Application
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th>
							<a id="app_semester" class="sort_button" href="#" sort="">
								Semester
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th>
							<a id="app_year" class="sort_button" href="#" sort="">
								Year
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th>
							<a id="fee_paid" class="sort_button" href="#" sort="">
								Payment Status
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th>
							<a id="status" class="sort_button" href="#" sort="">
								Processing Status
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
						<th id="processor_col">
							<a id="processor" class="sort_button" href="#" sort="">
								Assigned to
							<img src="assets/sort-desc.png" class="sort-icon desc" />
							<img src="assets/sort-asc.png" class="sort-icon asc" />
							</a>
						</th>
                        <th id="actions_col">
                            Actions
                        </th>
					</tr>
					</tr>
					<?php 
					$filter = Array('limit' => 50, 'status' => "0", 'term' => "All", 'year' => "", 'EMPLID' => "");
					GetApplications($filter, 'date', "DESC", $appdb); 
					?>
				</tbody>
			</table>
		</div>
	</body>
</html>

<?php $appdb->close(); ?>