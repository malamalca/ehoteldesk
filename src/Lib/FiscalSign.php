<?php
declare(strict_types=1);

namespace App\Lib;

use DOMDocument;
use DOMXPath;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * FiscalSign.php
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

class FiscalSign
{
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
    private $idPropertyName = 'Id';

    /**
     * @var string
     */
    private $idPropertyValue = 'data';

    /**
     * @var string
     */
    public $lastError = '';

    /**
     * @param array $options Config options
     */
    public function __construct($options = [])
    {
    }

    /**
     * @param string $fileName Clients key in .p12|.pfx store.
     * @return \App\Lib\FiscalSign
     */
    public function setP12File($fileName)
    {
        $this->p12 = file_get_contents($fileName);

        return $this;
    }

    /**
     * @param string $p12 Clients key in .p12|.pfx store.
     * @return \App\Lib\FiscalSign
     */
    public function setP12($p12)
    {
        $this->p12 = $p12;

        return $this;
    }

    /**
     * @param string $password Client's private key password.
     * @return \App\Lib\FiscalSign
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param string $xml Echo message
     * @param string $signingNode Node to sign eg fu:InvoiceRequest
     * @return bool|string
     */
    public function sign($xml, $signingNode)
    {
        $ret = false;

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $xpath = new DOMXPath($doc);
        $nodeset = $xpath->query('//' . $signingNode)->item(0);
        if (!empty($nodeset)) {
            $objXMLSecDSig = new XMLSecurityDSig('');
            $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
            $objXMLSecDSig->addReference(
                $nodeset,
                XMLSecurityDSig::SHA256,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
                ['id_name' => $this->idPropertyName, 'uri' => $this->idPropertyValue, 'overwrite' => false]
            );

            if (openssl_pkcs12_read($this->p12, $raw, $this->password)) {
                $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
                $objKey->loadKey($raw['pkey']);

                $objXMLSecDSig->sign($objKey, $nodeset);
                $objXMLSecDSig->add509Cert(
                    $raw['cert'],
                    true,
                    false,
                    ['issuerSerial' => true, 'subjectName' => true, 'issuerCertificate' => false]
                );

                $ret = $doc->saveXML();
            } else {
                $this->lastError = 'Cannot parse certificate.';
            }
        }

        return $ret;
    }

    /**
     * @param string $data Zoi string to be signed
     * @return bool|string
     */
    public function zoi($data)
    {
        $ret = false;

        $tmpPemFile = FiscalUtils::p12ToPem($this->p12, $this->password);
        if (!empty($tmpPemFile)) {
            $key = openssl_pkey_get_private('file://' . realpath($tmpPemFile), $this->password);
            if (!empty($key)) {
                openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256);
                openssl_free_key($key);
                $ret = md5($signature);
            }
        }

        return $ret;
    }
}
