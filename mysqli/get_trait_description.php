<?php
// retur all nom, prettyname available in the db
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = parse_ini_file("../conf.ini", true);

// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;
$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);

if (mysqli_connect_errno()) {

	die("Erreur connexion: " . mysqli_connect_error());
}
$result = mysqli_query($mysqli, "SELECT t.description  FROM trait t WHERE t.nom = '".$_GET['trait']."'");

$trait = [];
while ($row = mysqli_fetch_assoc($result)) {
	$trait = $row;
}

header('Content-Type: application/json');
echo json_encode($trait);

mysqli_close($mysqli);
?>
