<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Content-Type: application/json');
require_once("SmsRestHandler.php");
include_once($_SERVER['DOCUMENT_ROOT']."/clases/generales/Clog.php");
		
/*$method = $_SERVER['REQUEST_METHOD'];

$objResponse = new stdClass();
$objResponse->metodo = "esto es un metodo ".$method;
print_r(json_encode($objResponse));*/

$sms = json_decode(file_get_contents("php://input"));

$objLog = new Clog("SmsMasivo");
//$objLog->escribirLog("idmsg : ".$sms->idmsg );
//$objLog->escribirLog("Entro: ".$sms->remitente." texto: ".$sms->msg." Numero: ".$sms->destinatarios[0] );



$SmsRestHandler = new SmsRestHandler();

$SmsRestHandler->SendSms($sms);

?>