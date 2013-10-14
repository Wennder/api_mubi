<?php

error_reporting(E_ALL);
define("bar",constant("DIRECTORY_SEPARATOR"));
require_once('../nfephp/libs/DanfeNFePHP.class.php');
require_once('../nfephp/libs/ToolsNFePHP.class.php');
$fixpdf='1';//esta variavel decide se vai manter ou n�o o arquivo pdf
echo "<p align='center'><br>chamando o tools</p><BR>";
$nfe = new ToolsNFePHP('','1');
echo "<br> ";

$arq = $_GET['xml'];



if ( $nfe->errStatus ) {
       print $nfe->errMsg;
       die;
} else {
       print "<p align='center'>Configura��o adequada !</p><BR>";
}

print "<p align='center'><br>===================================================</p><BR>";

//inicalizar a classe de envio
$nfeMail = new MailNFePHP();
    //busca todas as notas a serem enviadas no diretorio validadas
  //  $aName = $nfe->listDir($nfe->aprDir,'*-nfe.xml');
  // print_r($aName);
  // echo "diretorio aprovadas $nfe->aprDir";
   // $n = count($aName);

        //if ( $n > 0 ) {
            //foreach($aName as $key => $file){
                $anomes = '20'.substr($file,2,4);
                //$nfefile=$nfe->aprDir.$file; '';

                $nfefile = $arq;

 
                $email='';
                $para = 'felipe.podestark@gmail.com';

                $html = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
                <html>
                <head>
                    <title>enviaMail</title>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                    <link rel='stylesheet' type='text/css' href='images/fimatec.css'>
                    <script language='javascript'>
                    <!--
                        setTimeout('self.close();',20000)
                    //-->
                    </script> 
                </head>
                <body>";
                
                if ($nfefile != ''){
                    //carregar o xml
                    //echo "<br> <b>tratando o arquivo $nfefile </b>";
                    $docXML = file_get_contents($nfefile);
                    $dom = new DomDocument;
                    $dom->loadXML($docXML);
                    $ide        = $dom->getElementsByTagName("ide")->item(0);
                    $emit       = $dom->getElementsByTagName("emit")->item(0);
                    $dest       = $dom->getElementsByTagName("dest")->item(0);
                    $obsCont    = $dom->getElementsByTagName("obsCont")->item(0);
                    $ICMSTot    = $dom->getElementsByTagName("ICMSTot")->item(0);

                    $razao = utf8_decode($dest->getElementsByTagName("xNome")->item(0)->nodeValue);
                    $cnpj = $dest->getElementsByTagName("CNPJ")->item(0)->nodeValue;
                    $numero = str_pad($ide->getElementsByTagName('nNF')->item(0)->nodeValue, 9, "0", STR_PAD_LEFT);
                    $serie = str_pad($ide->getElementsByTagName('serie')->item(0)->nodeValue, 3, "0", STR_PAD_LEFT);
                    $emitente = utf8_decode($emit->getElementsByTagName("xNome")->item(0)->nodeValue);
                    $vtotal = number_format($ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue, 2, ",", ".");
                    $email[] = !empty($dest->getElementsByTagName("email")->item(0)->nodeValue) ? utf8_decode($dest->getElementsByTagName("email")->item(0)->nodeValue) : '';
                    if (isset($obsCont)){
                        foreach ($obsCont as $obs){
                            $campo =  $obsCont->item($i)->getAttribute("xCampo");
                            $xTexto = !empty($obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue) ? $obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue : '';
                            if (substr($campo, 0, 5) == 'email' && $xTexto != '') {
                                $email[] = $xTexto;
                            }
                            $i++;
                        }
                    }
                    foreach($email as $e){
                        if ($nfeMail->validEmailAdd($e)){
                            $mailto[] = $e; 
                        } else {
                            echo "<p align='center'><b>O e-mail $e Não é um endereço válido, CORRIGIR !!<b></p><BR>";
                        } //fimda validaçao
                    }            
                    echo $html;
                    flush();
                    //verificar se tem endereços validos
                    if ( count($mailto)== 0 ){
                        echo "<p align='center'><b>$razao - Não há registro de e-mail para deste cliente.</b></p><BR>";
                    flush();
                    } else {
                    //inicializar a DANFE
                    $danfe = new DanfeNFePHP($docXML, 'P', 'A4','images/logo.jpg','I','');
                    //montar o PDF e o nome do arquivo PDF
                    $nome = (string)$danfe->montaDANFE();
                    $nomePDF = $nome . '.pdf';
                    $nomeXML = $nome . '-nfe.xml';
                    //carregar o arquivo pdf numa variavel
                    $docPDF = (string) $danfe->printDANFE($nomePDF,'S');
                    //gravar o danfe na pasta pdf
            
            if ($docPDF != ''){
                    
                    //grava a DANFE como pdf no diretorio
                    if (!file_put_contents($nfe->pdfDir.$nomePDF,$docPDF)){
                        //houve falha na gravação
                        $nfe->errMsg = "Falha na gravação do pdf.\n";
                        $nfe->errStatus = true;
                    } else {
                        //em caso de sucesso, verificar se foi definida a printer se sim imprimir
                        //este comando de impressão funciona tanto em linux como em wndows se o 
                        //ambiente estiver corretaente preparado
                        if ( $nfe->danfeprinter != '' ) {
                                $command = "lpr -P $nfe->danfeprinter $nfe->pdfDir$nomePDF";
                                system($command);
                            }
                    }
            } else {
                                //houve falha na geração da DANFE
                                $nfe->errMsg = "Falha na geração da DANFE.\n";
                                $nfe->errStatus = true;
                    }
                    //enviar o email e testar
                        foreach($mailto as $para){
                            //para testes
                            //$para = 'roberto.machado@superig.com.br';
                            $aMail = array('emitente'=>$emitente,'para'=>$para,'contato'=>$contato,'razao'=>$razao,'numero'=>$numero,'serie'=>$serie,'vtotal'=>$vtotal);
                            echo "<p align='center'>Enviando e-mail com a NFe N. $numero para $para - $razao </p>";
                            flush();
                            if ( $nfeMail->sendNFe($docXML,$docPDF,$nomeXML,$nomePDF,$aMail,'1') ){
                                echo '<p align="center">E-mail enviado com sucesso!! </p><br>';
                            } else {
                                echo "<p>$nfeMail->mailERROR</p><br>";
                            }
                                        //mover o arquivo xml para a pasta de arquivamento identificada com o ANOMES
                                $diretorio = $nfe->aprDir.$anomes.DIRECTORY_SEPARATOR;
                                if ( !is_dir($diretorio) ) {
                                      mkdir($diretorio, 0777);
                                }
                                rename($nfefile,$diretorio.$file);
                                //apagar o pdf criado para envio
                                if (!$fixpdf){
                                    if (is_file($nfe->pdfDir.$nomePDF)){
                                        echo "Apagando PDF temporario";
                                        unlink($nfe->pdfDir.$nomePDF);
                                    }
                                }
                        }    
                    }//fim dos emails
                    echo "<div><center><form method='POST' action=''><p><input type='button' value='Fechar' name='B1' onclick='self.close()'></p></form></center></div>";
                    echo "</body></html>";
                    flush();
                }
        //}
        //}else{
        //    echo "nada a enviar nfefile esta vazio";
        //    }
        //fim nfe

?>
