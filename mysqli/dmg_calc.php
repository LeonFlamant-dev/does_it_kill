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




$mysqli = mysqli_connect($config['database']['hostname'], $config['database']['username'], $config['database']['password'], $config['database']['database']);
if (mysqli_connect_errno()) {
	die("Erreur connexion: " . mysqli_connect_error());
}

if($attack_type != 'active') {
	$query="SELECT p.id, p.nom, p.nbr_hit_".$attack_type.", p.traits, p.faction , ap.per_perfo
	FROM personnage p  
	left JOIN atk_perforation ap ON ap.nom = p.perfo_".$attack_type."
	WHERE p.nom ='".$_GET['attackant']."';";
}
// echo $query;
// exit;

//

$result = mysqli_query($mysqli,$query );

$characters = [];
while ($row = mysqli_fetch_assoc($result)) {
	$characters = $row;
}

$nbr_hit = $characters['nbr_hit_'.$attack_type]+0; // does nothing but help me by remaning a variable

$round = [];

$nbr_of_block = 0;
$nbr_of_crit = 0;




// $round[] = $thisround;



// on a pas les crit pour l'instant 
// for ($i = 0; $i < $nbr_hit; $i++) {
// 	// $nbr_of_crit = $i;
// 	for ($y = 0; $y < $nbr_hit; $y++) {
		// $nbr_of_block = $y;

		// ./DoesItKill.py dmg nbr_hit per vie armor nbr_crit = 0 c_crit=0 d_crit=0 nbr_bloc=0 c_bloc = 0 v_bloc = 0 d_ampli = 0 multi = 1
		// ./DoesItKill.py 30 2 0.3 200 20 1 0.25 12	

		// echo './DoesItKill.py '.$_GET['dmg'].' '.$nbr_hit.' '.$characters['per_perfo'].' '.$_GET['hp'].' '.$_GET['armor'];
		// exit;
		$retour_string = shell_exec('./../DoesItKill.py '.$_GET['dmg'].' '.$nbr_hit.' '.$characters['per_perfo'].' '.$_GET['hp'].' '.$_GET['armor']);//.' '.$i.' '.$chancecrit.' '.$dùgcrit.' '.$y.' '.$c_bloc.''.$v_bloc.''.$dmg_amp.''.$multiplicateur);
		// { "tdmg" : [0,0,0] , "proba" : 0 }
		// echo $retour_string;
		$python_array = json_decode($retour_string, true);

		$thisround['start_hp'] = $_GET['hp']+0;

		$thisround['nbr_of_crit'] = $nbr_of_crit;
		$thisround['nbr_of_block'] = $nbr_of_block;

		$thisround['min_dmg'] = $python_array['tdmg'][0];
		$thisround['med_dmg'] = $python_array['tdmg'][1];
		$thisround['max_dmg'] = $python_array['tdmg'][2];
		$thisround['proba'] = $python_array['proba'];

		$thisround['hp_min_dmg'] = $thisround['start_hp']-$python_array['tdmg'][0];
		$thisround['hp_med_dmg'] = $thisround['start_hp']-$python_array['tdmg'][1];
		$thisround['hp_max_dmg'] = $thisround['start_hp']-$python_array['tdmg'][2];

		if($thisround['hp_min_dmg'] < 0) {
			if(-$thisround['hp_min_dmg'] > $thisround['start_hp']) {$thisround['overkill'] = 1;}
			$thisround['hp_min_dmg'] = 0; }
		if($thisround['hp_med_dmg'] < 0) {
			if(-$thisround['hp_med_dmg'] > $thisround['start_hp']) {$thisround['overkill_med'] = 1;}
			$thisround['hp_med_dmg'] = 0; }
		if($thisround['hp_max_dmg'] < 0) {
			if(-$thisround['hp_max_dmg'] > $thisround['start_hp']) {$thisround['overkill_max'] = 1;}
			$thisround['hp_max_dmg'] = 0; }
		
		
		// doit etre des pourcentages
		$thisround['affichage_hp_min_dmg'] = $thisround['hp_max_dmg'] / $thisround['start_hp'] * 100;
	
		$thisround['affichage_hp_med_dmg'] = $thisround['hp_med_dmg'] / $thisround['start_hp'] * 100 - $thisround['affichage_hp_min_dmg'];
		
		$thisround['affichage_hp_max_dmg'] = $thisround['hp_min_dmg'] / $thisround['start_hp'] * 100 - $thisround['affichage_hp_med_dmg'] - $thisround['affichage_hp_min_dmg'];
		
		$round[] = $thisround;
// 	}

// }




header('Content-Type: application/json');
echo json_encode($round);

// mysqli_close($mysqli);
?>
