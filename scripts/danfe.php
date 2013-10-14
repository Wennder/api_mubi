<?php

//error_reporting(0);
session_start("nfe");
$_SESSION["numerodorecibo"] = "";
//$_SESSION["lendo"] = "sim";
$tpAmb = 2;
$numerodorecibo = '';
require_once("NFeTXT2.class.php");
$nfe = new  NFeTXT2;
$nfe->setVersao("2.00");
$nfe->setCUF("43");
$nfe->setCNF("00300000");
$nfe->setNatOp("VENDA");
$nfe->setIndPag("0");
$nfe->setMod("55");
//PEGAR PARAMETRO
$nfe->setSerie("55");



$nro_nfe = "1027";




$nfe->setNNF($nro_nfe);


$nfe->setDEmi("2012-06-08"); // ( aaaa-mm-dd ) ficar atento com o formato
$nfe->setDSaiEnt("");
$nfe->setHSaiEnt("");
$nfe->setTpNF("1");
$nfe->setCMunFG("4310207");
$nfe->setTpImp("1");
$nfe->setTpEmis("1");
//$nfe->setCDV("0"); // é gerado automaticamente
$nfe->setTpAmb("2"); // 1-Produção/ 2-Homologação
$nfe->setFinNFe("1");
$nfe->setProcEmi("3");
$nfe->setVerProc("2.0.3");
$nfe->setDhCont("");
$nfe->setXJust("");

//Dados do emitente
$emi[XNome]  = "xxxxxxxxxxxxx";
$emi[XFant]  = "xxxxxxxxxxxxxx";
$emi[IE] 	 = "9999999999";
$emi[IEST]   = "";
$emi[IM]     = "";
$emi[CNAE]   = "";
$emi[CRT]    = "3";
$emi[CNPJ]   = "999999999999";
//$emi[CPF]  = "";
$emi[xLgr]   = "RUA DOS";
$emi[nro]    = "123";
$emi[Cpl] 	 = "132";
$emi[Bairro] = "123edas";
$emi[CMun]   = "4310207";
$emi[XMun]   = "sdfsdf";
$emi[UF]     = "SC";
$emi[CEP]    = "98700000";
$emi[cPais]  = "1058";
$emi[xPais]  = "BRASIL";
$emi[fone]   = "5533322084";

$nfe->setEmi($emi);


//destinatario
$dest[xNome]   = "AAAAAAAAAAAAAAAAAAAA";
$dest[IE]      = "9999999999999";
$dest[ISUF]    = "";
$dest[email]   = "fp@gmail.com";
$dest[CNPJ]    = "9999999999999999";
//$dest[CPF]   = "72496207034";
$dest[xLgr]    = "RUA JORGE XXXXXXXXXXXX";
$dest[nro]     = "54";
$dest[xCpl]    = "SEM COMPLEMENTO";
$dest[xBairro] = "centro";
$dest[cMun]    = "4310207";
$dest[xMun]    = "Ijui";
$dest[UF]      = "RS";
$dest[CEP]     = "88058000";
$dest[cPais]   = "1058";
$dest[xPais]   = "BRASIL";
$dest[fone]    = "989898898";

$nfe->setDest($dest);

/** // Informar apenas quando for diferente do endereço do remetente.
$retirada[CNPJ] = "";
$retirada[xLgr] = "";
$retirada[nro] = "";
$retirada[XCpl] = "";
$retirada[XBairro] = "";
$retirada[CMun] = "";
$retirada[XMun] = "";
$retirada[UF] = "";

$nfe->setRetirada($retirada);
**/

/** //Informar apenas quando for diferente do endereço do destinatário.
$entrega[CNPJ] = "";
$entrega[xLgr] = "";
$entrega[nro] = "";
$entrega[XCpl] = "";
$entrega[XBairro] = "";
$entrega[CMun] = "";
$entrega[XMun] = "";
$entrega[UF] = "";

$nfe->setEntrega($entrega);
**/




// array dos produtos pode usar um loop aqui e ir incrementando normalmente

