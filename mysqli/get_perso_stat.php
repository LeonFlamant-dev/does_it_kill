<?php

$config = parse_ini_file("../conf.ini", true);

// print_r($config);
// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;
// // mysql -h "database-1.ctquc2yogeul.eu-west-3.rds.amazonaws.com" --port 3306 -p"theleonzio974" -u admin

$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);
if (mysqli_connect_errno()) {
	die("Erreur connexion: " . mysqli_connect_error());
}

$result = mysqli_query($mysqli, "SELECT id, nom, nbr_hit_mele, perfo_mele, nbr_hit_range, perfo_range, item1, item2, item3, traits, faction, perso_stat.hp as hp, perso_stat.armor as armor, perso_stat.dmg as dmg FROM personnage left join perso_stat on nom = '".$_GET['nom']."' AND palier = '".$_GET['palier']."' WHERE nom ='".$_GET['nom']."';");


while ($row = mysqli_fetch_assoc($result)) {
	$characters = $row;
}

header('Content-Type: application/json');
echo json_encode($characters);

mysqli_close($mysqli);
?>
