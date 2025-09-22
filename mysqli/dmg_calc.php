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

$result = mysqli_query($mysqli,$query );

$characters = [];
while ($row = mysqli_fetch_assoc($result)) {
	$characters = $row;
}

//on recupere les items de l'attaquant


$data = json_decode($_GET['itemsattaquant'], true); 
// print_r(($data));

//pour le perso 1 on veux juste les chance de crit total 
$crit_boost = 0;
$crit_chance = 0;
$crit_value = 0;
for ($i = 2; $i >= 0; $i--)
{
	print($data[$i]);
	switch ($data['item_id_'.$i][0]) {
		case "crit":
			if($crit_chance == 0) 
			{
				$crit_chance = ($data['item_id_'.$i][1]+$crit_boost)/100;
				$crit_value = $crit_value+$data['item_id_'.$i][2];
			}
			else
			{
				$crit_chance = ($data['item_id_'.$i][0]+$crit_boost)/100 + (1-($data['item_id_'.$i][1]+$crit_boost)/100)*$crit_chance;
				$crit_value = $crit_value+$data['item_id_'.$i][2];
			}
			break;
		case "crit_boost":
			$crit_boost = $data['item_id_'.$i][1];
			$crit_value = $crit_value+$data['item_id_'.$i][2];
			break;
	}

}


//pour le perso 2 on veux juste les stats et les chance de block total 
$data = json_decode($_GET['itemsdefenceur'], true); 
// print_r(($data));
$block_chance = 0;
$block_value = 0;
$hp = $_GET['hp']+0;
$armor = $_GET['armor']+0;

for ($i = 2; $i >= 0; $i--)
{
	switch ($data['item_id_'.$i][0]) {
		case "defensive":
			$hp = $data['item_id_'.$i][1];
			$armor = $data['item_id_'.$i][2];
			break;
		case 'block':
			$block_chance = ($data['item_id_'.$i][1]+$crit_boost)/100;
			$block_value = $block_value+$data['item_id_'.$i][2];
			break;
		case "crit_boost":
			$block_chance = $data['item_id_'.$i][1];
			$block_value = $block_value+$data['item_id_'.$i][2];
			break;
	}

}
	

// todo DMG AMPLIFICATION et MUltiplicateur
$dmg_amp = 0;
$multiplicateur = 1;



$nbr_hit = $characters['nbr_hit_'.$attack_type]+0; // does nothing but help me by remaning a variable

$round = [];

$nbr_of_block = 0;
$nbr_of_crit = 0;
// on a pas les crit pour l'instant 
for ($i = 0; $i <= $nbr_hit; $i++) {
	$nbr_of_crit = $i;
	for ($y = 0; $y <= $nbr_hit; $y++) {
		$nbr_of_block = $y;
		if($block_chance == 0 && $nbr_of_block > 0) {continue;}
		if($crit_chance == 0 && $nbr_of_crit > 0) {continue;}

		// ./DoesItKill.py dmg nbr_hit per vie armor nbr_crit = 0 c_crit=0 d_crit=0 nbr_bloc=0 c_bloc = 0 v_bloc = 0 d_ampli = 0 multi = 1
		// ./DoesItKill.py 30 2 0.3 200 20 1 0.25 12	

		// echo './../DoesItKill.py '.$_GET['dmg'].' '.$nbr_hit.' '.$characters['per_perfo'].' '.$hp.' '.$armor.' '.$nbr_of_crit.' '.$crit_chance.' '.$crit_value.' '.$nbr_of_block.' '.$block_chance.' '.$block_value.' '.$dmg_amp.' '.$multiplicateur;
		// exit;
		$retour_string = shell_exec('./../DoesItKill.py '.$_GET['dmg'].' '.$nbr_hit.' '.$characters['per_perfo'].' '.$hp.' '.$armor.' '.$nbr_of_crit.' '.$crit_chance.' '.$crit_value.' '.$nbr_of_block.' '.$block_chance.' '.$block_value.' '.$dmg_amp.' '.$multiplicateur);
		// { "tdmg" : [0,0,0] , "proba" : 0 }
		// echo $retour_string;
		$python_array = json_decode($retour_string, true);

		$thisround['start_hp'] = $_GET['hp']+0;

		$thisround['nbr_of_crit'] = $nbr_of_crit;
		$thisround['nbr_of_block'] = $nbr_of_block;

		$thisround['min_dmg'] = $python_array['tdmg'][0];
		$thisround['med_dmg'] = $python_array['tdmg'][1];
		$thisround['max_dmg'] = $python_array['tdmg'][2];
		$thisround['proba'] = round($python_array['proba'], 4);

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
		
		if($thisround['proba']>0)
		{
			$round[] = $thisround;
		}
		
	}

}




header('Content-Type: application/json');
echo json_encode($round);

// mysqli_close($mysqli);
?>
