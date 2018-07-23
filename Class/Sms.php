<?php
	ini_set('memory_limit', '128M');
	require_once ('./vendor/autoload.php');
    use infobip\api\client\SendMultipleTextualSmsAdvanced;
    use infobip\api\configuration\BasicAuthConfiguration;
    use infobip\api\model\Destination;
    use infobip\api\model\sms\mt\send\Message;
    use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;
    //include_once($_SERVER['DOCUMENT_ROOT']."/clases/generales/Clog.php");

	Class Sms {

		

		public function Send($sms)
	    {
	    
	    	//$detalleSms = $this->getSmsDetails($sms);
	 
	    	//$objLog = new Clog("SmsMasivo");
	    	//$objLog->escribirLog("detinatarios : ".$detalleSms['cDestinatarios'] );

	    	// Create configuration object that will tell the client how to authenticate API requests
		    // Additionally, note the use of http protocol in base path.
		    // That is for tutorial purposes only and should not be done in production.
		    // For production you can leave the baseUrl out and rely on the https based default value.
		    $xml = simplexml_load_file("./configuracion/config.xml") or die("No se puede leer el archivo de configuracion");
		    $configuration = new BasicAuthConfiguration(trim($xml->usuario), trim($xml->password), 'http://api.infobip.com/');
		    // Create a client for sending sms texts by passing it the configuration object
		    $client = new SendMultipleTextualSmsAdvanced($configuration);

		    // Destination holds recipient's phone number along with id used to uniquely identify the message later on
		    

		    //$arrDestinos = explode(',', $detalleSms['cDestinatarios']);

		    $destinations = array();

		    foreach ($sms->destinatarios as $key => $numero) {
		    	
		    	$destination = new Destination();
		    	$destination->setTo($numero);
		    	$destinations[] = $destination;
		    	//$objLog->escribirLog("detinatario ".$key.": ".$numero );
		    }

		    
		    $caracEspeciales = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
			
			
			//$msg = strtr( $detalleSms['cTexto'] , $caracEspeciales );
			$msg = strtr( $sms->msg , $caracEspeciales );

			try {

				if (strlen($msg) > 150) {					
					throw new Exception('supero el limite de caracteres');					
				}
				
			} catch (Exception $e) {
				throw $e;
			}


		    // Message has text and the sender of the sms along with other metadata useful for tracking delivery
		    $message = new Message();


		    $message->setDestinations($destinations);
		    $message->setFrom($sms->remitente);
		    $message->setText($sms->msg);
			if (trim($sms->fecha) != ''){
				$fechaEnvio = DateTime::createFromFormat('Y-m-d\TH:i:s.u-07:00', trim($detalleSms['cProgramado']));
				//$objLog->escribirLog("fecha ".trim($detalleSms['cProgramado']) );
				$message->setSendAt($fechaEnvio);
			}


		    // SMSAdvancedTextualRequest model is sent to the API client
		    $requestBody = new SMSAdvancedTextualRequest();
		    // One request can have multiple different text messages, in this example we only send one
		    $requestBody->setMessages([$message]);

		    try {
		    	$apiResponse = $client->execute($requestBody);

			    $messages = $apiResponse->getMessages();

			    return $messages;
		    	
		    } catch (Exception $apiCallException) {
		    	
		    	throw $apiCallException;
		    }

	    }

	    private function getSmsDetails($sms){
	    	$xml = simplexml_load_file("./configuracion/config.xml") or die("No se puede leer el archivo de configuracion");		    

	    	//$objLog = new Clog("SmsMasivo");
	    	//$objLog->escribirLog("id detalles : ".$sms->idmsg );
		    $host =  $xml->ipserver;
		    $dbname = $xml->database;
		    $dbuser = $xml->userdb;
		    $dbpwd = $xml->passwddb;
		  
		    $conexion = new PDO("dblib:host=$host;dbname=$dbname", "$dbuser","$dbpwd");
		   
		    $sentencia = $conexion->prepare("select cDestinatarios, cTexto, cProgramado from dbo.envio_sms_det where id = ?;");
		    $sentencia->bindParam(1,$sms->idmsg);


		    $sentencia->execute();

		    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
		    
		    return $resultado;
	    }
	
	}
?>