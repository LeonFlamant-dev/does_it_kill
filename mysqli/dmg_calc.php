<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
$config = parse_ini_file("../conf.ini", true);

// dmg_calc.php?attaque=" + encoded_atk + "&defence=" + encoded_def + "&itemsattaquant=" + encoded_item_id_1 + "&itemsdefence=" + encoded_item_id_2 + "&buffs=" + encodedBuffs
// perso_attaque_array = [perso_attaque,attack_type,dmg_attaque,palier_attaque,faction_attaque];
// perso_defence_array = [perso_defence,hp_defence,armor_defence,palier_defence,faction_defence];
// items[`item_id_${i}`] = [item_selected, item_stat_1, item_stat_2];
// buffs = [1,2,3]
$data_perso_atk = json_decode($_GET['attaque'], true); 
$data_perso_def = json_decode($_GET['defence'], true); 
switch ($data_perso_atk[1]) {
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

if($attack_type != 'active') {
	$query="SELECT p.id, p.nom, p.nbr_hit_".$attack_type.", p.traits, p.faction , ap.per_perfo
	FROM personnage p  
	left JOIN atk_perforation ap ON ap.nom = p.perfo_".$attack_type."
	WHERE p.nom ='".$data_perso_atk[0]."';";
}
else {
	// faire la query pour l'actif du perso


	// ensuie parsé la reponse pour avoir les info importante
}

$result = mysqli_query($mysqli,$query );

$attaque_stats = [];
while ($row = mysqli_fetch_assoc($result)) {
	$attaque_stats = $row;
}

//on recupere les items de l'attaquant


$data_item_attaque = json_decode($data_perso_atk[0], true); 
// print_r(($data));

//pour le perso 1 on veux juste les chance de crit total 
$crit_boost = 0;
$crit_chance = 0;
$crit_value = 0;
for ($i = 2; $i >= 0; $i--)
{
	print($data_item_attaque[$i]);
	switch ($data_item_attaque['item_id_'.$i][0]) {
		case "crit":
			if($crit_chance == 0) 
			{
				$crit_chance = ($data_item_attaque['item_id_'.$i][1]+$crit_boost)/100;
				$crit_value = $crit_value+$data_item_attaque['item_id_'.$i][2];
			}
			else
			{
				$crit_chance = ($data_item_attaque['item_id_'.$i][0]+$crit_boost)/100 + (1-($data_item_attaque['item_id_'.$i][1]+$crit_boost)/100)*$crit_chance;
				$crit_value = $crit_value+$data_item_attaque['item_id_'.$i][2];
			}
			break;
		case "crit_boost":
			$crit_boost = $data_item_attaque['item_id_'.$i][1];
			$crit_value = $crit_value+$data_item_attaque['item_id_'.$i][2];
			break;
	}

}


//pour le perso 2 on veux juste les stats et les chance de block total 
$data_item_def = json_decode($_GET['itemsdefenceur'], true); 
// print_r(($data));
$block_chance = 0;
$block_value = 0;
$hp = $data_perso_def[1]+0;
$armor = $data_perso_def[2]+0;

for ($i = 2; $i >= 0; $i--)
{
	switch ($data_item_def['item_id_'.$i][0]) {
		case "defensive":
			$hp = $data_item_def['item_id_'.$i][1];
			$armor = $data_item_def['item_id_'.$i][2];
			break;
		case 'block':
			$block_chance = ($data_item_def['item_id_'.$i][1]+$crit_boost)/100;
			$block_value = $block_value+$data_item_def['item_id_'.$i][2];
			break;
		case "crit_boost":
			$block_chance = $data_item_def['item_id_'.$i][1];
			$block_value = $block_value+$data_item_def['item_id_'.$i][2];
			break;
	}

}
	

// parser de buffs
$nbr_hit = $attaque_stats['nbr_hit_'.$attack_type]+0;
$dmg = $data_perso_atk[2];
$dmg_amp = 0;
$multiplicateur = 1;

$buffs = json_decode($_GET['buffs'], true); 
if($buffs != null)
{
	$query_buff = "SELECT b.descript, a.X, a.Y, a.Z FROM buff b LEFT JOIN ability_stat a ON b.name = a.name AND a.palier = '".$data_perso_atk[3]."' WHERE b.id IN (".implode(',', $buffs).")";
	$result_buff = mysqli_query($mysqli,$query_buff );
	while ($row = mysqli_fetch_assoc($result_buff)) {
		$attaque_desc = str_replace(
			['X', 'Y', 'Z'],
			[$row['X'], $row['Y'], $row['Z']],
			$row['descript']
		);

		$attaque_desc = explode(' ',$attaque_desc);
		$taille_attaque = count($attaque_desc);
		for($indice = 0; $indice < $taille_attaque; $indice++) {
			if($attaque_desc[$indice] != 'allperso' && $attaque_desc[$indice] != $data_perso_atk[4])
			{

				while($attaque_desc[$indice] != ',' && $indice < $taille_attaque)
				{
					$indice++;
				}
				continue;
			}
			$indice++;

			if($indice >= $taille_attaque) {break;}
			if($attaque_desc[$indice] != 'alltype' && ($attaque_desc[$indice] != $attack_type && ($attaque_desc[$indice] == 'n_attaque' && $attack_type == 'active')))
			{

				while($attaque_desc[$indice] != ',' && $indice < $taille_attaque)
				{
					$indice++;
				}
				continue;
			}
			if($indice >= $taille_attaque) {break;}
			$indice++;


			switch ($attaque_desc[$indice]) {
				case "mult":
					$multiplicateur+= $attaque_desc[$indice+1]; 
					$indice+=2;
					break;
				case "deal":
					$dmg+= $attaque_desc[$indice+1]+0; 
					$indice+=2;
					break;
				case "take":
					$dmg_amp+= $attaque_desc[$indice+1]+0; 
					$indice+=2;
					break;
				case "add_hit":
					$nbr_hit+= $attaque_desc[$indice+1]+0; 
					$indice+=2;
					break;
				default:
					$indice++;
					break;
			}
			break;
		}
		
	}
} 


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

		// echo 'DoesItKill.py '.$dmg.' '.$nbr_hit.' '.$attaque_stats['per_perfo'].' '.$hp.' '.$armor.' '.$nbr_of_crit.' '.$crit_chance.' '.$crit_value.' '.$nbr_of_block.' '.$block_chance.' '.$block_value.' '.$dmg_amp.' '.$multiplicateur;
		// exit;
		$retour_string = shell_exec('./../DoesItKill.py '.$dmg.' '.$nbr_hit.' '.$attaque_stats['per_perfo'].' '.$hp.' '.$armor.' '.$nbr_of_crit.' '.$crit_chance.' '.$crit_value.' '.$nbr_of_block.' '.$block_chance.' '.$block_value.' '.$dmg_amp.' '.$multiplicateur);
		// { "tdmg" : [0,0,0] , "proba" : 0 }
		// echo $retour_string;
		$python_array = json_decode($retour_string, true);

		$thisround['start_hp'] = $hp;

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