<?php  
	include_once('NFe.class.php');
	$NFe = new NFe();
	$id = $_GET['nota'];
	$busca_nota = mysql_query("SELECT * FROM notas_fiscais WHERE id = 4");
	$bn = mysql_fetch_array($busca_nota);

	$NFe->chave = $bn['chave'];

	$NFe->cartaCorrecao();
?>