//produtos
//Obs. A variavel $i tem que ser iniciado com ( 0 ) zero
for ($i = 0; $i < 1; $i++){
    $prod[$i][infAdProd] = "INFORMACOES ADICIONAIS DO PRODUTO";
    $prod[$i][CProd]     = "2470BCB90";
    $prod[$i][CEAN]      = "";
    $prod[$i][XProd]     = "DELFOS SND TINTO COM AMACIANTE SO TECIDO 1,80M";
    $prod[$i][NCM]       = "60063200";
    $prod[$i][EXTIPI]    = "";
    $prod[$i][CFOP]      = "5552";
    $prod[$i][UCom]      = "UND";
    $prod[$i][QCom]      = "1";
    $prod[$i][VUnCom]    = "19.000000000";
    $prod[$i][VProd]     = "19.00";
    $prod[$i][CEANTrib]  = "";
    $prod[$i][UTrib]     = "UND";
    $prod[$i][QTrib]     = "1.00";
    $prod[$i][VUnTrib]   = "19.000000000";
    $prod[$i][VFrete]    = "";
    $prod[$i][VSeg] 	 = "";
    $prod[$i][VDesc]     = "";
    $prod[$i][vOutro] 	 = "";
    $prod[$i][indTot] 	 = "1";
    $prod[$i][xPed] 	 = "";
    $prod[$i][nItemPed]  = "1";

    //icms
    $icms[$i][Orig]    = "0";
    $icms[$i][CST]     = "40";
	$icms[$i][ModBC]   = "0";
    $icms[$i][VBC] 	   = "0.00";
    $icms[$i][PICMS]   = "0.00";
    $icms[$i][VICMS]   = "0.00";

    //ipi
    $ipi[$i][ClEnq]    = "";
    $ipi[$i][CNPJProd] = "";
    $ipi[$i][CSelo]    = "";
    $ipi[$i][QSelo]    = "";
    $ipi[$i][CEnq] 	   = "999";
    $ipi[$i][CST]      = "52";

    //pis
    $pis[$i][CST]  = "01";
    $pis[$i][VBC]  = "0.00";
    $pis[$i][PPIS] = "0.00";
    $pis[$i][VPIS] = "0.00";

    //cofins
    $cofins[$i][CST]     = "01";
    $cofins[$i][VBC]     = "0.00";
    $cofins[$i][PCOFINS] = "0.00";
    $cofins[$i][VCOFINS] = "0.00";

    //cofins st
    $cofinsst[$i][VCOFINS]   = "";
    $cofinsst[$i][VBC]       = "";
    $cofinsst[$i][PCOFINS]   = "";
    $cofinsst[$i][QBCProd]   = "";
    $cofinsst[$i][VAliqProd] = "";

} // fim dos produtos


$nfe->setProd($prod);
$nfe->setIcms($icms);
$nfe->setIpi($ipi);
$nfe->setPis($pis);
$nfe->setCofins($cofins);
$nfe->setCofinsst($cofinsst);



//totais
$total[vBC]     = "0.00";
$total[vICMS]   = "0.00";
$total[vBCST]   = "0.00";
$total[vST]     = "0.00";
$total[vProd]   = "19.00";
$total[vFrete]  = "0.00";
$total[vSeg]    = "0.00";
$total[vDesc]   = "0.00";
$total[vII]     = "0.00";
$total[vIPI]    = "0.00";
$total[vPIS]    = "0.00";
$total[vCOFINS] = "0.00";
$total[vOutro]  = "0.00";
$total[vNF]     = "19.00";
//$total[VRetPIS] = "";

$nfe->setTotal($total);


// Transporte
$transp[ModFrete] = "1";
$transp[XNome]    = "RETIRA";
$transp[CNPJ]     = "";
//$transp[CPF]    = "";
$transp[IE]       = "";
$transp[XEnder]   = "";
$transp[UF]       = "";
$transp[XMun]     = "";
$transp[QVol]     = "3";
$transp[Esp]      = "VOLUMES";
$transp[Marca]    = "";
$transp[NVol]     = "";
$transp[PesoL]    = "46.480";
$transp[PesoB]    = "50.000";

