<?php

$config = parse_ini_file("../conf.ini", true);

// print_r($config);
// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;


switch ($_GET['palier']) {
	case "1":
		$attack_type = "mele";
		break;
	case "2":
		$attack_type = "range";
		break;
	case "3":
		$attack_type = "active";
		break;
}


$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);
if (mysqli_connect_errno()) {
	die("Erreur connexion: " . mysqli_connect_error());
}

$query="SELECT p.id, p.nom, p.nbr_hit_mele, p.perfo_mele, p.nbr_hit_range, p.perfo_range, p.item1, p.item2, p.item3, p.traits, p.faction, s.hp, s.armor, s.dmg 
FROM personnage p 
LEFT JOIN perso_stat s 
ON p.nom = s.nom AND palier = '".$_GET['palier']."' 
WHERE p.nom ='".$_GET['nom']."';";

// echo $query;
// exit;

$result = mysqli_query($mysqli,$query );


while ($row = mysqli_fetch_assoc($result)) {
	$characters = $row;
}

header('Content-Type: application/json');
echo json_encode($characters);

mysqli_close($mysqli);
?>
