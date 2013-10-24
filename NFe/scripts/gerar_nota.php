<?php  
	function geraCN($length=8){ 
	    $numero = ''; 
	    for ($x=0;$x<$length;$x++){ 
	        $numero .= rand(0,9); 
	    } 
	    return $numero; 
	} 


	function calculaDV($chave43) { 
	    $multiplicadores = array(2,3,4,5,6,7,8,9); 
	    $i = 42; 
	    $soma_ponderada = 0;
	    while ($i >= 0) { 
	        for ($m=0; $m<count($multiplicadores) && $i>=0; $m++) { 
	            $soma_ponderada+= $chave43[$i] * $multiplicadores[$m]; 
	            $i--; 
	        } 
	    } 
	    $resto = $soma_ponderada % 11; 
	    if ($resto == '0' || $resto == '1') { 
	        return 0; 
	    } else { 
	        return (11 - $resto); 
	   } 
	} 

	//dados do cliente 
	$cUF = '35';//Código da UF [02] 
	$aamm = date("ym");//AAMM da emissão [4] 
	$cnpj = '12344567000164';//CNPJ do Emitente [14] 
	$mod='55';//Modelo [02] 
	$serie='001';//Série [03] 
	$NumeroDaNf= rand(555555,66666666);//Número da NF-e [<=09] 
	$tpEmis='1';//forma de emissão da NF-e [01] 1 – Normal – emissão 
	// normal; 2 – Contingência FS; 3 – Contingência SCAN; 
	$DataEmi = date("Y-m-d"); 

	//variaveis que monta a chave 
	$cn=''; 
	$dv=''; 
	$num = str_pad($NumeroDaNf, 9, '0',STR_PAD_LEFT); 
	$cn = geraCN(8); 
	$chave = "$cUF$aamm$cnpj$mod$serie$num$tpEmis$cn"; 
	$dv = calculaDV($chave); 
	$chave .= $dv; 



	// ai no xml fica 

	$xml = '<?xml version="1.0" encoding="UTF-8"?>'; 
	$xml .= '<NFe xmlns="http://www.portalfiscal.inf.br/nfe">'; 
	$xml .= '<infNFe Id="NFe'.$chave.'" versao="2.00">';//iden da nfe, 
	// chave de acesso 

	$xml .= '<ide>';//inicio da identificacao da nfe 
	$xml .= '<cUF>'.$cUF.'</cUF>';//estado do contribuinte da nfe 
	$xml .= '<cNF>'.$cn.'</cNF>';//codigo de acesso gerador pelo emisso 
	$xml .= '<natOp>VENDA DE MERCADORIA</natOp>';//natureza da nota 
	$xml .= '<indPag>0</indPag>';//0 - pagamento a vista / 1 - a prazo / 2 
	// - outros 
	$xml .= '<mod>55</mod>';//fixo 
	$xml .= '<serie>1</serie>';//serie da nota 
	$xml .= '<nNF>'.$NumeroDaNf. '</nNF>';//numero do documento fiscal 
	$xml .= '<dEmi>'.$DataEmi. '</dEmi>';//data da emissao 
	$xml .= '<dSaiEnt>'.$DataEmi.'</dSaiEnt>';
	$xml .= '<hSaiEnt>'.date('H:i:s').'</hSaiEnt>';
	
	$xml .= '<tpNF>1</tpNF>';//0 entrada / 1 saida 
	$xml .= '<cMunFG>3505708</cMunFG>';//cod do municipio do contribuinte 
	// da nfe 
	$xml .= '<tpImp>1</tpImp>';// 1 - retrato 
	$xml .= '<tpEmis>'.$tpEmis.'</tpEmis>';//1 emissao normal 
	$xml .= '<cDV>'.$dv.'</cDV>';//digito verificador da chave de acesso 
	$xml .= '<tpAmb>2</tpAmb>';//1 producao / 2 homologacao 
	$xml .= '<finNFe>1</finNFe>';//1 nfe normal 
	$xml .= '<procEmi>0</procEmi>';//0 emissao nfe pelo aplicativo do 
	// contribuinte 
	$xml .= '<verProc>2.0.3</verProc>';//versao do processo 
	$xml .= '</ide>';//fim da identificacao da nfe 
	$xml .= '<emit>';
		$xml .= '<CNPJ>12344567000164</CNPJ>';
		$xml .= '<xNome>MINHA EMPRESA TESTE LTDA.</xNome>';
		$xml .= '<xFant>MINHA EMPRESA FANTASIA TESTE</xFant>';
		$xml .= '<enderEmit>';
			$xml .= '<xLgr>RUA TESTE</xLgr>';
			$xml .= '<nro>123</nro>';
			$xml .= '<xBairro>CENTRO</xBairro>';
			$xml .= '<cMun>3505708</cMun>';
			$xml .= '<xMun>Barueri</xMun>';
			$xml .= '<UF>SP</UF>';
			$xml .= '<CEP>93000000</CEP>';
			$xml .= '<cPais>1058</cPais>';
			$xml .= '<xPais>BRASIL</xPais>';
			$xml .= '<fone>5111223344</fone>';
		$xml .= '</enderEmit>';
		$xml .= '<IE>206005920115</IE>';
		$xml .= '<CRT>1</CRT>';
	$xml .= '</emit>';
	$xml .= '<dest>';
		$xml .= '<CNPJ>99999999000191</CNPJ>';
		$xml .= '<xNome>NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL</xNome>';
		$xml .= '<enderDest>';
			$xml .= '<xLgr>AV. DOS TESTES</xLgr>';
			$xml .= '<nro>202</nro>';
			$xml .= '<xBairro>CENTRO</xBairro>';
			$xml .= '<cMun>3505708</cMun>';
			$xml .= '<xMun>Barueri</xMun>';
			$xml .= '<UF>SP</UF>';
			$xml .= '<CEP>93000000</CEP>';
			$xml .= '<cPais>1058</cPais>';
			$xml .= '<xPais>BRASIL</xPais>';
		$xml .= '</enderDest>';
		$xml .= '<IE>ISENTO</IE>';
	$xml .= '</dest>';
	$xml .= '<det nItem="1">';
		$xml .= '<prod>';
			$xml .= '<cProd>15036</cProd>';
			$xml .= '<cEAN/>';
			$xml .= '<xProd>PRODUTO TESTE REF 123123</xProd>';
			$xml .= '<NCM>84123123</NCM>';
			$xml .= '<CFOP>5101</CFOP>';
			$xml .= '<uCom>UN</uCom>';
			$xml .= '<qCom>1.0000</qCom>';
			$xml .= '<vUnCom>590.0000</vUnCom>';
			$xml .= '<vProd>590.00</vProd>';
			$xml .= '<cEANTrib/>';
			$xml .= '<uTrib>UN</uTrib>';
			$xml .= '<qTrib>1.0000</qTrib>';
			$xml .= '<vUnTrib>590.0000</vUnTrib>';
			$xml .= '<indTot>1</indTot>';
		$xml .= '</prod>';
		$xml .= '<imposto>';
			$xml .= '<ICMS>';
				$xml .= '<ICMSSN101>';
					$xml .= '<orig>0</orig>';
					$xml .= '<CSOSN>101</CSOSN>';
					$xml .= '<pCredSN>2.82</pCredSN>';
					$xml .= '<vCredICMSSN>16.64</vCredICMSSN>';
				$xml .= '</ICMSSN101>';
			$xml .= '</ICMS>';
			$xml .= '<PIS>';
				$xml .= '<PISOutr>';
					$xml .= '<CST>99</CST>';
					$xml .= '<vBC>0.00</vBC>';
					$xml .= '<pPIS>0.00</pPIS>';
					$xml .= '<vPIS>0.00</vPIS>';
				$xml .= '</PISOutr>';
			$xml .= '</PIS>';
			$xml .= '<COFINS>';
				$xml .= '<COFINSOutr>';
					$xml .= '<CST>99</CST>';
					$xml .= '<vBC>0.00</vBC>';
					$xml .= '<pCOFINS>0.00</pCOFINS>';
					$xml .= '<vCOFINS>0.00</vCOFINS>';
				$xml .= '</COFINSOutr>';
			$xml .= '</COFINS>';
		$xml .= '</imposto>';
	$xml .= '</det>';
	$xml .= '<total>';
		$xml .= '<ICMSTot>';
			$xml .= '<vBC>0.00</vBC>';
			$xml .= '<vICMS>0.00</vICMS>';
			$xml .= '<vBCST>0.00</vBCST>';
			$xml .= '<vST>0.00</vST>';
			$xml .= '<vProd>590.00</vProd>';
			$xml .= '<vFrete>0.00</vFrete>';
			$xml .= '<vSeg>0.00</vSeg>';
			$xml .= '<vDesc>0.00</vDesc>';
			$xml .= '<vII>0.00</vII>';
			$xml .= '<vIPI>0.00</vIPI>';
			$xml .= '<vPIS>0.00</vPIS>';
			$xml .= '<vCOFINS>0.00</vCOFINS>';
			$xml .= '<vOutro>0.00</vOutro>';
			$xml .= '<vNF>590.00</vNF>';
		$xml .= '</ICMSTot>';
	$xml .= '</total>';
	$xml .= '<transp>';
		$xml .= '<modFrete>1</modFrete>';
	$xml .= '</transp>';
	$xml .= '<cobr>';
		$xml .= '<dup>';
			$xml .= '<nDup>624</nDup>';
			$xml .= '<dVenc>2013-02-08</dVenc>';
			$xml .= '<vDup>590.00</vDup>';
		$xml .= '</dup>';
	$xml .= '</cobr>';
	$xml .= '<infAdic>';
		$xml .= '<infCpl>TESTE DE INFORMACOES ADICIONAIS</infCpl>';
	$xml .= '</infAdic>';
	$xml .=  '</infNFe>';
   	$xml .=  '</NFe>';
	// echo $xml;

   	#gera XML
	if (!file_put_contents('xml/'.$chave.'-nfe.xml',$xml)){
        echo "ERRO na gravacao!!";
        exit;
    }
    #assina XML
    require_once('../nfephp/libs/ToolsNFePHP.class.php');
	$nfe = new ToolsNFePHP;

	$file = 'xml/'.$chave.'-nfe.xml';
	$arq = file_get_contents($file);

	if ($xml = $nfe->signXML($arq, 'infNFe')){
	    file_put_contents($file, $xml);
	} else {
	    echo $nfe->errMsg;
	    exit;
	}

	#valida XML
	$docxml = file_get_contents($file);
	$xsdFile = '../nfephp/schemes/PL_006s/nfe_v2.00.xsd';
	$aErro = '';
	$c = $nfe->validXML($docxml,$xsdFile,$aErro);
	if (!$c){
	    echo 'Houve erro --- <br>';
	    foreach ($aErro as $er){
	        echo utf8_decode($er) .'<br>';
	    }
	} else {
	    // echo 'VALIDADA!';
	}

	#envia XML
	$modSOAP = '2'; //usando cURL

	//use isso, este é o modo manual voce tem mais controle sobre o que acontece  
	$filename = $file;
	//obter um numero de lote
	$lote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
	// montar o array com a NFe
	$aNFe = array(0=>file_get_contents($filename));
	//enviar o lote
	if ($aResp = $nfe->sendLot($aNFe, $lote, $modSOAP)){
	    if ($aResp['bStat']){
	        // echo "Numero do Recibo : " . $aResp['nRec'] .", use este numero para obter o protocolo ou informações de erro no xml com testaRecibo.php.";  
	    } else {
	        echo "Houve erro !! $nfe->errMsg";
	    }
	} else {
	    echo "houve erro !!  $nfe->errMsg";
	}
	// echo '<BR><BR><h1>DEBUG DA COMUNICAÇÕO SOAP</h1><BR><BR>';
	// echo '<PRE>';
	// echo htmlspecialchars($nfe->soapDebug);
	// echo '</PRE><BR>';

	#Consultar pelo Recibo
	echo $recibo = $aResp['nRec']; //este é o numero do seu recibo mude antes de executar este script
	$chave = '';
	$tpAmb = '2'; //homologação

	header('Content-type: text/html; charset=UTF-8');
	if ($aResp = $nfe->getProtocol($recibo, $chave, $tpAmb, $modSOAP)){
	    //houve retorno mostrar dados
	    if($aResp['aProt']['0']['cStat'] == '100'){
	    	// print_r($aResp);
	    	echo '<br>';
		    echo $aResp['aProt']['0']['chNFe'];
		    echo '<br>';
		    echo $aResp['aProt']['0']['dhRecbto'];
		    echo '<br>';
		    echo $aResp['aProt']['0']['nProt'];
		    echo '<br>';

		    #Gera Danfe
		 //    require_once('../nfephp/libs/DanfeNFePHP.class.php');

			// $arq = $file;

			// if ( is_file($arq) ){
			//     $docxml = file_get_contents($arq);
			//     $danfe = new DanfeNFePHP($docxml, 'P', 'A4','../nfephp/images/logo.jpg','I','');
			//     $id = $danfe->montaDANFE();
			//     $teste = $danfe->printDANFE($id.'.pdf','I');
			// }

			#cancela nota
			$chNFe = $aResp['aProt']['0']['chNFe'];
			$nProt = $aResp['aProt']['0']['nProt'];
			$xJust = "Testando cancelamento de nota";
			$tpAmb = '2';
			$modSOAP = '2';

			if ($resp = $nfe->cancelEvent($chNFe,$nProt,$xJust,$tpAmb,$modSOAP)){
			    // header('Content-type: text/xml; charset=UTF-8');
			    echo $resp;
			} else {
			    header('Content-type: text/html; charset=UTF-8');
			    echo '<BR>';
			    echo $nfe->errMsg.'<BR>';
			    echo '<PRE>';
			    echo htmlspecialchars($nfe->soapDebug);
			    echo '</PRE><BR>';
			}   
		}else{
			echo $aResp['aProt']['0']['xMotivo'];
		}
	} else {
	    //não houve retorno mostrar erro de comunicação
	    echo "Houve erro !! $nfe->errMsg";
	}


	#gera DANFE 

?>