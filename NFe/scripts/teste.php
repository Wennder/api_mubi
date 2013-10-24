<?php  
	include_once('NFe.class.php');

	$NFe = new NFe();
	// $NFe->numeroNota = 61;
	// $NFe->clienteId = 15;
	// $NFe->osIds = '35';

	$c = $NFe->removeCaracterEspecial(substr('Lona front nos tamanhos de 4 00 por 1 00 e acabamento Reforço e ilhós normal impresso em HP Latex na quantidade de 1', 0, 119));

	echo $c;
?>