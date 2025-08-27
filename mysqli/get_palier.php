<?php
// return all palier available in the db
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = parse_ini_file("../conf.ini", true);

// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;
$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);

if (mysqli_connect_errno()) {

	die("Erreur connexion: " . mysqli_connect_error());
}
$result = mysqli_query($mysqli, "SELECT id, palier FROM palier");

$characters = [];
while ($row = mysqli_fetch_assoc($result)) {
	$characters[] = $row;
}

header('Content-Type: application/json');
echo json_encode($characters);

mysqli_close($mysqli);
?>
