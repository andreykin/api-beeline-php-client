<?php
header("Content-type: text/xml;charset=UTF-8");
//var_dump($_POST);
//var_dump($_GET);
extract($_GET);
extract($_POST);

// test 401
if ((!$user || !$pass) || ($user != 'demo' || $pass != 'demo')) {
    $xml = new SimpleXMLExtended("401.xml", 0, true);
    echo $xml->asXML();
} else {
    $xml = new SimpleXMLExtended("output.xml", 0, true);

    $requests = [];

    if ($data && is_array($data)) {
        $requests = $data;
    } else {
        $requests[] = array_merge($_GET, $_POST);
    }

    //  если у нас попытка отправки сообщения...
    $allowedActions = [
        'post_sms',
        'status'
    ];

    foreach ($requests as $request) {
        extract($request);
        if (!$action || !in_array($action, $allowedActions)) {
            addRequestError($xml);
        } else {
            $result = $xml->addChild('result');
            if ($action == 'post_sms') {
                if (($target || $phl_codename) && $message) {
                    $phones = explode(',', $target);

                    $validPhones = [];
                    $inValidPhones = [];
                    foreach ($phones as $phone) {
                        if (validatePhone($phone)) {
                            $validPhones[] = $phone;
                        } else {
                            $inValidPhones[] = $phone;
                        }
                    }

                    if ($validPhones) {
                        $result->addAttribute('sms_group_id', getRandomGroupId());
                        foreach ($validPhones as $validPhone) {
                            $sms = $result->addChild('sms', '');
                            $sms->addCData($message);
                            $sms->addAttribute('id', getRandomSmsId());
                            $sms->addAttribute('smstype', 'SENDSMS');
                            $sms->addAttribute('phone', $validPhone);
                        }
                    }
                    if ($inValidPhones) {
                        $errors = $xml->addChild('errors');
                        foreach ($inValidPhones as $inValidPhone) {
                            $error = $errors->addChild('error', 'Неправильный номер телефона: ' . $inValidPhone);
                        }
                    }

                    //   <result sms_group_id="996">
                    //          <sms id="99991" smstype="SENDSMS" phone="+79999999991"><![CDATA[Привет]]></sms>
                    //          <sms id="99992" smstype="SENDSMS" phone="+79999999992"><![CDATA[Привет]]></sms>
                    //   </result>
                    //   <errors>
                    //         <error>Неправильный номер телефона: +7999999999999</error>
                    //   </errors>


                } else {
                    addRequestError($xml);
                }
            }

            if ($action == 'status') {
                if ($sms_id) {
                    if ($sms_group_id || $date_from || $date_to) {
                        addRequestError($xml);
                    } else {
                        $messages = $xml->addChild('MESSAGES');
                        addMessageStatus($messages, $sms_id);
                    }
                } elseif ($sms_group_id) {
                    if ($sms_id || $date_from || $date_to) {
                        addRequestError($xml);
                    } else {
                        $messages = $xml->addChild('MESSAGES');
                        addMessageStatus($messages, $sms_group_id);
                    }
                } else if ($date_from && $date_to) {
                    if ($sms_id || $sms_group_id) {
                        addRequestError($xml);
                    } else {
                        $messages = $xml->addChild('MESSAGES');
                        addMessageStatus($messages, '1');
                    }
                } else {
                    addRequestError($xml);
                }
            }
        }
        unset($action, $target, $message, $phl_codename, $sms_id, $sms_group_id, $date_from, $date_to);
    }
    echo $xml->asXML();
}

die();

function validatePhone($phone)
{
    $digits = (int)$phone;
    //var_dump(strlen($digits));
    if (strlen($digits) <> 11) {
        return false;
    }
    return true;
}

function getRandomGroupId()
{
    return random_int(100, 999);
}

function getRandomSmsId()
{
    return random_int(11111, 99999);
}

function addMessageStatus(SimpleXMLExtended &$xml, $sms_id, $sender = 'SenderName')
{
    $message = $xml->addChild('MESSAGE');
    $message->addAttribute('SMS_ID', $sms_id);
    $message->addAttribute('SMSTYPE', 'SENDSMS');

    $message->addChild('CREATED', '24.12.2007 15:57:45');
    $message->addChild('SMS_SUBMITTER_SUBSYSTEM', 'WEB');
    $message->addChild('AUL_USERNAME', 'userX.Y');
    $message->addChild('AUL_CLIENT_ADR', '127.0.0.1');
    $message->addChild('SMS_SENDER', $sender);
    $message->addChild('SMS_TARGET', '89999991111');
    $message->addChild('SMS_RES_COUNT', '1');
    $smsText = $message->addChild('SMS_TEXT');
    $smsText->addCdata('Привет');
    $message->addChild('SMSSTC_CODE', 'wait');
    $message->addChild('SMS_STATUS', 'Сообщение в процессе доставки');
    $message->addChild('SMS_CLOSED', '0');
    $message->addChild('SMS_SENT', '0');
}

function addRequestError(&$xml, $message = 'Invalid request')
{
    $errors = $xml->addChild('errors');
    $error = $errors->addChild('error', 'Invalid request');
    if ($message) {
        $error = $errors->addChild('error', $message);
    }
    $error->addAttribute('code', '-20200');
}

// http://coffeerings.posterous.com/php-simplexml-and-cdata
class SimpleXMLExtended extends SimpleXMLElement
{
    public function addCData($cdata_text)
    {
        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
    }
}

//// Open and parse the XML file
//$xml = simplexml_load_file("questions.xml");
//// Create a child in the first topic node
//$child = $xml->topic[0]->addChild("subtopic");
//// Add the text attribute
//$child->addAttribute("text", "geography");
//You can either display the new XML code with echo or store it in a file.
//
//// Display the new XML code
//echo $xml->asXML();
//// Store new XML code in questions.xml
//$xml->asXML("questions.xml");