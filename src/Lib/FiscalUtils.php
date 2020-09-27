<?php
declare(strict_types=1);

namespace App\Lib;

/**
 * FiscalUtils.php
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

class FiscalUtils
{
    /**
     * @param string $p12 .P12 store as fuile.
     * @param string|null $password Clients private key password.
     * @return string PEM filename in temp folder.
     */
    public static function p12ToPem($p12, $password = null)
    {
        return self::p12StringToPem(file_get_contents($p12), $password);
    }

    /**
     * @param string $p12 .P12 store as string.
     * @param string|null $password Clients private key password
     * @return string PEM filename in temp folder.
     */
    public static function p12StringToPem($p12, $password = null)
    {
        $ret = false;
        if (openssl_pkcs12_read($p12, $cert_info, $password)) {
            $ret = sys_get_temp_dir() . '/' . uniqid('cer') . '.pem';
            $data = $cert_info['pkey'] . $cert_info['cert'];
            if (isset($cert_info['extracerts'])) {
                $data .= implode('', $cert_info['extracerts']);
            }
            file_put_contents($ret, $data);
        }

        return $ret;
    }

    /**
     * @param string $cer Path to clients .cer file
     * @return bool|string
     */
    public static function cerToPem($cer)
    {
        $ret = false;
        $caContents = file_get_contents($cer);
        if (!empty($caContents)) {
            $caPemContent =
                '-----BEGIN CERTIFICATE-----' . PHP_EOL .
                chunk_split(base64_encode($caContents), 64, PHP_EOL) .
                '-----END CERTIFICATE-----' . PHP_EOL;

            $ret = sys_get_temp_dir() . '/' . uniqid('ca') . '.pem';
            file_put_contents($ret, $caPemContent);
        }

        return $ret;
    }
}
