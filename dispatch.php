<?php
   $callerName = $_POST["callerName"];
   $contactNo = $_POST["contactNo"];
   $locationOfIncident = $_POST["locationOfIncident"];
   $typeOfIncident = $_POST["typeOfIncident"];
   $descriptionOfIncident = $_POST["descriptionOfIncident"];
    
    require_once "db.php";
    $conn = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
	$sql = "SELECT patrolcar.patrolcar_id,patrolcar_status.patrolcar_status_desc FROM `patrolcar` inner join patrolcar_status on patrolcar.patrolcar_status_id = patrolcar_status.patrolcar_status_id;";

    $result = $conn->query($sql);
    $cars = [];
    while($row = $result->fetch_assoc()) {
		$id = $row["patrolcar_id"];
		$status = $row["patrolcar_status_desc"];
		$car = ["id"=>$id, "status"=>$status];
		array_push($cars, $car);
	}

    $btnDispatchClicked = isset($_POST["btnDispatch"]);
    $btnProcessCallClicked = isset($_POST["btnProcessCall"]);
    if($btnDispatchClicked == false && $btnProcessCallClicked == false) {
		header("location: logcall.php");
	}

    $sql = "SELECT incident.caller_name, incident.phone_number, incident_type.incident_type_desc, incident.incident_location, incident.incident_desc, incident.time_called, incident_status.incident_status_desc from incident inner join incident_type on incident.incident_type_id = incident_type.incident_type_id inner join incident_status on incident.incident_status_id = incident_status.incident_status_id where incident.incident_desc like " . "'%" . $descriptionOfIncident . "%'";
	
	$result = $conn->query($sql);
	$incidents = [];
	while($row = $result->fetch_assoc()) {
	    $caller_name = $row["caller_name"];
		$phone_number = $row["phone_number"];
		$incident_type = $row["incident_type_desc"];
		$location = $row["incident_location"];
		$incidentDesc = $row["incident_desc"];
		$time_called = $row["time_called"];
		$status = $row["incident_status_desc"];
		$incident = ["caller_name"=>$caller_name, "phone_number"=>$phone_number, "incident_type"=>$incident_type, "location"=>$location, "incident_desc"=>$incidentDesc, "time_called"=>$time_called, "status"=>$status];
		array_push($incidents, $incident);
	}
 
    if($btnDispatchClicked == true) {
		$insertIncidentSuccess = false;
		$hasCarSelection = isset($_POST["cbCarSelection"]);
		$patrolcarDispatched = [];
		$numOfPatrolCarDispatched = 0;
		if($hasCarSelection == true) {
			$patrolcarDispatched = $_POST["cbCarSelection"];
			$numOfPatrolCarDispatched = count($patrolcarDispatched);
		}
		$incidentStatus = 0;
		
		if($numOfPatrolCarDispatched > 0) {
			$incidentStatus = 2; //dispatched
		}
		else{
			$incidentStatus = 1; //pending
		}
		
		$callerName = $_POST["callerName"];
        $contactNo = $_POST["contactNo"];
        $locationOfIncident = $_POST["locationOfIncident"];
        $typeOfIncident = $_POST["typeOfIncident"];
        $descriptionOfIncident = $_POST["descriptionOfIncident"];
		
		$sql = "INSERT INTO `incident`(`caller_name`, `phone_number`, `incident_type_id`, `incident_location`, `incident_desc`, `incident_status_id`, `time_called`) VALUES ('" . $callerName . "','" . $contactNo . "','" . $typeOfIncident . "','" . $locationOfIncident . "','" . $descriptionOfIncident . "','" . $incidentStatus . "',now());";
		//echo $sql;
		$conn = new mysqli(DB_SERVER,DB_USER,DB_PASSWORD,DB_DATABASE);
		$insertIncidentSuccess = $conn->query($sql);
		if($insertIncidentSuccess == false) {
			echo "Error:" . $sql . "<br>" . $conn->error;
		}
		$incidentId = mysqli_insert_id($conn);
		//echo "<br>new incident id: " . $incidentId;
		$updateSuccess = false;
		$insertDispatchSuccess = false;
		
		foreach($patrolcarDispatched as $eachCarId) {
			//echo $eachCarId . "<br>";
			
			$sql = "UPDATE `patrolcar` SET `patrolcar_status_id`=1 WHERE `patrolcar_id`='" . $eachCarId . "'";
			$updateSuccess = $conn->query($sql);
			
			if($updateSuccess == false) {
				echo "Error:" . $sql . "<br>" . $conn->error;
			}
			
			$sql = "INSERT INTO `dispatch`(`incident_id`, `patrolcar_id`, `time_dispatched`) VALUES ('" . $incidentId . "','" . $eachCarId . "',now())";
			$insertDispatchSuccess = $conn->query($sql);
			
			if($insertDispatchSuccess == false) {
				echo "Error:" . $sql . "<br>" . $conn->error;
			}
		}
		$conn->close();
		
		if($insertDispatchSuccess == true && $updateSuccess == true && $insertDispatchSuccess == true) {
			header("location: logcall.php");
		} 
	}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Dispatch</title>
