<?php
    require_once("Class/SimpleRest.php");
    require_once("Class/Sms.php");
    //include_once($_SERVER['DOCUMENT_ROOT']."/clases/generales/Clog.php");
            
    class SmsRestHandler extends SimpleRest {

        function SendSms($smsData) {  

            $sms = new Sms();

            try {
                $response = $sms->Send($smsData);
                
                $statusCode = 200;
                $rawData = array();
                //$objLog = new Clog("SmsMasivo");
                foreach ($response as $message) {
                    
                    //$objLog->escribirLog("MessageId : ".$message->getMessageId());
                    $rawData['MessageId'] = $message->getMessageId();
                    $rawData['SmsCount'] = $message->getSmsCount();
                    $rawData['BulkId'] = $message->getStatus()->getId();
                    $rawData['To'] = $message->getTo();
                    $rawData['GroupId'] = $message->getStatus()->getGroupId();
                    $rawData['GroupName'] = $message->getStatus()->getGroupName() ;
                    $rawData['Id'] = $message->getStatus()->getId();
                    $rawData['Name'] = $message->getStatus()->getName();
                    $rawData['Description'] = $message->getStatus()->getDescription();
                }
            } catch (Exception $apiCallException) {
                $statusCode = 500;
                
                $errorMessage = $apiCallException->getMessage();
                $errorMessage .= $xml->usuario;
                $errorResponse = json_decode($apiCallException->getMessage());
                if (json_last_error() == JSON_ERROR_NONE) {
                    $errorReason = $errorResponse->requestError->serviceException->text;
                } else {
                    $errorReason = $errorMessage;
                }
                
                $rawData = array('error' => $errorReason);
            }


            $requestContentType = $_SERVER['HTTP_ACCEPT'];
            //$objLog->escribirLog("requestcontentype : ".$_SERVER['HTTP_ACCEPT']);
            $this ->setHttpHeaders($requestContentType, $statusCode);
            
                    
            if(strpos($requestContentType,'application/json') !== false){
                //$objLog->escribirLog("contentType : ".'application/json');
                $smsResponse = $this->encodeJson($rawData);
                echo $smsResponse;
            } else if(strpos($requestContentType,'text/html') !== false){
                //$objLog->escribirLog("contentType : ".'text/html');
                $smsResponse = $this->encodeHtml($rawData);
                echo $smsResponse;
            } else if(strpos($requestContentType,'application/xml') !== false){
                //$objLog->escribirLog("contentType : ".'application/xml');
                $smsResponse = $this->encodeXml($rawData);
                echo $smsResponse;
            }else{
                //$objLog->escribirLog("contentType : ".'application/json');
                $smsResponse = $this->encodeJson($rawData);
                echo $smsResponse;
            }
        }
        
        public function encodeHtml($responseData) {
        
            $htmlResponse = "<table border='1'>";
            foreach($responseData as $key=>$value) {
                    $htmlResponse .= "<tr><td>". $key. "</td><td>". $value. "</td></tr>";
            }
            $htmlResponse .= "</table>";
            return $htmlResponse;       
        }
        
        public function encodeJson($responseData) {
            $jsonResponse = json_encode($responseData);
            return $jsonResponse;       
        }
        
        public function encodeXml($responseData) {
            // creating object of SimpleXMLElement
            $xml = new SimpleXMLElement('<?xml version="1.0"?><mobile></mobile>');
            foreach($responseData as $key=>$value) {
                $xml->addChild($key, $value);
            }
            return $xml->asXML();
        }
        
    }
?>