<?php
require_once("ressources/strings.inc");
require_once("relations.php");
require_once("pticlic.php");
session_start();

$state = 0;
$err = false;
$msg = "";

function getWords($nbwords)
{
	global $msg;
	global $err;
	$words = array();	

	for($i = 0; $i < $nbwords; $i++)
		if(!isset($_POST['word'.$i]) || empty($_POST['word'.$i])) {
			$err = true;
			$msg = $strings['err_creategame_fill_all'];
			return -1;
		}
		else
			$words[$i] = $_POST['word'.$i];

	return $words;
}

function getWordsAndResponses($nbwords)
{
	global $err;
	global $msg;
	$words = array();
	$respwords = array();
	
	$words = getWords($nbwords);

	if($words == -1)
		return -1;

	foreach($words as $key=>$w) {
		if(isset($_POST['rd'.$key])) {
			$respwords[$key] = array();
			$respwords[$key][0] = $words[$key];
			$respwords[$key][1] = $_POST['rd'.$key];
		}
		else
			return -1;
	}

	return $respwords;
}

function checked($name, $value) {
	if(isset($_POST[$name]) && $_POST[$name] == $value)
		return 'checked';
}

function probaOf($relation, $relation2) {
	return 0;
}

if(isset($_POST['nbcloudwords'])) {
	$nbwords = $_POST['nbcloudwords'];

	if(!is_numeric($nbwords) || $nbwords <= 0) {
		$err = true;
		$msg = $strings['err_creategame_nbwords_value'];
	}
	else {
		$state = 1;
		$relations = get_game_relations();
	}
	
	if($state == 1 && isset($_POST['centralword']) && !empty($_POST['centralword'])) {
		if($_POST['relation1'] != $_POST['relation2']) {		
			$centralword = $_POST['centralword'];			
			$rels[0] = $relations[$_POST['relation1']][1];
			$rels[1] = $relations[$_POST['relation2']][1];
			$rels[2] = "Est en rapport avec";
			$rels[3] = "N'a aucun rapport avec";
			
			$words = getWords($nbwords);

			if($err != true)
				$state = 2;
		}
		else {
			$err = true;
			$msg = $strings['err_creategame_eq_relations'];
		}
	}
	
	if($state == 2) {
		$respwords = getWordsAndResponses($nbwords);
		$r1 = $stringRelations[$_POST['relation1']];
		$r2 = $stringRelations[$_POST['relation2']];
		$cloud = array();
		$totalDifficulty = 0;

		insertNode($centralword);
		$centralword = getNodeEid($centralword);

		if($respwords != -1) {
			foreach($respwords as $key=>$rw) {
				insertNode($respwords[$key][0]);
				$cloud[$key] = array('pos'=>$key, 'd'=>1, 'eid'=>getNodeEid($respwords[$key][0]),
									'probaR1'=> probaOf("r1", $rw[1]),
									'probaR2'=> probaOf('r2', $rw[1]),
									'probaR0'=> probaOf('r0', $rw[1]),
									'probaTrash'=> probaOf('trash', $rw[1]));

				$totalDifficulty += 1;
			}
		}

		cgInsert($centralword, $cloud, $r1, $r2, $totalDifficulty);

	}
	else {
		$err = true;
		$msg = $strings['err_creategame_fill_all'];
	}
}
else
	$err = true;



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>	
		<div class="content creategame">
			<h2>Création de parties</h2>
			<?php
				if(isset($_POST['nbcloudwords']) && $_POST['nbcloudwords'] > 0)
					echo '<p>Remplissez le mot central ainsi que les différents mots du nuage pour réaliser un partie personalisée.<br />
						Une fois satisfait de votre partie cliquez sur "Enregistrer la partie"';
				else
					echo '<p>Cette page vous permet de créer des parties personalisées en indiquant les mots qui seront affiché pour un mot central.<br /><br />
						Veuillez entrer le nombre de mots composant le nuage dans le formulaire ci-dessous avant de continuer.</p><br />';
			?>
			<form action="createGame.php" method="POST">
					<?php
					if($err == true && $msg != "")
						echo '<span class="message warning">'.$msg.'</span>';
					else if ($msg != "")
						echo '<span class="message success">'.$msg.'</span>';

					if($state == 0) {
						echo '<table>';
						echo '<tr><td><label for="nbcloudwords"> Nombre de mots du nuage : </label></td>';
						echo '<td><input type="text" name="nbcloudwords" /></td></tr>';
						echo '<tr><td></td><td><input type="submit" value="suivant" /></td></tr>';			
					}
					elseif($state == 1) {
						echo '<table class="wordsform">';
						echo '<tr><td><label for="relation1">Relation 1 : </label></td>';
						echo '<td class="inputcell"><select name="relation1">';
							foreach($relations as $key=>$r)
								echo '<option value="'.$key.'">'.$stringRelations[$r].'</option>';
						echo '</select></td>';
						echo '<td><label for="relation2">Relation 2 : </label></td>';
						echo '<td class="inputcell"><select name="relation2">';
							foreach($relations as $key=>$r)
								echo '<option value="'.$key.'">'.$stringRelations[$r].'</option>';
						echo '</select></td>';
						echo '<input type="hidden" name="nbcloudwords" value="'.$nbwords.'" />';
						echo '<tr><td colspan="2"><br /><label for="centralword">Mot central : </label><br /><br /></td>';
						echo '<td colspan="2" class="inputcell"><br /><input type="text" name="centralword" value="';
							if(isset($_POST['centralword'])) echo $_POST['centralword'];
						echo '"/><br /><br /></td>';
				
						for($i = 0; $i < $nbwords; $i++) {
							if($i % 2 == 0)
								echo '</tr><tr>';								

							echo '<td><label for="word'.$i.'">Mot '.($i+1).' : </label></td>';
							echo '<td class="inputcell"><input type="text" name="word'.$i.'" value="';
								if(isset($_POST['word'.$i])) echo $_POST['word'.$i];
							echo '" /></td>';
						}
				
						if($nbwords % 2 != 0)
							echo '<td></td>';

						echo '</tr><tr><td colspan="2"></td><td colspan="2" class="td2"><input type="submit" value="Enregistrer la partie" /></td></tr>';
					}
					else {
						echo 'Mot central : ';
						echo $centralword;
						echo '<input type="hidden" name="centralword" value="'.$centralword.'" />';
						echo '<input type="hidden" name="nbcloudwords" value="'.$nbwords.'" />';
						echo '<input type="hidden" name="relation1" value="'.$_POST['relation1'].'" />';
						echo '<input type="hidden" name="relation2" value="'.$_POST['relation2'].'" />';
						echo '<table class="wordsform">';
						echo '<tr>';

						foreach($words as $key=>$w) {
							echo '<td>'.$w.'</td><td class="inputcell">';
							echo '<input type="hidden" name="word'.$key.'" value="'.$w.'" />';
							echo '<input type="radio"  name="rd'.$key.'" id="'.$key.'_r1" value="0" '.checked("rd".$key,0).'>';
							echo '<label for="'.$key.'_r1">'.$rels[0].'</label><br />';
							echo '<input type="radio" name="rd'.$key.'" id="'.$key.'_r2" value="1" '.checked("rd".$key,1).'>';
							echo '<label for="'.$key.'_r2">'.$rels[1].'</label><br />';
							echo '<input type="radio" name="rd'.$key.'" id="'.$key.'_r3" value="2" '.checked("rd".$key,2).'>';
							echo '<label for="'.$key.'_r3">'.$rels[2].'</label><br />';
							echo '<input type="radio" name="rd'.$key.'" id="'.$key.'_r4" value="3" '.checked("rd".$key,3).'>';
							echo '<label for="'.$key.'_r4">'.$rels[3].'</label></td>';

							if($key%2 != 0)
								echo '</tr><tr>';
						}
						
						if(count($words)%2 != 0)
							echo '<td colspan="2"></td>';

						echo '</tr>';
						echo '<tr><td colspan="4"><input type="submit" value="Enregistrer" /></td></tr>';
					}
					?>
				</table>
			</form>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
