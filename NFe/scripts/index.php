<?php  
	include_once('config.php');
	$conn = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
	mysql_select_db(DATABASE_NAME, $conn);

	$busca_nota_max = mysql_query("SELECT max(numero) max FROM notas_fiscais");
	$bnm = mysql_fetch_array($busca_nota_max);

	$busca_nota = mysql_query("SELECT nf.*, s.id status_id, s.status status_nome FROM notas_fiscais nf, notas_fiscais_status s WHERE nf.status = s.id ORDER BY nf.numero");
?>

<!DOCTYPE html>
<html>
	<head>
	    <title>Nota Fiscal Eletronica</title>
	    <meta charset="UTF-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
		<!-- Optional theme -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
		
	    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	    <!--[if lt IE 9]>
	      <script src="../../assets/js/html5shiv.js"></script>
	      <script src="../../assets/js/respond.min.js"></script>
	    <![endif]-->
	</head>
	<body>
		<section style="width: 1000px;margin: 0 auto;">
			<div>
				<h1>Nota Fiscal Eletronica</h1>
			</div>
			<div style="margin-bottom: 20px;">
		
				<input style="height: 33px;" id="numero_nota" type="text" placeholder="Numero da nota" value="<?php echo $bnm['max'] + 1; ?>">
				<a href="#" style="margin-top: -3px;" class="btn btn-success btn-large" id="emitir-nota">
					<i class="icon-white icon-plus-sign"></i> Emitir Nota
				</a>
			</div>
			<div>
				<table class="table table-striped table-bordered table-condensed table-hover">
				 <!-- <table class="table table-striped" width="647"> -->
					<thead>
						<th width="10%">Numero</th>
						<th width="27%">Cliente</th>
						<th width="15%">Pedido</th>
						<th width="11%">Status</th>
						<th width="12%"></th>
						<th width="15%"></th>
						<th width="10%"></th>
					</thead>
					<?php while($bn = mysql_fetch_array($busca_nota)){ ?>
						<tr>
							<td><?php echo $bn['numero']; ?></td>
							<td>Joyvis Santana</td>
							<td>102345</td>
							<td>
								<?php 
									echo strtoupper($bn['status_nome']); 
								?>
							</td>
							<td>
								<?php if($bn['status_id'] == 2){ ?>
								<a target="_blank" href="gerar_danfe.php?nota=<?php echo $bn['id']; ?>">
									GERAR DANFE
								</a>
								<?php }else{ ?>
									GERAR DANFE
								<?php } ?>
							</td>
							<td>
								<?php if($bn['status_id'] == 2){ ?>
								<a target="_blank" href="gerar_carta_correcao.php?nota=<?php echo $bn['id']; ?>">
									CARTA CORREÇÃO
								</a>
								<?php }else{ ?>
									CARTA CORREÇÃO
								<?php } ?>
							</td>
							<td>
								<?php if($bn['status_id'] == 2){ ?>
								<a data-id="<?php echo $bn['id']; ?>" data-num="<?php echo $bn['numero']; ?>" href="#" class="cancela-nota">
									CANCELAR
								</a>
								<?php }else{ ?>
									CANCELAR
								<?php } ?>
							</td>
							<td></td>
						</tr>
					<?php } ?>
				</table>
			</div>
		</section>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="//code.jquery.com/jquery.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
		<script>
			$('#emitir-nota').click(function(e){
				e.preventDefault();
				$.post('emitir.php', {numero_nota : $('#numero_nota').val()}, function(r){
					if(!r.success){
						alert(r.msg);						
					}
					window.location.reload();
				}, 'json');
			});

			$('.cancela-nota').on('click', function(e){
				e.preventDefault();
				var nota_numero = $(this).attr('data-num');
				var nota_id = $(this).attr('data-id');
				if(confirm("Deseja cancelar a nota numero " + nota_numero + "?" )){
					var j = "";	
					if(j =prompt("Digite a justificativa de cancelamento:")){
						if(j != "" && j.length > 10){
							$.post('cancelar.php', {nota_id : nota_id, justificativa : j}, function(r){
								if(!r.success){
									alert(r.msg);
								}else{
									alert('Nota cancelada com sucesso!');
									window.location.reload();
								}
							}, 'json');
						}else{
							alert("A justificativa deve ter no minimo 20 caracteres!");
						}
					}
					
				}
			});
		</script>
	</body>
</html>