<link rel="stylesheet" href="css/bootstrap-4.4.1.css" type="text/css">
<style>
body {
  background-image: url('Background.png');
}
</style>
</head>
<body>
<div class="container" style="width:900px">
  <?php
	   include "header.php";
	?>
  <section class="mt-3">
    <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]) ?>" method="post">
      <div class="form-group row">
        <label for="callerName" class="col-sm-4 col-form-label">Caller's Name</label>
        <div class="col-sm-8">  
			<span>
				<?php echo $callerName; ?>
				<input type="hidden" id="callerName" name="callerName" value="<?php echo $callerName; ?>">
			</span>
        </div>
      </div>
      
		<div class="form-group row">
        <label for="contactNo" class="col-sm-4 col-form-label">Contact No</label>
        <div class="col-sm-8">
			<span>
				<?php echo $contactNo; ?>
				<input type="hidden" id="contactNo" name="contactNo" value="<?php echo $contactNo; ?>">	
			</span>
        </div>
      </div>
		
		<div class="form-group row">
        <label for="locationOfIncident" class="col-sm-4 col-form-label">Location of Incident</label>
        <div class="col-sm-8">
			<span>
				<?php echo $locationOfIncident; ?>
				<input type="hidden" id="locationOfIncident" name="locationOfIncident" value="<?php echo $locationOfIncident; ?>">
			</span>   
        </div>
      </div>
		
		<div class="form-group row">
        <label for="typeOfIncident" class="col-sm-4 col-form-label">Type of Incident</label>
        <div class="col-sm-8">
			<span>
				<?php echo $typeOfIncident; ?>
			    <input id="typeOfIncident" type="hidden" name="typeOfIncident" value="<?php echo $typeOfIncident; ?>">
			</span>
        </div>
      </div>
		
		<div class="form-group row">
        <label for="descriptionOfIncident" class="col-sm-4 col-form-label">Description of Incident</label>
        <div class="col-sm-8">
			<span>
				<?php echo $descriptionOfIncident; ?>
			    <input id="descriptionOfIncident" type="hidden" name="descriptionOfIncident" value="<?php echo $descriptionOfIncident; ?>">
			</span>
        </div>
      </div>

        <div class="form-group row">
        <label for="patrolCar" class="col-sm-4 col-form-label">Similar Incident(s)</label>
        <div class="col-sm-8">
			<table class="table table-striped">
			<tbody>
				<tr>
				    <th scope="col">Caller Name</th>
					<th scope="col">Phone Number</th>
					<th scope="col">Incident Type</th>
					<th scope="col">Incident Location</th>
					<th scope="col">Incident Description</th>
					<th scope="col">Incident Status</th>
					<th scope="col">Time Called</th>
				</tr>
				<?php
				    foreach($incidents as $incident) {
						echo "<tr>" . 
							"<td>" . $incident["caller_name"] . "</td>" . 
							"<td>" . $incident["phone_number"] . "</td>" .
							"<td>" . $incident["incident_type"] . "</td>" .
							"<td>" . $incident["location"] . "</td>" .
							"<td>" . $incident["incident_desc"] . "</td>" .
							"<td>" . $incident["status"] . "</td>" .
							"<td>" . $incident["time_called"] . "</td>" .
							"</tr>";
					}
				?>
		    </tbody>
			</table>
        </div>
      </div>
																													
		<div class="form-group row">
        <label for="patrolCar" class="col-sm-4 col-form-label">Choose Patrol Car(s)</label>
        <div class="col-sm-8">
			<table class="table table-striped">
			    <tbody>
				  <tr>
					<th>Car Number</th>
					<th>Status</th>
					<th></th>
				  </tr>
					
					<?php
					 foreach($cars as $car) {
						 echo "<tr>" . "<td>" . $car["id"] . "</td>" . "<td>" . $car["status"] . "</td>" . "<td>" . 
							 "<input type=\"checkbox\" " . "value=\"" . $car["id"] . "\" " . "name=\"cbCarSelection[]\">" . "</td>" . "</tr>";
					 }
					?>	
				</tbody>
			</table>
        </div>
      </div>
      <div class="form-group row">
        <div class="offset-sm-4 col-sm-8">
           <button type="submit" class="btn btn-primary" name="btnDispatch" id="submit">Dispatch</button>
        </div>
      </div>
     
    </form>
  </section>
	<?php
	   include "footer.php";
	?>
</div>
<script src="js/jquery-3.4.1.min.js"></script> 
<script src="js/popper.min.js"></script> 
<script src="js/bootstrap-4.4.1.js"></script>
</body>
</html>