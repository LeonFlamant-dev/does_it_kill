<?php

$config = parse_ini_file("../conf.ini", true);

// print_r($config);
// echo"mysql -h '". $config['database']['hostname'] ."' --port 3306 -p'". $config['database']['password'] ."' -u '". $config['database']['username'] ."'";
// exit;


switch ($_GET['attack_type']) {
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

// attackant=" + perso_attackant + "&defenceur=" + perso_defenceur + "&attack_type=" + attack_type+ "&dmg=" + dmg_attackant+ "&hp=" + hp_defenceur+ "&armor=" + armor_defenceur




// $mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);
// if (mysqli_connect_errno()) {
// 	die("Erreur connexion: " . mysqli_connect_error());
// }

// $query="SELECT p.id, p.nom, p.nbr_hit_mele, p.perfo_mele, p.nbr_hit_range, p.perfo_range, p.item1, p.item2, p.item3, p.traits, p.faction 
// FROM personnage p  
// WHERE p.nom ='".$_GET['perso_attackant']."';";

// echo $query;
// exit;

// $result = mysqli_query($mysqli,$query );

// $characters = [];
// while ($row = mysqli_fetch_assoc($result)) {
// 	$characters = $row;
// }



$round = [];
for ($i = 0; $i < 3; $i++) {
	$thisround['round'] = $i;
	$thisround['overkill'] = 0;

	// a remplacer par une fonction marius
	switch ($i) {
	case 0:
		$dmgdealt = $_GET['dmg'] * 0.8;
		$thisround['type_of_round'] = 'lowest damage';
		break;
	case 1:
		$dmgdealt = $_GET['dmg'] *1.0;
		$thisround['type_of_round'] = 'normal damage';
		break;
	default:
		$dmgdealt = $_GET['dmg'] * 1.2;
		$thisround['type_of_round'] = 'higher damage';
		break;
}
	// $dmgdealt = $dmgdealt * $characters['hit'];
	$thisround['dmg_dealt'] = round($dmgdealt);

	
	$remainhp = $_GET['hp'] - $dmgdealt;

	if ($remainhp < 0) {
		if ($remainhp * -1 > $_GET['hp']) {
			$thisround['overkill'] = 1;
		}
		$remainhp = 0;
	}

	$thisround['remain_hp'] = $remainhp;
	$thisround['start_hp'] = $_GET['hp'];
	$round[] = $thisround;
}




// goal iterate over hit to calculate damage for heach crit possibility

// if target canot block 










// if can block



header('Content-Type: application/json');
echo json_encode($round);

// mysqli_close($mysqli);
?>
