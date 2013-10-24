<?php  
	header('Content-type: text/html; charset=UTF-8');
	
	include_once('NFe.class.php');
	$NFe = new NFe();
	$id = $_GET['nota'];
	$busca_nota = mysql_query("SELECT * FROM notas_fiscais WHERE id = {$id}");
	$bn = mysql_fetch_array($busca_nota);
	$NFe->notaId = $bn['id'];
	$NFe->chave = $bn['chave'];
	$NFe->status = $bn['status'];
	$NFe->geraDanfe();


	#consultar recibo 
	// // print_r($bn);
	// $NFe->chave = "";
	// $NFe->recibo = $bn['recibo'];

	// echo $NFe->consultaRecibo();
?>