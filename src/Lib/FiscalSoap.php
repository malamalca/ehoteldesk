<?php
declare(strict_types=1);

namespace App\Lib;

use Exception;

/**
 * FiscalSoap.php
 *
 * Copyright (c) 2015-2016, Miha Nahtigal <miha@malamalca.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Miha Nahtigal nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Miha Nahtigal <miha@malamalca.com>
 * @copyright 2015-2016 Miha Nahtigal <miha@malamalca.com>
 * @license   http://www.gnu.org/licenses/lgpl.html  GNU Lesser General Public License
 */

class FiscalSoap
{
    /**
     * @var string
     */
    private $ECHO_TEMPLATE = '';

    /**
     * @var string
     */
    private $cert = '';

    /**
     * @var string
     */
    private $p12 = '';

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $url = 'https://blagajne-test.fu.gov.si:9002/v1/cash_registers';

    /**
     * @param string $options Options array
     */
    public function __construct($options = [])
    {
        $this->ECHO_TEMPLATE = '<?xml version="1.0" encoding="utf-8"?' . '>' .
            '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ' .
            'xmlns:fu="http://www.fu.gov.si/" xmlns:xd="http://www.w3.org/2000/09/xmldsig#">' .
            '<soapenv:Body>' .
            '<fu:EchoRequest>%s</fu:EchoRequest>' .
            '</soapenv:Body>' .
            '</soapenv:Envelope>';
    }

    /**
     * @param string $url Soap service url
     * @return \App\Lib\FiscalSoap
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param string $fileName Server's public key.
     * @return \App\Lib\FiscalSoap
     */
    public function setCert($fileName)
    {
        $this->cert = $fileName;

        return $this;
    }

    /**
     * @param string $fileName Clients key in .p12|.pfx store.
     * @return \App\Lib\FiscalSoap
     */
    public function setP12($fileName)
    {
        $this->p12 = $fileName;

        return $this;
    }

    /**
     * @param string $password Client's private key password.
     * @return \App\Lib\FiscalSoap
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $message Echo message
     * @return bool|string
     */
    public function sendEcho($message)
    {
        $response = $this->doRequest('echo', sprintf($this->ECHO_TEMPLATE, $message));
        if (!empty($response)) {
            if ($this->hasError($response) === false) {
                return $this->elementValue($response, 'EchoResponse');
            }
        }

        return false;
    }

    /**
     * @param string $xml Signed premise xml
     * @return string
     */
    public function sendPremiseRaw($xml)
    {
        return $this->doRequest('invoices/register', $xml);
    }

    /**
     * @param string $xml Signed premise xml
     * @return bool|string
     */
    public function sendPremise($xml)
    {
        $response = $this->sendPremiseRaw($xml);
        if (!empty($response)) {
            return $this->hasError($response) === false;
        }

        return false;
    }

    /**
     * @param string $xml Signed invoice xml
     * @return string
     */
    public function sendInvoiceRaw($xml)
    {
        return $this->doRequest('invoices', $xml);
    }

    /**
     * @param string $xml Signed invoice xml
     * @return bool|string
     */
    public function sendInvoice($xml)
    {
        $response = $this->sendInvoiceRaw($xml);
        if (!empty($response)) {
            if ($this->hasError($response) === false) {
                return $this->elementValue($response, 'UniqueInvoiceID');
            }
        }

        return false;
    }

    /**
     * @param string $xml Signed invoice xml
     * @return bool
     */
    public function hasError($xml)
    {
        return strpos($xml, 'Error>') !== false;
    }

    /**
     * @param string $xml Response XML
     * @param string $elementName XML Element Name
     * @return bool|string
     */
    public function elementValue($xml, $elementName)
    {
        $ret = false;
        $elementPos = strpos($xml, $elementName . '>');
        if ($elementPos !== false) {
            $elementPos += strlen($elementName) + 1;
            $ret = substr($xml, $elementPos, strpos($xml, '</', $elementPos) - $elementPos);
        }

        return $ret;
    }

    /**
     * @param string $action Curl action.
     * @param string $xml XML body.
     * @return bool|string Returns response on success or false on failure.
     */
    private function doRequest($action, $xml)
    {
        $privateKey = FiscalUtils::p12ToPem($this->p12, $this->password);
        if (empty($privateKey)) {
            throw new Exception('ERROR: Cannot parse P12');
        }

        $ca = FiscalUtils::cerToPem($this->cert, $this->password);
        if (empty($ca)) {
            throw new Exception('ERROR: Cannot parse CA Info');
        }

        $header = [
            'Content-Type: text/xml; charset=utf-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: /' . $action,
        ];
        $conn = curl_init();
        $settings = [
            CURLOPT_URL => $this->url,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_CONNECTTIMEOUT_MS => 3000,
            CURLOPT_TIMEOUT_MS => 3000,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSLCERT => $privateKey,
            CURLOPT_SSLCERTPASSWD => $this->password,
            CURLOPT_CAINFO => $ca,
        ];
        curl_setopt_array($conn, $settings);

        $ret = false;
        $rawResponse = curl_exec($conn);
        if (!empty($rawResponse)) {
            $ret = $rawResponse;
        } else {
            throw new Exception('CODECURL: ' . curl_error($conn));
        }

        // cleanup temp files
        unlink($privateKey);
        unlink($ca);

        return $ret;
    }
}
