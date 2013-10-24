<?php  
	/**
	* Classe que monta e organiza o processo de emissão de nota fiscal eletronica 
	* de produtos e suas respectivas funcionaldiades (Emissão, cancelamento, geração 
	* de DANFE, initilização de numeros e envio de carta de correção).
	* 
	* Desenvolvido por MAXSAM TECNOLOGIA - Joyvis Santana - 17/10/2013
	*/

	include_once('config.php');
	include_once('../nfephp/libs/ToolsNFePHP.class.php');

	class NFe{
		private $tpAmb = '2';
		private $modSOAP = '2';
		private $pathXml = 'xml';
		private $numeroNota;
		private $chave;
		private $notaId;
		private $lote;
		private $recibo;
		
		function __construct(){
			$conn = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
			mysql_select_db(DATABASE_NAME, $conn);
		}

		public function __set($name, $value) {
	        $this->$name = $value;
	    }
	 
	    public function __get($name) {
	        return $this->$name;
	    }

		public function gerarXml(){
			$cUF = '35';//Código da UF [02] 
			$aamm = date("ym");//AAMM da emissão [4] 
			$cnpj = '12344567000164';//CNPJ do Emitente [14] 
			$mod='55';//Modelo [02] 
			$serie='001';//Série [03] 
			
			if($this->validaNumeroNF($this->numeroNota)){
				$NumeroDaNf = $this->numeroNota;//Número da NF-e [<=09] 
				
				if(!mysql_query("INSERT INTO notas_fiscais(numero) VALUES('{$NumeroDaNf}')")){
					return array('success' => false);
				}else{
					$this->notaId = $ultimoIdNota = mysql_insert_id();
				}
			}else{
				return array('success' => false, 'msg' => 'Esse numero já foi utilizado em outra nota!');
			}

			$tpEmis='1';//forma de emissão da NF-e [01] 1 – Normal – emissão 
			// normal; 2 – Contingência FS; 3 – Contingência SCAN; 
			$DataEmi = date("Y-m-d"); 

			//variaveis que monta a chave 
			$cn=''; 
			$dv=''; 
			$num = str_pad($NumeroDaNf, 9, '0',STR_PAD_LEFT); 
			$cn = $this->geraCN(8); 
			$chave = "$cUF$aamm$cnpj$mod$serie$num$tpEmis$cn"; 
			$dv = $this->calculaDV($chave); 
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
					$xml .= '<qTrib>1</qTrib>';
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
		   	// $NumeroDaNf
			if (!file_put_contents("$this->pathXml/".$chave.'-nfe.xml',$xml)){
		        $this->logNotaFiscal($ultimoIdNota, "Gerar XML", "Erro ao gerar o XML!");
		        return array('success' => false);
		    }else{
		    	if(mysql_query("UPDATE notas_fiscais SET chave = '{$chave}' WHERE id = {$ultimoIdNota}")){
		    		$this->logNotaFiscal($ultimoIdNota, "Gerar XML", "XML gerado com sucesso!");
		    		$this->chave = $chave;
		    		$this->notaId = $ultimoIdNota;
		    		return array('success' => true);	
		    	}else{		    	
		    		return array('success' => false);
		    	}
		    }
		}

		#Função que valida o XML antes do envio 
		public function assinaXml(){
			
			$nfe = new ToolsNFePHP;
			$arq = $this->getConteudoXml($this->chave);

			if ($xml = $nfe->signXML($arq, 'infNFe')){
				
			    if(file_put_contents("{$this->pathXml}/".$this->chave.'-nfe.xml', $xml)){
			    	$this->logNotaFiscal($this->notaId, "Assinar XML", "XML assinado com sucesso!");
			    	return true;
			    }else{
			    	$this->logNotaFiscal($this->notaId, "Assinar XML", "erro ao salvar o XML assinado!");
			    	return false;
			    }
			   
			} else {
				$this->logNotaFiscal($notaId, "Assinar XML", utf8_decode($nfe->errMsg));
			    return false;
			}
			unset($nfe);
		}

		#Função que valida o XML antes de enviar 
		public function validaXml(){
			$nfe = new ToolsNFePHP;
			header('Content-type: text/html; charset=UTF-8');
			$arq = $this->getConteudoXml($this->chave);
			$xsdFile = '../nfephp/schemes/PL_006s/nfe_v2.00.xsd';

			$aErro = array();
			$c = $nfe->validXML($arq,$xsdFile,$aErro);
			if (!$c){
				 $this->logNotaFiscal($this->notaId, "Validar XML", "O xml contém erros, tente refazer a operação!");
			    return false;
		    }else{
		    	$this->logNotaFiscal($this->notaId, "Validar XML", "XML validado com sucesso!");
			    return true;
		    }

			unset($nfe);
		}

		#Função que envia o XML
		public function enviaXml(){
			$nfe = new ToolsNFePHP;
			$this->lote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
			$aNFe = array(0 => $this->getConteudoXml($this->chave));
			// print_r($NFe);
			$aResp = array();
			if ($aResp = $nfe->sendLot($aNFe, $this->lote, $this->modSOAP)){
			    if ($aResp['bStat']){
			    	if(mysql_query("UPDATE notas_fiscais SET recibo = '{$aResp['nRec']}' WHERE id = {$this->notaId}")){
			    		$this->recibo = $aResp['nRec'];
			    		$this->alteraStatusNota($this->notaId, 2);
			         	$this->logNotaFiscal($this->notaId, "Enviar XML", "XML enviado com sucesso!");
			         	return true;
		         	}else{
		         		$this->logNotaFiscal($this->notaId, "Enviar XML", "XML enviado, erro ao salvar numero recibo!");
		         		return false;
		         	}	

			    } else {
			        $this->logNotaFiscal($this->notaId, "Enviar XML", utf8_decode($nfe->errMsg));
			        return false;
			    }
			} else {
			    $this->logNotaFiscal($this->notaId, "Enviar XML", utf8_decode($nfe->errMsg));
			    return false;

		    	print_r($nfe->errMsg);
			}

		}

		#Função que consulta o recibo da NF
		public function consultaRecibo(){
			$nfe = new ToolsNFePHP;
			$aResp = array();
			$ch = '';
			$this->recibo = '351001911056627';
			$this->chave = '35131012344567000164550050000000101593027320';
			$this->notaId = 10;
			if ($aResp = $nfe->getProtocol($this->recibo, $ch, $this->tpAmb, $this->modSOAP)){
				// print_r($aResp);
				if($aResp['aProt']['0']['cStat'] == '100'){

					if($this->alteraStatusNota($this->notaId, 2)){						
						$protFile = $nfe->temDir . $this->recibo . '-prot.xml'; 
						$nfefile = $this->getConteudoXml($this->chave);
						$procnfe = $nfe->addProt($nfefile, $protFile); 
						if (file_put_contents($pathXml.$this->chave.'-nfe.xml', $procnfe)) { 
							mysql_query("UPDATE notas_fiscais SET protocolo = '{$aResp['aProt']['0']['nProt']}', recebimento = now() WHERE id = {$this->notaId}");
							$this->logNotaFiscal($this->notaId, "Retornar XML", "XML protocolado com sucesso!");
							return true;
						}else{
							$this->logNotaFiscal($this->notaId, "Retornar XML", "Erro ao adicionar protocolo ao XML!");
							return false;
						}
					}else{
						$this->logNotaFiscal($this->notaId, "Retornar XML", "Falha ao alterar o status da nota!");
						return false;
					}
		         	
				}else{
					$this->alteraStatusNota($this->notaId, 3);
					$this->logNotaFiscal($this->notaId, "Retornar XML", utf8_decode($aResp['aProt']['0']['xMotivo']));
					return false;
				}
			}else{
				$this->logNotaFiscal($this->notaId, "Retornar XML", "Falha ao se conectar ao SEFAZ!");
				return false;
			}
		}

		#Função para gerar o DANFE
		public function geraDanfe(){
			include_once('../nfephp/libs/DanfeNFePHP.class.php');
			if ( is_file($this->pathXml."/".$this->chave."-nfe.xml")){
			    $docxml = $this->getConteudoXml($this->chave);
			    $danfe = new DanfeNFePHP($docxml, 'P', 'A4','../nfephp/images/logo.jpg','I','');
			    $id = $danfe->montaDANFE();
			    $teste = $danfe->printDANFE($id.'.pdf','I');
			}else{
				echo "XML não encontrado!";
			}
		}

		#Função para cancelar a NF
		public function cancelaNota(){
			$nfe = new ToolsNFePHP;
			$this->justificativa = "OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO";
			$this->alteraStatusNota($this->notaId, 4);
			if ($resp = $nfe->cancelEvent($this->chave,$this->protocolo,$this->justificativa,$this->tpAmb,$this->modSOAP)){
				return true;
			} else {
			    return false;
			}   
		}


		#Função para emissão de carta de correção 
		public function cartaCorrecao(){
			$nfe = new ToolsNFePHP;
			include_once('../nfephp/libs/DacceNFePHP.class.php');
			$chNFe= $this->chave;
			$xCorrecao='O numero correto para o endereco RUA LUZITANA 290 a acrescido da indicacao fundos. Telefone de contato 1234567890';
			$nSeqEvento='1';
			$tpAmb='2';
			$modSOAP='2';
			$xml = $nfe->envCCe($chNFe, $xCorrecao, $nSeqEvento, $tpAmb, $modSOAP, $resp);
			$arquivo = "/var/www/api_mubi/NFe/nfe/homologacao/cartacorrecao/{$this->chave}-{$nSeqEvento}-envCCe.xml";

			$aEnd = array('razao'=>"mubi",
            'logradouro' => "RUa teste",
            'numero' => "41",
            'complemento' => "fundos",
            'bairro' => "Bitaru",
            'CEP' => "11330220",
            'municipio' => "São Vicente",
            'UF'=>"SP",
            'telefone'=>"3371-1259",
            'email'=>"maxsam@maxsam.com.br");



            $cce = new DacceNFePHP($arquivo, 'P', 'A4','../nfephp/images/logo.jpg','I',$aEnd,'','Times',1);
            $teste = $cce->printCCe('CCe.pdf','I');
			// } else {
			// 	echo 'sadsadsa';
			//     echo $nfe->errMsg;
			// }

			

		}

		#Função que pega o conteudo do XML
		public function getConteudoXml($chave){
			$file = "$this->pathXml/".$chave.'-nfe.xml';
			return file_get_contents($file);
		}

		#Função para gerar o código de acesso na NF
		public function geraCN($length=8){ 
		    $numero = ''; 
		    for ($x=0;$x<$length;$x++){ 
		        $numero .= rand(0,9); 
		    } 
		    return $numero; 
		} 

		#Função para gerar o digito verificador da nota
		public function calculaDV($chave43) { 
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

		#Função para verificar se o numero da nota foi usado.
		public function validaNumeroNF($numeroNF = null){
			$buscaNf = mysql_query("SELECT count(*) total FROM notas_fiscais WHERE numero = '{$numeroNF}'") or die(mysql_error());
			$bNf = mysql_fetch_array($buscaNf);

			if($bNf['total'] != 0){
				return false;
			}else{
				return true;
			}
		}

		#Função que salva o log da tabela
		public function logNotaFiscal($idNota, $acao, $mensagem){
			return $salvaLog = mysql_query("INSERT INTO notas_fiscais_log(nota_fiscal_id, acao, mensagem) VALUES({$idNota}, '{$acao}', '{$mensagem}')") or die(mysql_error());
		}

		#Função para alterar o status da nota
		public function alteraStatusNota($notaId, $status){
			if(mysql_query("UPDATE notas_fiscais SET status = {$status} WHERE id = {$notaId}")){
				return true;
			}else{
				return false;
			}
		}
	}
?>