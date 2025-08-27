
<?php
// get [nom] [palier] [item_nbr]
// return crit, hp, armor bonus of all item of type item_type for the faction of the perso of the perso

$config = parse_ini_file("../conf.ini", true);

// print_r($config);
// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;

$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);
if (mysqli_connect_errno()) {
	die("Erreur connexion: " . mysqli_connect_error());
}

// list des crit du perso 
$query ='SELECT i.nom, s.crit_dmg, s.hp, s.armor from personnage p 
JOIN item i on p.item1 = i.type
RIGHT JOIN item_faction f on i.nom = f.item AND f.faction = p.faction
LEFT JOIN item_stat s on i.nom = s.nom AND s.palier = "'.$_GET['palier'].'"
WHERE p.nom = "'.$_GET['nom'].'";';


$result = mysqli_query($mysqli,$query );

$item = [];
while ($row = mysqli_fetch_assoc($result)) {
	$item = $row;
}

header('Content-Type: application/json');
echo json_encode($item);

mysqli_close($mysqli);
?>
