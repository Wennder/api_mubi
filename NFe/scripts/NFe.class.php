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
		private $tpAmb = '1';
		private $modSOAP = '2';
		private $serie= '5';
		private $numeroNota;
		private $chave;
		private $notaId;
		private $lote;
		private $recibo;
		private $protocolo;
		private $config;
		private $nfe;
		private $status;
		private $clienteId;
		private $osIds;
		private $osData = array();

		function __construct(){
			$conn = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
			mysql_select_db(DATABASE_NAME, $conn);
			$this->setConfig();
		}

		public function __set($name, $value) {
	        $this->$name = $value;
	    }
	 
	    public function __get($name) {
	        return $this->$name;
	    }

	    public function setConfig(){
	    	$query = mysql_query("SELECT * FROM clientemubi WHERE id = 1") or die(mysql_error());
			$emitente = mysql_fetch_array($query);

	    	$this->config = array(
	    		'ambiente' => $emitente['ambiente'] = 2,
	    		'empresa' => $emitente['xnome'],
	    		'nome_fantasia' => $emitente['xfant'],
	    		'UF' => $emitente['uf'],
	    		'cnpj' => $emitente['cnpj'],
	    		'certName' => $emitente['certificado'],
	    		'keyPass' => $emitente['senhacertificado'],
	    		'arquivosDir' => $emitente['path_xml'],
	    		'danfeLogo' => $emitente['logotipo']
	    	);

	    	#Pegar restante dos dados do emitente
			foreach ($emitente as $key => $value) {
				$this->config[$key] = $value;
			}
			// print_r($this->config);
	    	$this->tpAmb = $this->config['ambiente'];
	    }

		public function gerarXml(){
			$nfe = new ToolsNFePHP($this->config);
			$this->getOs();
			$cliente = $this->getCliente($this->osData['idcliente']);
			// print_r($cliente);
			// exit;

			$cUF = $nfe->cUF;//Código da UF [02] 
			$aamm = date("ym");//AAMM da emissão [4] 
			$cnpj = $this->removePontoCnpj($this->config['cnpj']);//CNPJ do Emitente [14] 
			$mod='55';//Modelo [02] 
			$serie = $this->serie;
			$seriePad = str_pad($serie, 3, '0',STR_PAD_LEFT); 
			//Série [03] 
			
			if($this->validaNumeroNF($this->numeroNota)){
				$NumeroDaNf = $this->numeroNota;//Número da NF-e [<=09] 
				if(!mysql_query("INSERT INTO notas_fiscais(numero) VALUES('{$NumeroDaNf}')")){
					return array('success' => false);
				}else{
					$this->notaId = $ultimoIdNota = mysql_insert_id();
				}
			}else{
				return array('success' => false, 'msg' => 'Esse numero já foi utilizado em notas anteriores.');
			}

			$tpEmis='1';//forma de emissão da NF-e [01] 1 – Normal – emissão 
			// normal; 2 – Contingência FS; 3 – Contingência SCAN; 
			$DataEmi = date("Y-m-d"); 

			//variaveis que monta a chave 
			$cn=''; 
			$dv=''; 
			$num = str_pad($NumeroDaNf, 9, '0',STR_PAD_LEFT); 
			
			$cn = $this->geraCN(8); 
			
			$chave = "$cUF$aamm$cnpj$mod$seriePad$num$tpEmis$cn"; 
			
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
				$xml .= '<serie>'.$serie.'</serie>';//serie da nota 
				$xml .= '<nNF>'.$NumeroDaNf. '</nNF>';//numero do documento fiscal 
				$xml .= '<dEmi>'.$DataEmi. '</dEmi>';//data da emissao 
				$xml .= '<dSaiEnt>'.$DataEmi.'</dSaiEnt>';
				$xml .= '<hSaiEnt>'.date('H:i:s').'</hSaiEnt>';
				
				$xml .= '<tpNF>1</tpNF>';//0 entrada / 1 saida 
				$xml .= '<cMunFG>'.$this->config['cmun'].'</cMunFG>';//cod do municipio do contribuinte 
				// da nfe 
				$xml .= '<tpImp>1</tpImp>';// 1 - retrato 
				$xml .= '<tpEmis>'.$tpEmis.'</tpEmis>';//1 emissao normal 
				$xml .= '<cDV>'.$dv.'</cDV>';//digito verificador da chave de acesso 
				$xml .= '<tpAmb>'.$this->tpAmb.'</tpAmb>';//1 producao / 2 homologacao 
				$xml .= '<finNFe>1</finNFe>';//1 nfe normal 
				$xml .= '<procEmi>0</procEmi>';//0 emissao nfe pelo aplicativo do 
				// contribuinte 
				$xml .= '<verProc>Maxsam NF-e V1</verProc>';//versao do processo 
			$xml .= '</ide>';//fim da identificacao da nfe 
			$xml .= '<emit>';
				$xml .= '<CNPJ>'.$cnpj.'</CNPJ>';
				$xml .= '<xNome>'.$this->config['empresa'].'</xNome>';
				$xml .= '<xFant>'.$this->config['xfant'].'</xFant>';
				$xml .= '<enderEmit>';
					$xml .= '<xLgr>'.$this->config['xlgr'].'</xLgr>';
					$xml .= '<nro>'.$this->config['nro'].'</nro>';
					$xml .= '<xCpl>'.$this->config['xcpl'].'</xCpl>';
					$xml .= '<xBairro>'.$this->config['xbairro'].'</xBairro>';
					$xml .= '<cMun>'.$this->config['cmun'].'</cMun>';
					$xml .= '<xMun>'.$this->config['xmun'].'</xMun>';
					$xml .= '<UF>'.$this->config['uf'].'</UF>';
					$xml .= '<CEP>'.$this->config['cep'].'</CEP>';
					$xml .= '<cPais>'.$this->config['cpais'].'</cPais>';
					$xml .= '<xPais>'.$this->config['xpais'].'</xPais>';
					$xml .= '<fone>'.$this->config['fone'].'</fone>';
				$xml .= '</enderEmit>';
				$xml .= '<IE>'.$this->config['ie'].'</IE>';
				$xml .= '<CRT>'.$this->config['crt'].'</CRT>';
			$xml .= '</emit>';
			$xml .= '<dest>';
				$xml .= '<CNPJ>'.$this->removePontoCnpj($cliente['cnpjcliente']).'</CNPJ>';
				$xml .= '<xNome>'.$cliente['nomecliente'].'</xNome>';
				$xml .= '<enderDest>';
					$xml .= '<xLgr>'.$cliente['endereco'].'</xLgr>';
					$xml .= '<nro>'.$cliente['numero'].'</nro>';
					$xml .= '<xBairro>'.$cliente['complemento'].'</xBairro>';
					$xml .= '<cMun>'.$cliente['codigomunicipio'].'</cMun>';
					$xml .= '<xMun>'.$cliente['cidade'].'</xMun>';
					$xml .= '<UF>'.$cliente['uf'].'</UF>';
					$xml .= '<CEP>'.$cliente['cep'].'</CEP>';
					$xml .= '<cPais>'.$cliente['codigopais'].'</cPais>';
					// $xml .= '<xPais>'.$cliente['pais'].'</xPais>';
					$xml .= '<xPais>'.'Brasil'.'</xPais>';
				$xml .= '</enderDest>';
				$xml .= '<IE>'.$cliente['inscricaoestadual'].'</IE>';
			$xml .= '</dest>';

			$produtos = $this->getProdutos($this->osIds);
			$i = 1;
			$totalProd = 0;
			foreach ($produtos as $key => $q) {
				// print_r($q);
				echo $descricao = $this->removeCaracterEspecial(substr(utf8_encode($q['descricao']), 0, 119));
				$q['ncm/sh'] = 84123123;
				$xml .= '<det nItem="'.$i.'">';
					$xml .= '<prod>';
						$xml .= '<cProd>'.$q['codigo'].'</cProd>';
						$xml .= '<cEAN/>';
						$xml .= '<xProd>'.$descricao.'</xProd>';
						$xml .= '<NCM>'.$q['ncm/sh'].'</NCM>';
						$xml .= '<CFOP>5101</CFOP>';
						if($q['unidade'] == 'unit.'){
							$xml .= '<uCom>UN</uCom>';
						}else{
							$xml .= '<uCom>'.strtoupper($q['unidade']).'</uCom>';
						}
						$xml .= '<qCom>'.number_format($q['quantidade'],4).'</qCom>';
						$xml .= '<vUnCom>'.number_format($q['valorunidade'],6).'</vUnCom>';
						$xml .= '<vProd>'.number_format($q['valorunidade'] * $q['quantidade'],2).'</vProd>';
						$xml .= '<cEANTrib/>';
						if($q['unidade'] == 'unit.'){
							$xml .= '<uTrib>UN</uTrib>';
						}else{
							$xml .= '<uTrib>'.strtoupper($q['unidade']).'</uTrib>';
						}
						$xml .= '<qTrib>'.number_format($q['quantidade'],4).'</qTrib>';
						$xml .= '<vUnTrib>'.number_format($q['valorunidade'],6).'</vUnTrib>';
						$xml .= '<indTot>1</indTot>';
					$xml .= '</prod>';
					$xml .= '<imposto>';
						$xml .= '<ICMS>';
							$xml .= '<ICMSSN102>';
								$xml .= '<orig>0</orig>';
								$xml .= '<CSOSN>103</CSOSN>';
								// $xml .= '<pCredSN>2.82</pCredSN>';
								// $xml .= '<vCredICMSSN>16.64</vCredICMSSN>';
							$xml .= '</ICMSSN102>';
						$xml .= '</ICMS>';
						$xml .= '<PIS>';
							$xml .= '<PISOutr>';
								$xml .= '<CST>99</CST>';
								$xml .= '<vBC>0</vBC>';
								$xml .= '<pPIS>0</pPIS>';
								$xml .= '<vPIS>0</vPIS>';
							$xml .= '</PISOutr>';
						$xml .= '</PIS>';
						$xml .= '<COFINS>';
							$xml .= '<COFINSOutr>';
								$xml .= '<CST>99</CST>';
								$xml .= '<vBC>0</vBC>';
								$xml .= '<pCOFINS>0</pCOFINS>';
								$xml .= '<vCOFINS>0</vCOFINS>';
							$xml .= '</COFINSOutr>';
						$xml .= '</COFINS>';
					$xml .= '</imposto>';
				$xml .= '</det>';

				$i++;

				$totalProd += $q['valorunidade'] * $q['quantidade'];
			}

			$xml .= '<total>';
				$xml .= '<ICMSTot>';
					$xml .= '<vBC>0.00</vBC>';
					$xml .= '<vICMS>0.00</vICMS>';
					$xml .= '<vBCST>0.00</vBCST>';
					$xml .= '<vST>0.00</vST>';
					$xml .= '<vProd>'. number_format($totalProd,2) .'</vProd>';
					$xml .= '<vFrete>0.00</vFrete>';
					$xml .= '<vSeg>0.00</vSeg>';
					$xml .= '<vDesc>0.00</vDesc>';
					$xml .= '<vII>0.00</vII>';
					$xml .= '<vIPI>0.00</vIPI>';
					$xml .= '<vPIS>0.00</vPIS>';
					$xml .= '<vCOFINS>0.00</vCOFINS>';
					$xml .= '<vOutro>0.00</vOutro>';
					$xml .= '<vNF>'. number_format($totalProd,2) .'</vNF>';
				$xml .= '</ICMSTot>';
			$xml .= '</total>';
			$xml .= '<transp>';
				$xml .= '<modFrete>9</modFrete>';
			$xml .= '</transp>';
			// $xml .= '<cobr>';
			// 	$xml .= '<dup>';
			// 		$xml .= '<nDup>624</nDup>';
			// 		$xml .= '<dVenc>2013-02-08</dVenc>';
			// 		$xml .= '<vDup>590.00</vDup>';
			// 	$xml .= '</dup>';
			// $xml .= '</cobr>';
			$xml .= '<infAdic>';
				$xml .= '<infCpl>|DOCUMENTO FISCAL EMITIDO POR EMPRESA ME/EPP OPTANTE PELO SIMPLES NACIONAL NAO GERA CREDITO DE ICMS |</infCpl>';
			$xml .= '</infAdic>';
			$xml .=  '</infNFe>';
		   	$xml .=  '</NFe>';
			// echo $xml;

		   	#gera XML
			if (!file_put_contents($nfe->temDir . $chave.'-nfe.xml',$xml)){
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
			
			$nfe = new ToolsNFePHP($this->config);

			$arq = $this->getConteudoXml($nfe->temDir, $this->chave);

			if ($xml = $nfe->signXML($arq, 'infNFe')){
				
			    if(file_put_contents($nfe->assDir.$this->chave.'-nfe.xml', $xml)){
			    	unlink($nfe->temDir .'/'. $this->chave . '-nfe.xml');
			    	$this->logNotaFiscal($this->notaId, "Assinar XML", "XML assinado com sucesso!");
			    	return true;
			    }else{
			    	$this->logNotaFiscal($this->notaId, "Assinar XML", "erro ao salvar o XML assinado!");
			    	return false;
			    }
			   
			} else {
				$this->logNotaFiscal($this->notaId, "Assinar XML", utf8_decode($nfe->errMsg));
			    return false;
			}
			unset($nfe);
		}

		#Função que valida o XML antes de enviar 
		public function validaXml(){
			$nfe = new ToolsNFePHP($this->config);
			header('Content-type: text/html; charset=UTF-8');
			// $this->notaId = 7;
			// $this->chave = '35131012344567000164550010000000071152918728';
			$arq = $this->getConteudoXml($nfe->assDir, $this->chave);
			$xsdFile = '../nfephp/schemes/PL_006s/nfe_v2.00.xsd';

			$aErro = array();
			$c = $nfe->validXML($arq,$xsdFile,$aErro);
			if (!$c){
				$this->logNotaFiscal($this->notaId, "Validar XML", "O xml contém erros, tente refazer a operação!");
			    return false;
		    }else{
		    	if(file_put_contents($nfe->conDir.$this->chave.'-nfe.xml', $arq)){
			    	unlink($nfe->assDir .'/'. $this->chave . '-nfe.xml');
			    	$this->logNotaFiscal($this->notaId, "Validar XML", "XML validado com sucesso!");
			    	return true;
			    }else{
			    	$this->logNotaFiscal($this->notaId, "Validar XML", "Erro ao alterar local do XML!");
			    	return false;
			    }
		    	
		    }

			unset($nfe);
		}

		#Função que envia o XML
		public function enviaXml(){
			$nfe = new ToolsNFePHP($this->config);
			$this->lote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
			$aNFe = array(0 => $this->getConteudoXml($nfe->conDir, $this->chave));
			// print_r($NFe);
			$aResp = array();
			if ($aResp = $nfe->sendLot($aNFe, $this->lote, $this->modSOAP)){
			    if ($aResp['bStat']){
			    	if(mysql_query("UPDATE notas_fiscais SET recibo = '{$aResp['nRec']}' WHERE id = {$this->notaId}")){
			    		$this->recibo = $aResp['nRec'];
			    		
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
			}

		}

		#Função que consulta o recibo da NF
		public function consultaRecibo(){
			$nfe = new ToolsNFePHP($this->config);
			$aResp = array();
			$ch = '';
			// $this->recibo = '351001911892124';
			// $this->chave = '35131012344567000164550050000000161953853173';
			// $this->notaId = 16;
			if ($aResp = $nfe->getProtocol($this->recibo, $ch, $this->tpAmb, $this->modSOAP)){
				// print_r($aResp);
				if($aResp['aProt']['0']['cStat'] == '100'){

					if($this->alteraStatusNota($this->notaId, 2)){				
						$protFile = $nfe->temDir . $this->recibo . '-recprot.xml'; 

						$nfeFile = $nfe->conDir . $this->chave.'-nfe.xml';

						$procnfe = $nfe->addProt($nfeFile, $protFile); 
						
						$pathPutContent = $nfe->aprDir . $this->chave . '-nfe.xml';

						if (file_put_contents($pathPutContent, $procnfe)) { 
							unlink($protFile);

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
					$this->logNotaFiscal($this->notaId, "Retornar XML", utf8_decode($aResp['xMotivo']));
					return false;
				}
			}else{
				$this->logNotaFiscal($this->notaId, "Retornar XML", "Falha ao se conectar ao SEFAZ!");
				return false;
			}
		}

		#Função para gerar o DANFE
		public function geraDanfe(){
			$nfe = new ToolsNFePHP($this->config);
			include_once('../nfephp/libs/DanfeNFePHP.class.php');
			if($this->status == 2)
				$path = $nfe->aprDir;
			else if($this->status == 3)
				$path = $nfe->repDir;
			else
				$path = $nfe->canDir;


			if ( is_file($path . $this->chave."-nfe.xml")){
			    $docxml = $this->getConteudoXml($path, $this->chave);
			    $danfe = new DanfeNFePHP($docxml, 'P', 'A4',$nfe->danfelogopath,'I','');
			    $id = $danfe->montaDANFE();
			    $pathPutContent = $nfe->pdfDir . $this->chave . '-danfe.php';
			    $teste = $danfe->printDANFE($id.'.pdf','I');
			    if (file_put_contents($pathPutContent, $teste)) {
			    	$this->logNotaFiscal($this->notaId, "Gerar DANFE", "DANFE gerado com sucesso!");
			    }else{
			    	$this->logNotaFiscal($this->notaId, "Gerar DANFE", "Erro ao exportar o danfe!");
			    }

			   
			}else{
				echo "XML não encontrado!";
			}
		}

		#Função para cancelar a NF
		public function cancelaNota(){			
			$nfe = new ToolsNFePHP($this->config);
			if ($resp = $nfe->cancelEvent($this->chave,$this->protocolo,$this->justificativa,$this->tpAmb,$this->modSOAP)){
				$this->alteraStatusNota($this->notaId, 4);
				$this->logNotaFiscal($this->notaId, "Cancelar XML", "Nota cancelada com sucesso!");
				return true;
			} else {
				$this->logNotaFiscal($this->notaId, "Cancelar XML", utf8_decode($nfe->errMsg));
			    return false;
			}   
		}


		#Função para emissão de carta de correção 
		public function cartaCorrecao(){
			$nfe = new ToolsNFePHP($this->config);
			$aResp= array();
			if ($xml = $nfe->envCCe($this->chave, $this->correcao, $nSeqEvento, $this->tpAmb, $this->modSOAP, $aResp)){
			    $this->logNotaFiscal($this->notaId, 'Carta de Correção', 'Carta de correção gerada com Sucesso!');
			   	return true;
			 } else {
				$this->logNotaFiscal($this->notaId, 'Carta de Correção', 'Problemas ao gerar o XML!');
				return false;
			}

			

		}
		#Função que pega o conteudo do XML
		public function getConteudoXml($userPath, $chave){
			$file = $userPath.$chave.'-nfe.xml';
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
			$buscaNf = mysql_query("SELECT count(*) total FROM notas_fiscais WHERE numero = '{$numeroNF}' AND chave <> '' AND status IN (1,3)") or die(mysql_error());
			$bNf = mysql_fetch_array($buscaNf);

			if($bNf['total'] != 0){
				return false;
				
			}else{
				return true;
			}
		}

		#função para reiniciar o processo
		public function resetaProcesso(){
			$nfe = new ToolsNFePHP($this->config);
			$buscaNf = mysql_query("SELECT * FROM notas_fiscais WHERE numero = '{$numeroNF}' AND chave <> '' AND status IN (1,3)") or die(mysql_error());
			$bNf = mysql_fetch_array($buscaNf);

			$chave = $bNf['chave'];

			unlink($nfe->temDir . $chave . '-nfe.xml');
			if(mysql_query("UPDATE notas_fiscais SET chave = '' WHERE numero = {$numeroNF}")){
				return true;
			}else{
				return false;
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

		#função que busca os dados da os
		public function getOs(){
			$id = explode('-', $this->osIds);

			$busca_os = mysql_query("SELECT * FROM aberturaos WHERE id = {$id['0']}") or die(mysql_error());
			$bo = mysql_fetch_array($busca_os);

			foreach ($bo as $key => $v) {
				$this->osData[$key] = $v;
			}
		}

		#função que retorna o cliente para o qual será emitido a nota
		public function getCliente($idcliente){
		
			$query = mysql_query("SELECT * FROM cliente WHERE id = {$idcliente}") or die(mysql_error());
			$array = mysql_fetch_array($query);
			$cliente = array();
			foreach ($array as $key => $value) {
				$cliente[$key] = $value;
			}

			if($this->config['ambiente'] == '2'){

				$cliente['cnpjcliente'] = '99999999000191';
				$cliente['nomecliente'] = 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';
				$cliente['inscricaoestadual'] = 'ISENTO';
				
			}

			return $cliente;
											
		}

		#função que retorna os produtos de os
		public function getProdutos($agrupar){
			$numos = explode("-",$agrupar);


  			while (key($numos) !== null){
	  
  
  				$ordemdeservico = current($numos);
      			//busco todos os itens de cada o.s
				$calc = mysql_query("SELECT * FROM itensordemservicodigital  WHERE idpai = '$ordemdeservico'");
				while ($prod = mysql_fetch_array($calc)) {
					
					//monto a descrição do produtos
					$descricao = $prod['descricao'];
					
					//criar codigo
					$codmidia = $prod['midia'];
				    $acabmidia = $prod['acabamento'];
					
					$cod = mysql_query("SELECT * FROM midiasdigitais  WHERE nome = '$codmidia'");
				    $mid = mysql_fetch_array($cod);
					$idmidia = $mid['id'];
					
					$cod = mysql_query("SELECT * FROM acabamentos  WHERE nome = '$acabmidia'");
				    $mid = mysql_fetch_array($cod);
					$idacab = $mid['id'];
					$ncm = $mid['ncm'];
					//codigo id da midia mais o id do acabamento
					$codigo = $idmidia.$idacab;
					
					
					// array final
					$produtos[] = array('codigo'=>$codigo, 'descricao'=>$descricao, 'ncm/sh' =>$ncm, 'o/cst'=>"0", 'cfod'=>"0", 'unidade'=> "m2", 'quantidade'=> $prod['quantidade'], 'valorunidade'=>$prod['valormetro'], 'subtotal'=>$prod['subtotal']);
					
				}
				
				$calc = mysql_query("SELECT * FROM itensordemservicomontagem  WHERE idpai = '$ordemdeservico'");
				while ($prod = mysql_fetch_array($calc)) {
					//monto a descrição do produtos
					$descricao = $prod['descricao'];
					$produto = $prod['produto'];
					$modelo = $prod['modelo'];
					
					$cod = mysql_query("SELECT * FROM produtosestrutura  WHERE produto = '$produto'");
					$mid = mysql_fetch_array($cod);
					$idpai = $mid['id'];
					
					$cod = mysql_query("SELECT * FROM itensprodutosestrutura  WHERE modelo = '$modelo' && idpai = '$idpai'");
				    $mid = mysql_fetch_array($cod);
					$ncm = $mid['ncm'];
					
					$codigo = $idpai.$mid['id'];
					
					
					// array final
					$produtos[] = array('codigo'=>$codigo, 'descricao' =>$descricao, 'ncm/sh' => $ncm, 'o/cst'=>0, 'cfod'=>0, 'unidade'=> $prod['unidcusto'], 'quantidade'=> $prod['quantidade'], 'valorunidade'=>$prod['valorunidade'], 'subtotal'=>$prod['subtotal']);
				
				}
				  
		  
				$calc = mysql_query("SELECT * FROM itensordemservicoletracaixa  WHERE idpai = '$ordemdeservico'");
							
				while ($prod = mysql_fetch_array($calc)) {
						//monto a descrição do produtos
					$descricao = $prod['descricao'];
					
					//criar codigo
					$letra = $prod['produto'];
					
					$cod = mysql_query("SELECT * FROM letracaixa  WHERE nome = '$letra'");
				    $mid = mysql_fetch_array($cod);
					$ncm = $mid['ncm'];
					$codigo = $mid['id'];
					
					// array final
					$produtos[] = array('codigo'=>$codigo, 'descricao' =>$descricao, 'ncm/sh' => $ncm, 'o/cst'=>0, 'cfod'=>0, 'unidade'=> "unit.", 'quantidade'=> $prod['quantidade'], 'valorunidade'=>$prod['valorunitario'], 'subtotal'=>$prod['subtotal']);
				
				}
					         
			  
				next($numos);
			}
			
			return $produtos;  
		}

		#função que remove caracteres especiais
		public function removeCaracterEspecial($string){
			$characteres = array(
			    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
			    'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
			    'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
			    'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
			    'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
			    'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
			    'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', ' '=>' ', '-'=>' ', '.'=>' ',
			    ','=>' ', 
			);

			return strtr($string, $characteres);
		}

		#função que remove a pontuação do cnpj
		public function removePontoCnpj($str){
			$str = str_replace('.', '', $str);
			$str = str_replace('-', '', $str);
			$str = str_replace('/', '', $str);

			return $str;
		}

		public function teste(){
			$nfe = new ToolsNFePHP($this->config);

			echo $nfe->cUF;
		}

	}
?>