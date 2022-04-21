<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../login/login_on.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php require_once('../Connections/kronos.php'); 
mysql_query("SET NAMES 'utf8'"); 
include("../funciones/funcion.php"); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$editFormAction = limpia_ruta($_SERVER['PHP_SELF']);
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {

$muestra_error="sigue";
$ruta_doc=$_POST['documentos_ruta_2'];

//subir el documento

if (is_uploaded_file($_FILES["up_documento"]["tmp_name"])) {	
$archivo = $_FILES["up_documento"]["tmp_name"];   
$tamanio = $_FILES["up_documento"]["size"];  
$nombre  = $_FILES["up_documento"]["name"];  
$extension=strtolower(substr($_FILES["up_documento"]["name"],-3,3));

$grupo=$_POST['documento_grupo'];
if(($grupo==3 or $grupo==5 or $grupo==6) and $ruta_doc=='') {
$validos=array(doc,pdf,xls,ocx,lsx,msg); 
$error_1=2; } else {
$validos=array(pdf,doc,xls,ocx,lsx,msg);
$error_1=1;	}

if (in_array($extension,$validos) && $_FILES['up_documento']['size'] < 24000000) { 

$fp = fopen($archivo, "rb");  
$contenido = fread($fp, $tamanio); 
fclose($fp);  

// directorio de almacenamiento de los archivos 
if($extension=='ocx') { $extension='docx'; }
if($extension=='lsx') { $extension='xlsx'; }	
if($_POST['documentos_ruta_2']=="") {
$ruta_doc="../documentos/".$_POST['documentos_caso']."/".$_POST['documentos_caso']."_documet_".$_POST['filt_doc'].".".$extension; 
} else {
$ruta_doc=substr($_POST['documentos_ruta_2'], 0, -3);
$ruta_doc=$ruta_doc.$extension; }

// Subimos documento                 
move_uploaded_file($archivo, $ruta_doc); } else { 

if($error_1==1) {
$muestra_error="<br />Formato o Tamaño de Documento no cumple los requisitos: <br /> - Formato: Word, Excel o PDF <br /> - Tamaño máximo 20 MB <br /><br /> "; } else { 
$muestra_error="<br />Formato o Tamaño de Documento no cumple los requisitos: <br /> - Formato: Word, Excel o PDF<br /> - Tamaño máximo 20 MB <br /><br /> "; }
  } }

if($muestra_error == "sigue") {

if($_POST['documentos_fecha_envio']=="") { $fecha_envio=""; } else { $fecha_envio=fech_guarda($_POST['documentos_fecha_envio']); } 
if($_POST['documentos_estado']==5 and $fecha_envio=="") { $fecha_envio=date('Y-m-d'); }
	
  $updateSQL = sprintf("UPDATE documentos SET documentos_estado=%s, documentos_n_acta=%s, documentos_ruta=%s, documentos_observaciones=%s, documentos_fecha_envio=%s WHERE documentos_id=%s",
                       GetSQLValueString($_POST['documentos_estado'], "int"),
					   GetSQLValueString($_POST['documentos_n_acta'], "text"),
                       GetSQLValueString($ruta_doc, "text"),
                       GetSQLValueString($_POST['documentos_observaciones'], "text"),
					   GetSQLValueString($fecha_envio, "text"),
                       GetSQLValueString($_POST['filt_doc'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error());

if($extension=="doc" or $extension=="docx") {  
   $insertSQL = sprintf("INSERT INTO documentos_word (doc_word_caso, doc_word_user, doc_word_grupo, doc_word_ruta) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['documentos_caso'], "int"),
					   GetSQLValueString($_POST['documentos_chat_user'], "int"),
					   GetSQLValueString($_POST['documento_grupo'], "int"),
                       GetSQLValueString($ruta_doc, "text"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($insertSQL, $kronos) or die(mysql_error()); }


if($_POST['documentos_chat_observacion']=="") { $chat_observacion="Sin Observaciones"; } else { $chat_observacion=$_POST['documentos_chat_observacion']; }  
  $insertSQL = sprintf("INSERT INTO documentos_chat (documentos_chat_caso, documentos_chat_doc, documentos_chat_user, documentos_chat_estado, documentos_chat_observacion) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['documentos_caso'], "int"),
					   GetSQLValueString($_POST['filt_doc'], "int"),
					   GetSQLValueString($_POST['documentos_chat_user'], "int"),
					   GetSQLValueString($_POST['documentos_estado'], "int"),
                       GetSQLValueString($chat_observacion, "text"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($insertSQL, $kronos) or die(mysql_error());

if($_POST['documentos_estado']==4 and $_POST['control_factura']==3) {  
    $updateSQL = sprintf("UPDATE casos SET caso_fech_valoriza=%s WHERE caso_id=%s",
                       GetSQLValueString(date('Y-m-d'), "text"),
                       GetSQLValueString($_POST['documentos_caso'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error()); }
  
if($_POST['documentos_estado']==4 and $_POST['control_factura']==4) {  
    $updateSQL = sprintf("UPDATE casos SET caso_estado=%s, caso_fech_valoriza=%s WHERE caso_id=%s",
                       GetSQLValueString(5, "int"),
					   GetSQLValueString(date('Y-m-d'), "text"),
                       GetSQLValueString($_POST['documentos_caso'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error()); 

  
$filt_caso_f=$_POST['documentos_caso'];  
mysql_select_db($database_kronos, $kronos);
$query_factura = "SELECT * 
FROM carga_datos 
WHERE carga_caso = '$filt_caso_f' AND carga_tipo = 1 AND carga_exito = 'Si'
ORDER BY carga_datos.carga_id ASC";
$factura = mysql_query($query_factura, $kronos) or die(mysql_error());
$row_factura = mysql_fetch_assoc($factura);
$totalRows_factura = mysql_num_rows($factura);  


if($row_factura['carga_id']!="") {  

for($i = 1; $i <= $totalRows_factura ; $i++){
  
  $updateSQL = sprintf("UPDATE casos SET caso_estado=%s, caso_fech_valoriza=%s WHERE caso_id=%s",
                       GetSQLValueString(5, "int"),
					   GetSQLValueString(date('Y-m-d'), "text"),
                       GetSQLValueString($row_factura['carga_text_1'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error());  
  
$row_factura=mysql_fetch_assoc($factura);
}  }
  
mysql_free_result($factura);   }  

// CASO PASA A LIQUIDADO
  
if($_POST['documentos_estado']==5 and $_POST['documentos_pre_estado']==2) { 
if($_POST['fecha_liquida_caso']=="") { $fecha_liquida=date('Y-m-d'); } else { $fecha_liquida=$_POST['fecha_liquida_caso']; } 
    $updateSQL = sprintf("UPDATE casos SET caso_estado=%s, caso_estado_real=%s, caso_fech_liquidacion=%s WHERE caso_id=%s",
                       GetSQLValueString(2, "int"),
					   GetSQLValueString(8, "int"),
					   GetSQLValueString($fecha_liquida, "text"),
                       GetSQLValueString($_POST['documentos_caso'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error()); 
  
   $insertSQL = sprintf("INSERT INTO casos_real_historial (caso_casos_real_hist, estado_casos_real_hist, user_casos_real_hist) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['documentos_caso'], "int"),
                       GetSQLValueString(8, "int"),
                       GetSQLValueString($login_id, "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($insertSQL, $kronos) or die(mysql_error());   
  
$filt_caso_f=$_POST['documentos_caso']; 


require 'documento_envia_mapfre.php';

mysql_select_db($database_kronos, $kronos);
$query_factura = "SELECT * 
FROM carga_datos 
WHERE carga_caso = '$filt_caso_f' 
ORDER BY carga_datos.carga_id ASC";
$factura = mysql_query($query_factura, $kronos) or die(mysql_error());
$row_factura = mysql_fetch_assoc($factura);
$totalRows_factura = mysql_num_rows($factura);  



if($row_factura['carga_id']!="") {  

for($i = 1; $i <= $totalRows_factura ; $i++){
  
  $updateSQL = sprintf("UPDATE casos SET caso_estado=%s, caso_estado_real=%s, caso_fech_liquidacion=%s WHERE caso_id=%s",
                       GetSQLValueString(2, "int"),
					   GetSQLValueString(8, "int"),
					   GetSQLValueString($fecha_liquida, "text"),
                       GetSQLValueString($row_factura['carga_text_1'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error());  
  
$row_factura=mysql_fetch_assoc($factura);
}  }
  
mysql_free_result($factura);     } 

if ((isset($_POST["caso_causa_rechazo"])) && ($_POST["caso_causa_rechazo"] != "")) {
  $updateSQL = sprintf("UPDATE casos SET caso_causa_rechazo=%s WHERE caso_id=%s",
                       GetSQLValueString($_POST['caso_causa_rechazo'], "int"),
                       GetSQLValueString($_POST['documentos_caso'], "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($updateSQL, $kronos) or die(mysql_error()); }
  
  
if($_POST['documentos_estado']==4 and $_POST['estado_caso']!=5) {    
$insertSQL = sprintf("INSERT INTO histo_estado_caso (his_est_cas_caso, his_est_cas_user, his_est_cas_estado) VALUES (%s, %s, %s)",
                       GetSQLValueString($_POST['documentos_caso'], "int"),
                       GetSQLValueString(5, "int"),
                       GetSQLValueString($login_id, "int"));

  mysql_select_db($database_kronos, $kronos);
  $Result1 = mysql_query($insertSQL, $kronos) or die(mysql_error());  }
  
  include("estado_real.php");  } 

//Evita salto de pagina cuando formato no corresponde
if($muestra_error=="sigue" and $_POST['documentos_estado']!=1) {	
  $updateGoTo = "datos.php?caso=".dato_envia($_POST['documentos_caso']);
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));  } 
} 


$filt_doc=$_POST['filt_doc'];
//$filt_doc=1;
mysql_select_db($database_kronos, $kronos);
$query_documentos = "SELECT documentos.documentos_id, documentos.documentos_caso, formatos.formato_nombre AS nombre_formato, formatos.formato_format AS formatt, formatos.formato_ruta_php AS ruta, formatos.formato_control AS control, formatos.formato_ruta AS ruta_formato, formatos.formato_text, documentos.documentos_estado, documentos.documentos_ruta, documentos.documentos_observaciones, documentos.documentos_n_acta, documentos.documentos_fecha_envio, documentos.documentos_texto, documentos.documentos_fecha, formatos.formato_grupo AS documento_grupo, documentos_grupo.doc_grup_tipo AS tipo_document, casos.caso_fech_liquidacion AS fech_liquidacion, casos.caso_estado AS estado_caso, casos.caso_estado_real AS estado_real, casos.caso_user_1 AS ajustador_1, casos.caso_cia_seguros AS cia_1, casos.caso_division AS division_1, casos.caso_cobertura_mas , casos.caso_n_poliza, casos.caso_cobertura, casos.caso_causa_rechazo, casos.caso_estado_procesal    
FROM documentos 
INNER JOIN formatos ON formatos.formato_id = documentos.documentos_formato
INNER JOIN documentos_grupo ON documentos_grupo.doc_grup_id = formatos.formato_grupo
INNER JOIN casos ON casos.caso_id = documentos.documentos_caso 
WHERE documentos_id = '$filt_doc'";
$documentos = mysql_query($query_documentos, $kronos) or die(mysql_error());
$row_documentos = mysql_fetch_assoc($documentos);
$totalRows_documentos = mysql_num_rows($documentos);

$grupo_2=$row_documentos['documento_grupo'];
$extension_doc_2=strtolower(substr($row_documentos['documentos_ruta'],-3,3));
$control=$row_documentos['control'];

$sale="";
if($row_documentos['nombre_formato']=="No requere") {
$extension_doc_2="pdf"; 
$sale="no control"; }

// Edición
$filt_estado=" estado_documentos_id = 1 "; 

// En Despacho
if($row_documentos['documentos_estado']==2 or $login_tipo==6) { $filt_estado.=" OR estado_documentos_id = 2 "; } 

// Observación
$filt_estado.=" OR estado_documentos_id = 3 "; 


// Facturación RC
if($row_documentos['division_1']==4) {
if($row_documentos['documentos_estado']==4 or $row_documentos['documentos_estado']==8 and $perdida_neta > 0) { 
  $filt_estado.=" OR estado_documentos_id = 4 "; 
  require_once ('../class/PHPMailer/class.phpmailer.php');
  require '../class/PHPMailer/class.smtp.php';
  include ('mail_facturacion.php');}
}



// Facturación
if($row_documentos['documentos_estado']==4 or $row_documentos['documentos_estado']==8 and $row_documentos['division_1']!=4) { $filt_estado.=" OR estado_documentos_id = 4 "; } 

// Facturación Parcial para jefes de area
if($login_tipo==4 and $control==3)
{ $filt_estado.=" OR estado_documentos_id = 4 "; }

// Finalizado
if((($login_tipo==3 or $login_tipo==7) and $control==1) or ($login_tipo==4 and $control<=2) or $row_documentos['documentos_estado']==5 or $row_documentos['documentos_estado']==2) { $filt_estado.=" OR estado_documentos_id = 5 "; } 

// Anulado
$filt_estado.=" OR estado_documentos_id = 6 ";

// En Revisión de Jefe
if(($login_tipo==3 and $control>=2) or $row_documentos['documentos_estado']==7) { $filt_estado.=" OR estado_documentos_id = 7 "; } 

// En Revisión de Jefe de Area
if(($login_tipo==4 and $control>=3) or $row_documentos['documentos_estado']==8) { 
if($row_documentos['division_1']==3)
{ $filt_estado.=" OR estado_documentos_id = 2 OR estado_documentos_id = 4 OR estado_documentos_id = 8 "; } else { $filt_estado.=" OR estado_documentos_id = 4 OR estado_documentos_id = 8 "; } }


if($login_tipo==1 or $login_tipo==5) { $filt_estado=" estado_documentos_id != 99 "; } 

mysql_select_db($database_kronos, $kronos);
$query_estado = "SELECT * FROM estado_documentos WHERE $filt_estado ORDER BY estado_documentos_nombre ASC";
$estado = mysql_query($query_estado, $kronos) or die(mysql_error());
$row_estado = mysql_fetch_assoc($estado);
$totalRows_estado = mysql_num_rows($estado);

$filt_caso=$row_documentos['documentos_caso'];

//include("documentos_valida.php");


$padre="documentos";
$_POST['edita_dato']=$row_documentos['documentos_caso'];

mysql_select_db($database_kronos, $kronos);
$query_datos_caso = "SELECT casos.caso_fech_ocurren, casos.caso_fech_denuncio, casos.caso_recupero, casos.caso_perdida_bruta, casos.caso_deducible, casos.caso_ramo_fecu, asegurado.asegurado_fech_nac AS asegurados_naci, casos.caso_moneda    
FROM casos 
INNER JOIN asegurado ON asegurado.asegurado_id = casos.caso_asegurado 
WHERE caso_id = '$filt_caso'";
$datos_caso = mysql_query($query_datos_caso, $kronos) or die(mysql_error());
$row_datos_caso = mysql_fetch_assoc($datos_caso);
$totalRows_datos_caso = mysql_num_rows($datos_caso);

mysql_select_db($database_kronos, $kronos);
$query_causa_rechazo = "SELECT causas_rech_id, causas_rech_nombre FROM causas_rechazo ORDER BY causas_rech_nombre ASC";
$causa_rechazo = mysql_query($query_causa_rechazo, $kronos) or die(mysql_error());
$row_causa_rechazo = mysql_fetch_assoc($causa_rechazo);
$totalRows_causa_rechazo = mysql_num_rows($causa_rechazo);

$cobe_mensaje="";
$n_poliza=$row_documentos['caso_n_poliza'];
$cobertura_masivos=$row_documentos['caso_cobertura_mas'];
$id_coberturas=$row_documentos['caso_cobertura'];
$asegurados_nacimiento=$row_datos_caso['asegurados_naci'];
if($row_datos_caso['caso_recupero']<=0) {
$reclamo=0; } else { $reclamo=$row_datos_caso['caso_recupero']; }
if($row_datos_caso['caso_perdida_bruta']<=0) {
$valor_ajuste=0; } else { $valor_ajuste=$row_datos_caso['caso_perdida_bruta']; }
if($row_datos_caso['caso_deducible']<=0) {
$deducible=0; } else { $deducible=$row_datos_caso['caso_deducible']; }
$indemnizacion=$valor_ajuste-$deducible;
$ramo_fecu=$row_datos_caso['caso_ramo_fecu'];
$moneda=$row_datos_Caso['caso_moneda'];
$perdida_bruta=$row_datos_caso['caso_perdida_bruta'];
$deducible_p=$row_datos_caso['caso_deducible'];
$perdida_neta=$perdida_bruta-$deducible_p;

if($row_documentos['division_1']==3)
{ include("cobertura_total_cod.php"); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Modificar Documento</title>
<link href="../css/cuerpo.css" rel="stylesheet" type="text/css" />
<!--Este es el Javascript del calendario 1/3-->
<script type="text/javascript" src="../calendar/calendar.js"></script>
<script type="text/javascript" src="../calendar/calendar-es.js"></script>
<script type="text/javascript" src="../calendar/calendar-setup.js"></script>
<link href="../calendar/calendar.css" rel="stylesheet" type="text/css" />
<script language="Javascript" type="text/javascript">
<!--Este es el Javascript abre POP UP para llenar campos-->
function pop_up_1() 
	{
	vWinCal = window.open("documento_envia.php?doc=<?php echo $row_documentos['documentos_id']; ?>"  , "Busqueda", "width=500,height=300,status=yes,resizable=NO,top=200,left=200");
	vWinCal.opener = self;
	ggWinCal = vWinCal.window.focus();
	}
function pop_up_2() 
	{
	vWinCal = window.open("../informacion/mail_1.php?id=<?php echo $row_documentos['documentos_id']; ?>"  , "Busqueda", "width=600,height=300,status=yes,resizable=NO,top=200,left=200");
	vWinCal.opener = self;
	ggWinCal = vWinCal.window.focus();
	}
function pop_up_3() 
	{
	vWinCal = window.open("../seleccion/facturacion_pre.php?id=<?php echo $row_documentos['documentos_id']; ?>&n_c=<?php echo $row_documentos['documentos_caso']; ?>"  , "Busqueda", "width=600,height=300,status=yes,resizable=NO,top=200,left=200");
	vWinCal.opener = self;
	ggWinCal = vWinCal.window.focus();
	}		
<!--Este es el Javascript abre POP UP para llenar campos-->
function llama_doc() 
	{
	vWinCal = window.open("../formatos/<?php echo $row_documentos['ruta']; ?>?caso=<?php echo $row_documentos['documentos_caso']; ?>&doc=<?php echo $row_documentos['documentos_id']; ?>" , "Busqueda", "width=550,height=350,status=yes,resizable=NO,top=200,left=200");
	vWinCal.opener = self;
	ggWinCal = vWinCal.window.focus();
	}

function llama_for() 
	{
	vWinCal = window.open("../mantenedor/control_formato/<?php echo $row_documentos['ruta_formato']; ?>" , "Busqueda", "width=550,height=350,status=yes,resizable=NO,top=200,left=200");
	vWinCal.opener = self;
	ggWinCal = vWinCal.window.focus();
	}
	
<!--Este es el Javascript valida los campos del formulario-->
function formCheck(form1) {  
if (form1.documentos_estado.value != "1" && form1.documentos_estado.value != "6") {
if (form1.documentos_ruta_2.value == "<?php echo $sale; ?>")
{alert("Para cambiar Estado debe estar cargado el Documento");return false;} }
if (form1.documentos_estado.value == "3") {
if (form1.documentos_chat_observacion.value == "")
{alert("Ingrese Comentario en Gestión");return false;} }
<?php if ($moneda==isnull) { ?>
{alert("Debe ingresar moneda para continuar");return false;}
<?php } ?>
if (form1.documentos_estado.value == "6") {
if (form1.documentos_chat_observacion.value == "")
{alert("Ingrese motivos para Anular Documento en Gestión");return false;} }
<?php if(($grupo_2==3 or $grupo_2==5 or $grupo_2==6 or $control==3) and $extension_doc_2=="doc") { ?>
if (form1.documentos_estado.value == "4") 
{alert("Para Enviar a Facturación debe estar cargar archivo en formato PDF o EXCEL");return false;} 
if (form1.documentos_estado.value == "5") 
{alert("Para Finalizar Documento debe estar cargar archivo en formato PDF o EXCEL");return false;} 
<?php } ?>
<?php 
if($row_documentos['division_1']==3 and $cobe_mensaje!="" and $row_documentos['caso_causa_rechazo']=="" and $row_documentos['caso_estado_procesal']=="2") { ?>
if (form1.documentos_estado.value == "7") 
{alert("Para enviar a Jefe de Área debe seleccionar Causa de Rechazo");return false;} <?php } ?>
<?php
//De liquidación a facturación y que si la perdida a indemnizar sea 0, obligue al liquidador a incorporar el motivo de rechazo  
if($grupo_2==5 and $indemnizacion<=0 and $row_documentos['caso_causa_rechazo']=="" and $row_documentos['division_1']!=3) { ?>
if (form1.documentos_estado.value == "4") {
if (form1.caso_causa_rechazo.value == "") 
{alert("Indemnización  es 0, para enviar a Facturación debe seleccionar Causa de Rechazo");return false;} }<?php } ?>
<?php 
if($grupo_2==5 and $reclamo<=0 and $row_documentos['division_1']==3) { ?>
if (form1.documentos_estado.value == "4") {
if (form1.caso_causa_rechazo.value == "") 
{alert("Indemnización  es 0, para enviar a Facturación debe seleccionar Causa de Rechazo");return false;} }<?php } ?>	
<?php
//De liquidación a facturación y que si no se ingresado ramo fecu, obligue al liquidador a incorporar el ramo fecu 
if($grupo_2==5 and $ramo_fecu=="" or $ramo_fecu=="Sin información") { ?>
if (form1.documentos_estado.value == "4") {
{alert("Debe ingresar ramo fecu para facturacion");return false;} }<?php } ?>

<?php
//De liquidación a finalización y que si no se ingresado ramo fecu, obligue al liquidador a incorporar el ramo fecu 
if($grupo_2==5 and $ramo_fecu=="" or $ramo_fecu=="Sin información") { ?>
if (form1.documentos_estado.value == "5") {
{alert("Debe ingresar ramo fecu para finalizar");return false;} }<?php } ?>
if (document.form.submit.action != "") {
document.form.submit.disabled=1;} 
}

	
</script>
</head>

<body>
<!--Este es el DIV necesario para el Javascript del calendario 2/3-->
<div id="calendar-container" style="float: right"></div>
<table width="1200" border="0" cellpadding="0" cellspacing="0" class="centrar">
  <tr>
    <td><?php include("../menus/cabeza.php"); ?></td>
  </tr>
  <tr>
    <td valign="top">
    
    <table width="100%" border="0" cellspacing="5" cellpadding="0">
      <tr>
        <td class="fondo_3" width="150"><div id="invertido"><a href="datos.php?caso=<?php echo dato_envia($filt_caso); ?>">&bull; Volver</a></div></td>
        <td class="fondo_3">Modificar Documento <?php //echo $cobe_mensaje; ?></td>
      </tr>
      </table>     
   <br />  
<form id="form1" name="form1" method="post" action="<?php echo $editFormAction; ?>" OnSubmit="return formCheck(this)" enctype="multipart/form-data">
<table width="95%" border="0" align="center" cellpadding="2" cellspacing="0">
      <tr>
        <td width="20%" align="left" height="25">Documento</td>
        <td width="30%"><?php echo $row_documentos['tipo_document']; ?></td>
        <td width="25%">Nº de Caso</td>
        <td width="25%"><?php echo $row_documentos['documentos_caso']; ?></td>
      </tr>
      <tr>
        <td align="left" height="25">Formato</td>
        <td><?php echo $row_documentos['nombre_formato']; ?></td>
        <td>Nº de Documento</td>
        <td><?php echo $row_documentos['documentos_id']; ?></td>
      </tr>
      <?php if($row_documentos['division_1']==6 and $row_documentos['documento_grupo']==1) { ?>
      <tr>
        <td  align="left" height="25">&nbsp;</td>
        <td>&nbsp;</td>
        <td>Nº de Acta</td>
        <td>
          <input name="documentos_n_acta" class="text_font_1" type="text" id="documentos_n_acta" value="<?php echo $row_documentos['documentos_n_acta']; ?>" /></td>
      </tr>
      <?php } ?>
      <tr>
        <td  align="left" height="25">Fecha Creación</td>
        <td><?php if($row_documentos['documentos_fecha']!="") { echo fech_muestra_corta($row_documentos['documentos_fecha']); } ?></td>
        <td>Archivo</td>
        <td><div id="saltos"><?php if($row_documentos['documentos_ruta']=="") { echo "Sin Archivo";} else { ?> 
          <a href="<?php echo $row_documentos['documentos_ruta']; ?>" target="_blank">Ver Archivo</a><?php } ?></div></td>
      </tr>
      <tr>
        <td align="left">Comentario</td>
        <td colspan="2"><textarea name="documentos_observaciones" cols="60" rows="3" class="text_font_1"><?php echo $row_documentos['documentos_observaciones']; ?></textarea></td>
        <td><div id="saltos"><?php if($row_documentos['documentos_ruta']=="") { echo "Sin Archivo";} else { 
		if($row_documentos['formato_text']=="carga") { ?>
        <a href="documento_envia_e.php?doc=<?php echo $filt_doc; ?>" onmouseover="window.status='Seleccione'; return true;" onmouseout="window.status='';return true;">Enviar Documento por E-Mail</a>
        <?php } else { ?>
          <a href="javascript:pop_up_1();" onmouseover="window.status='Seleccione'; return true;" onmouseout="window.status='';return true;">Enviar Documento por E-Mail</a><?php } } ?></div></td>
      </tr>
      <tr>
        <td align="left">Subir Archivo</td>
        <td colspan="2"><input name="up_documento" type="file" id="up_documento" size="50" /><br /> 
		<?php if($muestra_error != "sigue") { echo $muestra_error; } ?></td>
        <td><div id="saltos"><?php if($row_documentos['documentos_ruta']=="") { echo "Sin Archivo";} else { ?>
          <a href="javascript:pop_up_2();" onmouseover="window.status='Seleccione'; return true;" onmouseout="window.status='';return true;">Ver Envío de E-Mail</a><?php } ?></div></td>
      </tr>
      <tr>
        <td align="left">Estado de Doc.</td>
        <td colspan="3">
          <select class="text_font_1" name="documentos_estado" id="documentos_estado">
<?php do { ?>
            <option value="<?php echo $row_estado['estado_documentos_id']?>"<?php if ($row_estado['estado_documentos_id']==$row_documentos['documentos_estado']) {echo "selected=\"selected\"";} ?>><?php echo $row_estado['estado_documentos_nombre']?></option>
            <?php
} while ($row_estado = mysql_fetch_assoc($estado));
  $rows = mysql_num_rows($estado);
  if($rows > 0) {
      mysql_data_seek($estado, 0);
	  $row_estado = mysql_fetch_assoc($estado);
  }
?>
          </select> <?php if($row_documentos['tipo_document']=="Informe Final") { ?>
        &nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;&nbsp;  <a href="javascript:pop_up_3();" onmouseover="window.status='Seleccione'; return true;" onmouseout="window.status='';return true;"><strong>Administrar Facturación</strong></a>
        <?php } ?></td>
      </tr>
      <tr>
        <td align="left">Gestiones</td>
        <td colspan="3"><div id="chat"><?php include("../informacion/chat.php") ?></div></td>
      </tr>
      <tr>
        <td align="left">Gesti&oacute;n</td>
        <td colspan="2" align="left"><textarea name="documentos_chat_observacion" cols="60" rows="4" class="text_font_1"></textarea></td>
        <td align="left">&nbsp;</td>
      </tr>
      <tr>
        <td align="left">Fecha Finalizado</td>
        <td colspan="3" align="left"><input name="documentos_fecha_envio" type="text" class="text_font_1" id="documentos_fecha_envio" value="<?php if($row_documentos['documentos_fecha_envio']!="") { echo fech_muestra($row_documentos['documentos_fecha_envio']); } ?>" size="10" readonly="readonly" /></td>
      </tr>
      <tr>
        <td align="left"><?php if($grupo_2==5 and $indemnizacion<=0 and $row_documentos['caso_causa_rechazo']=="") { ?>
        Causa de Rechazo<?php } ?>&nbsp;</td>
        <td colspan="3" align="left"><?php if($grupo_2==5 and $indemnizacion<=0 and $row_documentos['caso_causa_rechazo']=="") { ?>
        <select class="text_font_1" name="caso_causa_rechazo" id="caso_causa_rechazo">
          <option value="" <?php if (""==$row_documentos['caso_causa_rechazo']) {echo "selected=\"selected\"";} ?>>Ninguna</option>
          <?php
do {  
?>
          <option value="<?php echo $row_causa_rechazo['causas_rech_id']?>" <?php if ($row_causa_rechazo['causas_rech_id']==$row_casos['caso_causa_rechazo']) {echo "selected=\"selected\"";} ?>><?php echo $row_causa_rechazo['causas_rech_nombre']?></option>
          <?php
} while ($row_causa_rechazo = mysql_fetch_assoc($causa_rechazo));
  $rows = mysql_num_rows($causa_rechazo);
  if($rows > 0) {
      mysql_data_seek($causa_rechazo, 0);
	  $row_causa_rechazo = mysql_fetch_assoc($causa_rechazo);
  }
?>
        </select><?php } ?></td>
      </tr>
      <tr>
        <td align="left">&nbsp;</td>
        <td colspan="3" align="left">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3" align="center" scope="row"> 
        <?php if($row_documentos['ruta_formato']!="") { ?><input type="submit" class="boton_1" value="Ver Formato" onclick="llama_for()" /><?php } else { ?>        
        <?php if($row_documentos['formatt']=="pdf") { ?><input type="submit" class="boton_1" value="Generar PDF" onclick="llama_doc()" /><?php } ?>
        <?php if($row_documentos['formatt']=="word") { ?><input type="button" class="boton_1" value="Generar Word" onclick="llama_doc()" /><?php } } ?></td>
        <td align="center" scope="row"><input type="submit" class="boton_1" value="Guardar Cambios" />
		</td>
        </tr>
    </table> 
<br />
<?php if($row_documentos['formatt']=="pdf") { 
if($row_documentos['documentos_ruta']=="") {
$ruta_pdf="../documentos/".$row_documentos['documentos_caso']."/".$row_documentos['documentos_caso']."_documet_".$row_documentos['documentos_id'].".pdf"; } else { 
$ruta_pdf=substr($row_documentos['docu'], 0, -3);
$ruta_pdf=$ruta_pdf."pdf"; }
?>
<input type="hidden" name="documentos_ruta_2" value="<?php echo $ruta_pdf; ?>" />
<input type="hidden" name="caso_rc" value="<?php echo $filt_caso ?>" />
<?php } else { ?>
<input type="hidden" name="documentos_ruta_2" value="<?php echo $row_documentos['documentos_ruta']; ?>" /> <?php } ?>
<input type="hidden" name="documentos_chat_user" value="<?php echo $login_id; ?>" />
<input type="hidden" name="documentos_caso" value="<?php echo $row_documentos['documentos_caso']; ?>" />
<input type="hidden" name="filt_doc" value="<?php echo $row_documentos['documentos_id']; ?>" />
<input type="hidden" name="documento_grupo" value="<?php echo $row_documentos['documento_grupo']; ?>" />
<input type="hidden" name="fecha_liquida_caso" value="<?php echo $row_documentos['fech_liquidacion']; ?>" />
<input type="hidden" name="documentos_pre_estado" value="<?php echo $row_documentos['documentos_estado']; ?>" />
<input type="hidden" name="estado_real" value="<?php echo $row_documentos['estado_real']; ?>" />
<input type="hidden" name="ajustador_1" value="<?php echo $row_documentos['ajustador_1']; ?>" />
<input type="hidden" name="cia_1" value="<?php echo $row_documentos['cia_1']; ?>" />
<input type="hidden" name="control_factura" value="<?php echo $control; ?>" />
<input type="hidden" name="estado_caso" value="<?php echo $row_documentos['estado_caso']; ?>" />
<input type="hidden" name="MM_update" value="form1" />
</form>
</td>
  </tr>
</table>
</body>
<!--Este es el Javascript del calendario final 3/3-->
<script type="text/javascript">
function dateChanged(calendar) {
    if (calendar.dateClicked) {
      var y = calendar.date.getFullYear();
      var m = calendar.date.getMonth();
      var d = calendar.date.getDate();
      window.location = "#";
    }
  };

  Calendar.setup(
    {
      inputField  : "documentos_fecha_envio",
      ifFormat    : "%d/%m/%Y",
      button      : "documentos_fecha_envio"
    }
  );
</script>
</html>
<?php
mysql_free_result($documentos);

mysql_free_result($estado);

mysql_free_result($datos_caso);

mysql_free_result($causa_rechazo);
?>
