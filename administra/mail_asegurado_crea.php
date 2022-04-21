<?php 
require '../class/PHPMailer/class.phpmailer.php';
require '../class/PHPMailer/class.smtp.php'; //incluimos la clase para envíos por SMTP

$mail_envia="info@aplusajustadores.cl";
$pass_envia="aPlus367info";
$nombre_envia="Sistema A Plus";

if($_POST['liquidador_mail']!="" and $_POST['liquidador_pass']!="") {
$mail_envia=$_POST['liquidador_mail'];
$pass_envia=$_POST['liquidador_pass'];
$nombre_envia=$_POST['liquidador_nombre'];	
$email_contacto=$_POST['contactos_mail'];}
$casos=$_POST['edita_dato'];
//$email_contacto=$_POST['correo_contacto'];
$num_siniestro=$_POST['num_siniestro'];
$email_asegurado=$_POST['asegurado_mail'];
// $email_contacto1=$_POST['contactos_mail'];
$mail_1=$_POST['mail1'];
$mail_2=$_POST['mail2'];
$mail_3=$_POST['mail3'];
$mail_4=$_POST['mail4'];
$mail_5=$_POST['mail5'];

$mail             = new PHPMailer();



$mail->IsSMTP(); // telling the class to use SMTP

$mail->CharSet = 'UTF-8';

$mail->Host       = "mail.aplusajustadores.cl"; // SMTP server

//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)

                                           // 1 = errors and messages

                                           // 2 = messages only

$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "";                 // establecemos el prefijo del protocolo seguro de comunicación con el servidor
$mail->Host       = "mail.aplusajustadores.cl"; // sets the SMTP server
$mail->Port       = 587;                    // set the SMTP port for the GMAIL server
$mail->Username   = $mail_envia; // SMTP account username
$mail->Password   = $pass_envia;        // SMTP account password



$mail->SetFrom($mail_envia,$nombre_envia);
$mail->AddReplyTo($mail_envia); 
$mail->Subject    = "Creación / Caso N° ".$casos." / N° Siniestro ".$num_siniestro.

//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML(file_get_contents('contents_crea.html'));//Cuerpo del Mensaje en HTML

$mail->AddAddress($email_asegurado);//destinatario 
$mail->AddAddress($email_contacto);//destinatario 
//$mail->AddAddress($email_contacto);//destinatario que va a recibir el correo
$mail->AddCC($mail_envia);//destinatario que va a recibir copia de correo
$mail->AddBCC("ivan.ormeno@aplusajustadores.cl");//destinatario que va a recibir el correo
$mail->AddBCC("cristopher.videla@aplusajustadores.cl");//destinatario que va a recibir el correo
$mail->AddBCC("asistenteoperaciones@aplusajustadores.cl");//destinatario que va a recibir el correo
//$mail_1=$mail_envia;

if($division_mail=="6") { 
   $mail->AddCC("gabriel.cespedes@aplusajustadores.cl");//destinatario que va a recibir copia de correo
}

if($mail_1!="") { 

$mail->AddAddress($mail_1);//destinatario que va a recibir el correo

 }

 
if($mail_2!="") { 

$mail->AddAddress($mail_2);//destinatario que va a recibir el correo

 }

 
if($mail_3!="") { 

$mail->AddAddress($mail_3);//destinatario que va a recibir el correo

 }

 
if($mail_4!="") { 

$mail->AddAddress($mail_4);//destinatario que va a recibir el correo

 }

 if($mail_5!="") { 

   $mail->AddAddress($mail_5);//destinatario que va a recibir el correo
   
    }
 

//$mail->AddAttachment($_POST['ruta']);      // attachment

//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment



$mail->Send()





?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style type="text/css">
body {
	background-color: #FFF;
}
</style>
<script type="text/javascript">

<!--Envia formulario->
function editar()
{
   document.edita_form.submit()
}
</script>
</head>

<body onload="editar()">
<form method="post" name="edita_form" id="edita_form" action="datos_edita.php">
<input name="edita_dato" type="hidden" id="edita_dato" value="<?php echo $casos; ?>" />
</form>
</body>
</html>