$nfe->setTransp($transp);



// dados da fatura
$fatura[NFat]  = "0001";
$fatura[VOrig] = "883.12";
$fatura[VDesc] = "";
$fatura[VLiq]  = "883.12";

$nfe->setFatura($fatura);


// dados da duplicata(s)
for ($i = 0; $i < 1; $i++){
	$parcela[$i][NDup]  = "0001-1";
	$parcela[$i][DVenc] = "2010-12-20";
	$parcela[$i][VDup]  = "883.12";
}

$nfe->setParcela($parcela);


$infoAdd[InfAdFisco] = "EMITIDO NOS TERMOS DO ARTIGO 400-C DO DECRETO 48042/03 SAIDA COM SUSPENSAO DO IPI CONFORME ART 29 DA LEI 10.637";
$infoAdd[InfCpl] = "";

$nfe->setInfoAdd($infoAdd);



if ($nfe->validaTxt() != "OK"){

    //imprime o erro na tela
	$erro = $nfe->validaTxt();
    print $nfe->validaTxt();

}
else{

    //print $nfe->montaTXT();


    $arquivo = $nro_nfe. ".-nfe.txt";
    //Abrimos o arquivo que será gravado.
    $abrir = fopen($arquivo, "a");
    //Gravamos no arquivo
    $gravar = fwrite($abrir, $nfe->montaTXT());

    //Testa se foi gravado

    if(!$gravar){
    //echo" Arquivo TXT com sucesso em $arquivo!";
    //}else{
    echo"Arquivo nao gerado!";
     }


//CONVERTE TXT TO XML

$nome_xml_gerado = $nro_nfe.'-nfe.xml';

require_once('../nfephp/libs/ConvertNFePHP.class.php');
//instancia a classe
$nfe = new ConvertNFePHP();
if ( is_file($arquivo) ){
    $xml = $nfe->nfetxt2xml($arquivo);
    if ($xml != ''){
        echo '<PRE>';
        //echo htmlspecialchars($xml);
        //echo '</PRE>';
        if (!file_put_contents($nome_xml_gerado,$xml)){
            echo "ERRO na gravação";
        }
    }
}

echo "Arquivo xml gerado foi : " .$nome_xml_gerado;
echo "\n";






/**
 * Este é um exemplo do uso do método de assinatura digital do xml
 * da NFe
 * Esse método recebe a NFe como uma "string" com o conteúdo do xml e o
 * nome da "tag" xml que deverá ser assinada
 *
 */
//as classes desabilitam os erros e avisos por padrão então
//enquanto você está desenvolvendo reabilite os avisos de erros


require_once('../nfephp/libs/ToolsNFePHP.class.php');
error_reporting(E_ALL);
ini_set('display_errors', 'On');
//instancie a classe tools
$nfe = new ToolsNFePHP();
//escolha o xml a ser assinado
$arqxml = $nome_xml_gerado;
//determine o novo local para o esse xml depois de assinado
$novonome = $nro_nfe."-sign.xml";
//verifique se o xml existe
if ( is_file($arqxml) ) {
    //se o xml for achado então carregue o xml todo em uma string
    $nfefile = file_get_contents($arqxml);
    //tente assinar o xml na tag "infNFe", pois se trata de uma NFe
    if ( $signn = $nfe->signXML($nfefile, 'infNFe') ) {
        //se houve retorno do método então o xml foi assinado
        echo "NFe foi Assinada ..";
        echo "\n";
        //tente gravar esse xml assinado na nova localização
        if ( file_put_contents($novonome, $signn) ) {
            //a gravação foi um sucesso, apague o arquivo xml original
            unlink($arqxml);
            echo "SUCESSO !!! NFe assinada em $novonome. <br />";
        } else {
            echo "FALHA na gravação da NFe Assinada!! <br />";
        }
    } else {
        echo "FALHA NFe não assinada!!!! <br />";
        echo $nfe->errMsg;
    } //fim signXML
} //fim file existe
//Fim do exemplo



/**
 * Este é um exemplo de uso do método validXML que confere se o xml
 * assinado de uma NFe atende aos critérios do seu schema
 * Note que não devemos aplicar esse método as NFe que já tenham incorporados
 * seus proptocolos de aceitação da SEFAZ pois vai gerar erro.
 * E isso é lógico pois esse método deve ser usado antes de submeter a NFe ao SEFAZ
 * e portanto antes de receber o protocolo.
*/
//carregue a classe
require_once('../nfephp/libs/ToolsNFePHP.class.php');
header('Content-type: text/html; charset=UTF-8');
//instancie a classe
$nfe = new ToolsNFePHP;
echo 'Agora teste Validação<BR>';
//defina o arquivo da NFe assinado que deseja validar
$file = $novonome;
if (file_exists($file)){
    //se o arquivo existir, carregue o em uma string
    $docXml = file_get_contents($file);
    //coloque o path completo para o schema a ser usado
    $pathXSD = '../nfephp/schemes/PL_006j/nfe_v2.00.xsd';
    //$pathXSD = '../nfephp/schemes/Schemas/';

    //verifique a validade do xml em relação ao seu schema construtivo

    $aRet = $nfe->validXML($docXml, $pathXSD);

    if (!$aRet['status']) {
        echo str_replace("\n","<br>",$aRet['error']);
    }
}


///////////////////ENVIO PARA SEFAZ E TRATAMENTO DO RETORNO/////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

error_reporting(E_ALL);
ini_set("display_errors", 1);
define("bar",constant("DIRECTORY_SEPARATOR"));
require_once('../nfephp/libs/ToolsNFePHP.class.php');
//require_once('./b2sfuncaoaux.php');
print "Preparando configuração...  ";
print "<br>Enviando arquivo XML assinado aguarde...  ";
$modSOAP = '2';

$_NFe = new ToolsNFePHP('','1');
//echo "<br>";
if ( $_NFe->errStatus ) {
       print $_NFe->errMsg;
       die;
} else {
       echo 'Configuração adequada !';
}

print "<br>===================================================<br>";

print "<b>Enviando arquivo XML para sefaz.....  </b>";


//declaração das funcoes

    function send_nfe($aNFE,$file,$_NFe,$modSOAP){//,$xml){
    //obter o numero do ultimo lote enviado
    echo "<br> numero do lote ";
    $num = 1;//getNumLot($_NFe);
    //$num=2;
    //incrementa o numero
    //$num++;
    //enviar as notas    //array   numero do lote
    echo "numero do lote $num";
    //$aResp = $_NFe->sendLot($aNFE, $lote, $modSOAP)
    $ret = $_NFe->sendLot($aNFE,$num,$modSOAP);
    echo"<br> Resposta do envio sefaz, <b>";
    print_r($ret);
    echo"</b><br> fim da resposta de envio ";
    
    
    //echo $ret['nRec'];
    //echo $ret['nRec'];
    //echo $ret['nRec'];
    
    $_SESSION["numerodorecibo"] = $ret['nRec'];
    
    
    if($errMsg=trat_ret($ret,$num,$file,$_NFe)){

    //"O diretorio validadas foi enviado ao sefaz";
    $erro=$errMsg['erro'];
    $sfilename=$errMsg['num'];
    if($erro){
        //ocorreu algum erro

        echo "<br>===================================================<br>
        ERRO OCORRIDO EM <br>file name $sfilename STATUS=$erro";
        echo "<br> errMsg Motivo ". $errMsg['xMotivo'] ;
        //print_r($errMsg);
        echo "<br> errMsg do tools". $_NFe->errMsg ;
        }else{

            if($errMsg['cStat']==103){//retornou lote foi enviado
                echo "<br>===================================================<br>"."Lote com o arquivo".$sfilename.' <br><b>FOI ENVIADO</b><br> '.$_NFe->errMsg.'<br> ';

            }
     }

    //chamando o retorno do lote pelo numero do recibo caso seja rejeitado tem o motivo

    $aRet=retlot($num,$_NFe,$modSOAP,$ret);

    //echo "<br> imprime retorno";
    //print_r($aRet);
    //echo "<br> Fim do retorno";
    //pega o retorno com os xml ja analizados
    //retorne de um lote com uma nota mais de uma não foi pensado hehe
    //$aRet['cStat']  $aRet['xMotivo'] $aRet['chNFe']
    //$aRecb   =             $aRet['aRec']  ;//numero do lote de envio  mesmo que $num
    //$nProt   =             $aRet['nProt']   ;
    //$tpAmb   =             $aRet['tpAmb']   ;
    //$verAplic=             $aRet['verAplic'];
    //$chNFe   =             $aRet['chNFe']   ;
    //$dhRecbto=             $aRet['dhRecbto'];
    //$digVal  =             $aRet['digVal']  ;

    $cStat   =             $aRet['cStat']   ;
    $xMotivo =             utf8_decode($aRet['xMotivo']);
    //$sql='';

            if ( $cStat == 100) {
                        echo "<br>===================================================<br>"."Arquivo".$sfilename.' <br><b>APROVADO</b><br> '.$xMotivo.' <br> ';
                        }//endif
            if ( $cStat == 101) {
                        echo "<br>===================================================<br>"."Arquivo".$sfilename.' <br><b>CANCELADA</b><br> '.$xMotivo.' <br> ';
                        }//endif
            if ( $cStat == 102) {
                        echo "<br>===================================================<br>"."Arquivo".$sfilename.' <br><b>INUTILIZADA</b><br> '.$xMotivo.' <br> ';
                        }//endif
            if ( $cStat == 110) {
                        echo "<br>===================================================<br>"."Arquivo".$sfilename.' <br><b>DENEGADA</b><br> '.$xMotivo.' <br> ';
                        }//endif
            if (  $cStat > 200 ) {
                        //NFe reprovada
                        echo "<br>===================================================<br>"."Arquivo".$sfilename.' <br><b> NFe Reprovada <br><b> '.$xMotivo.'<br> ';

                    }//endif

                echo "<br> status de retorno numero $cStat <br>texto retornado $xMotivo";
                
                $_SESSION["cStat"]=$cStat;
                $_SESSION["xMotivo"]=$xMotivo;


                 echo "<br>$sfilename ENVIADO <br> ";


        }else{//movimento da nota deu erro
            echo "<br><b>ERRO NO MOVIMENTO ENTRE DIRETORIO DAS NOTAS";print_r($_NFe->errMsg);echo"<b>";
        }
    }









    function trat_ret($ret,$num,$sfile,$_NFe){
    echo "\n <br> tratando retorno num $num,<br> file $sfile \n";

        //echo $ret['cStat'];
        //echo $num;

        echo 'SERÁ O OARQUIVO XML VÁLIDO? \n';
        echo $sfile;
        
        //echo $_NFe;



        $errMsg['errMsg']='';
        if ($ret['cStat']==103){

        //protocolo foi gravado em disco
        //echo "<br>mover as notas do lote para o diretorio de enviadas";
        //para cada em $aNames[] mover para $this->envDir



            $errMsg['erro'] = false;
            $errMsg["errMsg"].="<br>Nota enviada";
            $errMsg['cStat']=$ret['cStat'];
            $errMsg['xMotivo']=$ret['xMotivo'];
            $errMsg['num']=$sfile;
            return $errMsg;
            //}//fim for rename

        }
        else{
        //erro no envio do lote lote não recebido
        $errMsg['erro'] = 10;
        $errMsg['errMsg'].='Lote num '.$num. 'erro no envio,retorno do sefaz'.$ret['xMotivo'];
        $errMsg['cStat']=$ret['cStat'];
        $errMsg['xMotivo']=$ret['xMotivo'];
        $errMsg['num']=$sfile;
        return $errMsg;
        }

    }

    function retlot($idLote,$_NFe,$modSOAP,$ret){

                // dá um tempo de 5 segundo pra pesquisar alguma situação
                sleep(15);
                //$recfile = $_NFe->$idLote.'-rec.xml';
                $recfile = $_NFe->temDir.$idLote.'-rec.xml';
                //echo "215 Busca recibo Numero $recfile";
                
                //$recfile = $_NFe->temDir.$idLote.'-rec.xml';
                echo "<br> 215 Busca recibo Numero $recfile";
                $xmlresp=file_get_contents($recfile);
                $doc = new DOMDocument(); //cria objeto DOM
                $doc->formatOutput = false;
                $doc->preserveWhiteSpace = false;
                $doc->loadXML($xmlresp,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);


                echo "vai imprimir o xmlresp";
                echo ($recfile);
                
                //echo "vai imprimrir o $doc";
                //echo($doc);


                // Busca o Numero do recibo
                $numerodorecibo = $doc->getElementsByTagName('nRec')->item(0)->nodeValue;
                echo "<br> numero do recibo é<b> $numerodorecibo</b><br> ";
                //$numerodorecibo_aux = $numerodorecibo;
                // Buscando o retorno do lote
                echo "busca 1<br> ";
                $tpAmb=$ret['tpAmb'];
                echo "tipo ambiente $tpAmb <br>";
                $chave='';//ou chave ou numero do recibo
                $protocolo =$_NFe->getProtocol($numerodorecibo, $chave, $tpAmb, $modSOAP);


                //$aResp = $_NFe->getProtocol($recibo, $chave, $tpAmb, $modSOAP))
                if(!$protocolo){//retorna falso caso tenha erro
                //gambiarra
                sleep(5);//espero 5 segundos
                echo "<br> busca 2 <br> ";
                $protocolo =$_NFe->getProtocol($numerodorecibo);
                }

                if($protocolo['cStat']==104){
                        $aRet['aRec']              = $idLote;//numero do lote
                        $aRet['cStat']             = $protocolo['aProt']['0']['cStat'];
                        $aRet['xMotivo']           = $protocolo['aProt']['0']['xMotivo'];
                        //$aRet['nProt']             = $protocolo['aProt']['0']['nProt'];
                        $aRet['tpAmb']             = $protocolo['aProt']['0']['tpAmb'];
                        $aRet['verAplic']          = $protocolo['aProt']['0']['verAplic'];
                        $aRet['chNFe']             = $protocolo['aProt']['0']['chNFe'];
                        $aRet['dhRecbto']          = $protocolo['aProt']['0']['dhRecbto'];
                        if($aRet['cStat']<200){
                        $aRet['digVal']            = $protocolo['aProt']['0']['digVal'];
                        }

//protocolo com retorno

                           $_NFe->errStatus = false;
                           $_NFe->errMsg = '';
                           $aRet['status'] = true;

                          echo "<br><b>GETPROTOCOL:NOTA=".$aRet['chNFe']."<br> CSTAT = ".$aRet['cStat']."<br> XMOTIVO:".$aRet['xMotivo']."</b>";
                          $_SESSION["chNFe"] = $aRet['chNFe'];
                }else

                {//protocolo sem retorno
                           $aRet['status'] = false;
                           $aRet['cStat'] = $protocolo['cStat'];
                           $aRet['xMotivo'] = $_NFe->errMsg ;
                           $aRet['chNFe'] = "RECIBO=".$numerodorecibo;
                          echo "<br><b>".$aRet['chNFe']." - ".$aRet['xMotivo']."</b>";

                }

    return $aRet;

    }






///////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////

/*

//ENVIAR A NF-E
$nfe = new ToolsNFePHP;
$modSOAP = '2'; //usando cURL

//use isso, este Ã© o modo manual voce tem mais controle sobre o que acontece
$filename = $file;
//obter um numero de lote
$lote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
// montar o array com a NFe
$aNFe = array(0=>file_get_contents($filename));
//enviar o lote
if ($aResp = $nfe->sendLot($aNFe, $lote, $modSOAP)){
    if ($aResp['bStat']){
        echo "Numero do Recibo : " . $aResp['nRec'] .", use este numero para obter o protocolo ou informaÃ§Ãµes de erro no xml com testaRecibo.php.";
    } else {
        echo "Houve erro !! $nfe->errMsg";
    }
} else {
    echo "houve erro !!  $nfe->errMsg";
}
echo '<BR><BR><h1>DEBUG DA COMUNICAÃ‡Ã•O SOAP</h1><BR><BR>';
echo '<PRE>';
echo htmlspecialchars($nfe->soapDebug);
echo '</PRE><BR>';

*/


        //limpa a matriz com as notas fiscais
        $_NFe->errMsg= null;//errMsg
        $_NFe->errStatus= null;
        $_NFe->errMsg['file']= null;
        $filename = $file; //file nfeID-nfe.xml
        $_NFefile = file_get_contents($filename);
        echo "<br> ENVIANDO $filename";
        $aNFE[] = $_NFefile;//coloco no array para satisfazer a funcao send_nfe
        //chama a função para enviar a nota fiscal e tratar o retorno
        send_nfe($aNFE,$file,$_NFe,$modSOAP);//,$xml);



        //ACRESCENTAR A CHAVE DE ACESSO AO XML
        
        
        
     //./$dirt/431000011915844-recprot.xml
     

     if($tpAmb==2)
     {
       $dirt = 'homologacao/temporarias';
     }
     else
     {
       $dirt = 'producao/temporarias';
     }


      echo $arqprot = $dirt.'/'.$_SESSION["numerodorecibo"].'-recprot.xml';



     echo $_SESSION["xMotivo"];
     echo "\n";
     echo $_SESSION["cStat"];

     //if($_SESSION["cStat"]=='100')  //se tudo ok adicionar o protocolo na nfe através da funcao addprot
     //{
     //}
                         if($_SESSION["cStat"]==100 or $_SESSION["cStat"]==110 or $_SESSION["cStat"]==101)
                         {
                         //montar a NFe com o protocolo
                        $protFile = $arqprot;//$_NFe->temDir.$chave.'-prot.xml';//caminho para o arquivo temporario numero-prot.xml
                        $nfeFile = $novonome;//$_NFe->envDir.$file;                 //caminho para o arquivo enviado
                        if ( is_file($protFile) && is_file($nfeFile) ) {
                            $procnfe = $_NFe->addProt($nfeFile,$protFile);
                            if(empty($procnfe)){//; //acrescenta o protocolo ao arquivo xml enviado utilizando o protocolo que esta em temporario numero-prot.xml

                            echo "<br>$procnfe<b>ERRO PROTOCOLANDO</b>";

                            }else{
                            
                            $chave = $_SESSION['chNFe'];
                            //salvar a NFe com o protocolo na pasta destino
                            //echo "<br> conteudo da nfe protolada $procnfe <br> fim do protocolo";
                            if ( file_put_contents($chave."-nfe.xml", $procnfe) ) {  //$dotxml='-nfe.xml';


                                echo "<br>Novo arquivo $chave.'-nfe.xml' <br>//remover o arquivo antigo sem o protocolo $nfeFile";

                                unlink($nfeFile);
                            echo "<br>PROTOCOLO RECUPERADO E ATUALIZADO";
                            
                            
                            $aux =  $_SESSION['chNFe'];
                            echo "<a href='printDANFE.php?xml=$aux-nfe.xml'>Imprimir DANFE</a><br><br>";
                            echo "<a href='Enviaemail.php?xml=$aux-nfe.xml'>Enviar e-mail da DANFE</a>";

                            
                            
                            //echo "<br>$comentario";

                            }else{
                                echo "<br>ERRO NOTA $nfeFile <br> não foi juntada com protocolo em <br> <b>VERIFICAR</b>";
                                }
                            }
                        }else{
                        echo "<br>ERRO arquivo $nfeFile <br> ou protocolo $protFile não encontrados ";
                        } //endif

                    }
                    else
                    {
                     echo "Nota não aprovada, não foi protocolada.";
                    }
     
     
     





}
		
		
?>
