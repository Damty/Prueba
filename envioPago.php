<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8"1>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pago en Linea</title>
<script type="text/javascript" src="../js/jquery.js" ></script>
<script type="text/javascript" src="../js/funciones.js" ></script>
</head>
<body>
<?php
header("Content-Type: text/html; charset=iso-8859-1");
include("modelo.php"); $obj = new registro();
$id_pa=$_POST['id_participante'];
$participante = $obj->participante($id_pa);
$nombre=$participante['nombre']." ".$participante['a_paterno']." ".$participante['a_materno'];

$datos_registro =$obj->registros($id_pa);
$id_evento=$datos_registro['id_evento'];

$datos_evento=$obj->evento($id_evento);
?>
<div style="display:none;">
<?php
if(function_exists('curl_init')){ // Comprobamos si hay soporte para cURL
	$id_pedido=rand(0,10).date('YmdHis');

	$data = array(
                 'idServicio'        => urlencode('3'),
                 'idSucursal'        => urlencode($_POST['idSucursal']),
                 'idUsuario'         => urlencode($_POST['idUsuario']),
                 'nombre'            => urlencode($nombre),
                 'numeroTarjeta'     => urlencode($_POST['numeroTarjeta']),
                 'cvt'               => urlencode($_POST['cvt']),
                 'mesExpiracion'     => urlencode($_POST['mesExpiracion']),
                 'anyoExpiracion'    => urlencode($_POST['anyoExpiracion']),
                 'monto'             => urlencode($_POST['monto']),//formato 1000.00
                 'email'             => urlencode($participante['email']),
                 'telefono'          => urlencode($participante['telefono']), // son 10 digitos
                 //'calleyNumero'      => urlencode($_POST['calleyNumero']),
                 //'colonia'           => urlencode($_POST['colonia']),
                 //'municipio'         => urlencode($_POST['municipio']),
                 //'estado'            => urlencode($_POST['estado']),
                 //'pais'              => urlencode($_POST['pais']),
                 'idPedido'          => urlencode($id_pedido),
                 'param1'            => urlencode($_POST['param1']),

                 'ip'                => urlencode($_SERVER['REMOTE_ADDR']),
                 'httpUserAgent'     => urlencode($_SERVER['HTTP_USER_AGENT']),
                 );
	$cadena='';
    foreach ($data as $key=>$valor){
		$cadena.="&data[$key]=$valor";
    }

	$url = 'https://www.pagofacil.net/st/public/Wsrtransaccion/index/format/json/?method=transaccion'.$cadena;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    var_dump($response);

    $response = json_decode($response,true);
    $response=$response['WebServices_Transacciones']['transaccion'];

    $valido = $response['autorizado'];
	$error = $response['texto'];


	if(($response['autorizado']=='1' || $response['autorizado']==1) && ($valido=='1' || $valido==1)){
		echo "Pago Realiazo";
		$obj->status($id_pa,3);
		$obj->hora_pago($id_pa);
		$obj->codigo($id_pa);
	}else{
		echo "<br><br><br>Fallo Validaci&oacute;n";
	}

}else echo "No hay soporte para cURL";

?>
</div>
<div>Datos del Participante</div>
	<div>Nombre: <?=$nombre?><div>
	<div>Correo: <?=$participante['email']?></div>
	<div>Tel&eacute;fono: <?=$participante['telefono']?></div>
	<div>Cuidad: <?=$participante['cuidad']?>
<div>Datos del Evento</div>
	<div>Tema: <?=$datos_evento['Tema']?></div>
	<div>Lugar: <?=$datos_evento['Lugar']?></div>
	<div>Fecha: <?=$datos_evento['Fechainicio']?>
<div>Datos Compra</div>
	<div>N&uacute;mero de Pedido: <?=$id_pedido?></div>
	<div>Tipo de Entrada: <?=$_POST['tipo_entrada']?></div>
	<div>Costo: <?=$_POST['costo']?></div>
	<div>C&oacute;digo de Promoci&oacute;n: <?=$_POST['codigo_promocion']?></div>
	<div>N&uacute;mero de Transacci&oacute;n: <?php
		if(($response['autorizado']=='1' || $response['autorizado']==1) && ($valido=='1' || $valido==1)){
			echo $response['transaccion'];
		}else{ echo '<span>No se gener&oacute; el cargo</span><br />'; }
		?></div>
	<div>N&uacute;mero de Autorizaci&oacute;n: <?php
    	if(($response['autorizado']=='1' || $response['autorizado']==1) && ($valido=='1' || $valido==1)){
			echo $response['transaccion'];
		}else{ echo '<span>No se gener&oacute; el cargo</span><br />'; }
	?></div>
	<div>Inicio Transacci&oacute;n: <?=$response['TransIni']?></div>
	<div>Fin Transacci&oacute;n: <?=$response['TransFin']?></div>
	<div>Estatus del Pago: <?php
    	if (($response['autorizado']=='1' || $response['autorizado']==1) && ($valido=='1' || $valido==1)){
			echo '<span class="cargo_ok">Cargo Exitoso</span>';
		} else {
			echo '<span class="error_cargo">Error al Realizar el Cargo, Verifique...</span><br />'.$error;}
	?></div>
</body>
</html>
