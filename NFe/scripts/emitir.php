<?php  
	include_once('NFe.class.php');

	$NFe = new NFe();
	if(isset($_POST['os_ids']) && !empty($_POST['os_ids'])){
		$NFe->osIds = $_POST['os_ids'];
	}else{
		echo json_encode(array('succes' => false, 'O id da OS deve ser passado!'));
		exit;
	}

	if(isset($_POST['numero_nota']) && !empty($_POST['numero_nota'])){
		$NFe->numeroNota = $_POST['numero_nota'];
	}else{
		echo json_encode(array('succes' => false, 'O numero da nota deve ser passado!'));
		exit;
	}
	
	$r = $NFe->gerarXml();
	if($r['success']){
		if($NFe->assinaXml()){
			if($NFe->validaXml()){
				if($NFe->enviaXml()){
					if($NFe->consultaRecibo()){					
						echo json_encode(array('success' => true));
					}else{
						echo json_encode(array('success' => false, 'msg' => "A nota foi recusa pelo SEFAZ!"));	
					}
				}else{
					echo json_encode(array('success' => false, 'msg' => "Erro ao enviar o XML"));
				}
			}else{
				echo json_encode(array('success' => false, 'msg' => "Erro ao validar o XML"));
			}
		}else{
			echo json_encode(array('success' => false, 'msg' => "Erro ao assinar o XML"));
		}
	}else{
		echo json_encode(array('success' => false, 'msg' => $r['msg']));
	}
?>

