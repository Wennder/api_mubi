<?php  
	include_once('NFe.class.php');
	$NFe = new NFe();
	$id = $_POST['nota_id'];
	$busca_nota = mysql_query("SELECT * FROM notas_fiscais WHERE id = {$id}");
	$bn = mysql_fetch_array($busca_nota);
	$NFe->protocolo = $bn['protocolo'];
	$NFe->chave = $bn['chave'];
	// $NFe->justificativa = $_POST['justificativa'];
	$NFe->justificativa = "Testando cancelamento de nota";
	$NFe->notaId = $id;
	if($NFe->cancelaNota()){
		echo json_encode(array('success' => true));
	}else{
		echo json_encode(array('success' => false, 'msg' => "O cancelamento da nota foi rejeitado!"));	
	}


?>