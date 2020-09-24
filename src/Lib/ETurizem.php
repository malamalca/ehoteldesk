<?php
namespace App\Lib;

use App\Lib\FiscalUtils;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use DomDocument;
use Exception;

class ETurizem
{
    const ENVELOPE = '<' . '?xml version="1.0" encoding="UTF-8"?>' .
        '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.ajpes.si/eturizem/">' .
        '<SOAP-ENV:Body>' .
        '<ns1:oddajPorocilo>' .
        '<ns1:uName>%1$s</ns1:uName>' .
        '<ns1:pwd>%2$s</ns1:pwd>' .
        '<ns1:data>%3$s</ns1:data>' .
        '<ns1:format>1</ns1:format>' .
        '</ns1:oddajPorocilo>' .
        '</SOAP-ENV:Body>' .
        '</SOAP-ENV:Envelope>';

    const SUCCESS_GB = 1;

    const ERROR_GB_SCHEMA = -10;
    const ERROR_GB_SOAP = -20;

    private $lastErrorMessage = '';
    private $xml = '';

    // apiTest:Test123!

    /**
     * @param string $xml XML body.
     * @param string $uName ETruizem username.
     * @param string $uPwd ETruizem password.
     * @param string $p12 P12 storage with private key.
     * @param string $password Password for private key.
     * @return bool
     */
    public function send($xml, $uName, $uPwd, $p12, $password)
    {
        if (!$privateKey = FiscalUtils::p12StringToPem($p12, $password)) {
            $this->lastErrorMessage = 'Cannot parse P12';

            return false;
        }

        $url = Configure::read('Eturizem.wsUrl');

        $envelopedXml = sprintf(self::ENVELOPE, $uName, $uPwd, $xml);

        $header = [
                "Content-Type: text/xml; charset=utf-8",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: http://www.ajpes.si/eturizem/oddajPorocilo"
        ];

        $conn = curl_init();
        $settings = [
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_CONNECTTIMEOUT_MS => 3000,
            CURLOPT_TIMEOUT_MS => 3000,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $envelopedXml,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSLCERT => $privateKey,
            CURLOPT_SSLCERTPASSWD => $password,
            //CURLOPT_CAINFO => $ca
        ];

        curl_setopt_array($conn, $settings);

        $ret = false;
        if ($rawResponse = curl_exec($conn)) {
            $httpStatus = curl_getinfo($conn, CURLINFO_HTTP_CODE);
            if ($httpStatus == 200) {
                $responseDoc = new DOMDocument();
                if ($responseDoc->loadXML($rawResponse)) {
                    $responseNode = $responseDoc->getElementsByTagName('oddajPorociloResult')->item(0);
                    if ($dataNodeText = $responseNode->nodeValue) {
                        $dataDoc = new DOMDocument();
                        if ($dataDoc->loadXML(html_entity_decode($dataNodeText))) {
                            $failures = $dataDoc->documentElement->getAttribute('failure');

                            if ($failures > 0) {
                                foreach ($dataDoc->documentElement->childNodes as $node) {
                                    $message .= __('Line {0}: {1}', $node->getAttribute('rowPackageId'), $node->getAttribute('msgTxt')) . PHP_EOL;
                                }
                            } else {
                                $ret = [
                                    'guid' => $dataDoc->documentElement->getAttribute('packageGuid'),
                                    'time' => $dataDoc->documentElement->getAttribute('time'),
                                    'raw' => $rawResponse
                                ];
                            }
                        }
                    }
                }
            } else {
                $message = 'HttpStatus ' . $httpStatus;
            }
            if (!$ret) {
                if ($message) {
                    $this->lastErrorMessage = $message;
                } else {
                    $this->lastErrorMessage = __('Cannot parse response XML');
                }
            }
        } else {
            $this->lastErrorMessage = curl_error($conn);
        }

        // cleanup temp files
        unlink($privateKey);

        return $ret;
    }

    /**
     * @return string Last error message.
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @param int $status Status id.
     * @return string Status message
     */
    public static function getStatusDescription($status)
    {
        $statusMessages = [
            self::ERROR_GB_SCHEMA => __('XSD Validation Failed'),
            self::ERROR_GB_SOAP => __('Soap request Failed'),
            self::SUCCESS_GB => __('Data has been successfully sent!'),
        ];

        if (isset($statusMessages[$status])) {
            return $statusMessages[$status];
        } else {
            return '';
        }
    }

    /**
     * Logs ETurizem to table.
     *
     * @return uuid
     */
    public function log($companyId, $status, $xml, $message)
    {
        $EturizemLogsTable = TableRegistry::get('EturizemLogs');
        $log = $EturizemLogsTable->newEmptyEntity();
        $log->company_id = $companyId;
        $log->status = $status;
        $log->xml = $xml;
        $log->message = $message;

        return $EturizemLogsTable->save($log);
    }

    /**
     * @param string $xml XML body.
     * @return bool
     */
    public function validateGuestBookSchema($xml)
    {
        $this->lastErrorMessage = '';

        return $this->validateToSchema($xml, WWW_ROOT . 'GuestBookSchema.xml');
    }

    /**
     * @param string $xml XML body.
     * @return bool
     */
    public function validateMonthlyReportSchema($xml)
    {
        $this->lastErrorMessage = '';

        return $this->validateToSchema($xml, WWW_ROOT . 'GuestBookMRschema.xml');
    }

    /**
     * @param string $xml XML body.
     * @param string $schemaFilename XSD Schema Filename.
     * @return bool
     */
    private function validateToSchema($xml, $schemaFilename)
    {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        if (!$doc->schemaValidate($schemaFilename)) {
            $this->lastErrorMessage = $this->libxmlDisplayErrors();
            libxml_use_internal_errors(false);

            return false;
        }
        libxml_use_internal_errors(false);

        return true;
    }
    /**
     * @param \libXMLError $error Error object.
     * @return string Formatted error message for single error object.
     */
    private function libxmlDisplayError($error)
    {
        $return = '';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= __("Warning {0}: ", $error->code);
                break;
            case LIBXML_ERR_ERROR:
                $return .= __("Error {0}: ", $error->code);
                break;
            case LIBXML_ERR_FATAL:
                $return .= __("Fatal Error {0}: ", $error->code);
                break;
        }
        $return .= trim($error->message);
        $return .= " on line $error->line" . PHP_EOL;

        return $return;
    }

    /**
     * @return string String with all errors.
     */
    private function libxmlDisplayErrors()
    {
        $ret = '';
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $ret .= $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();

        return $ret;
    }
}
