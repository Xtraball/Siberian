<?php

namespace Siberian;

class License
{
    /**
     * @param $code
     * @param $itemId
     * @param null $license
     * @throws Exception
     */
    public static function checkModuleLicense ($code, $itemId, $license = null)
    {
        $sk = __get('siberiancms_key');
        $lk = $license ?? __get($code . '_key');
        $query = http_build_query([
            'edd_action' => 'check_license',
            'item_id' => $itemId,
            'license' => $lk,
            'url' => $sk,
        ]);
        $urlCheck = 'https://extensions.siberiancms.com/?' . $query;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $urlCheck,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
        ]);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ((int) $status === 400) {
            throw new Exception($code. ': ' . $response);
        }

        if ((int) $status === 200) {
            // Decode json and ensure all is ok
            $licenseResult = json_decode($response, true);
            if ($licenseResult['license'] === 'site_inactive') {
                throw new Exception($code. ': Your license was not correctly activated, please request a new download package.');
            }
            if ($licenseResult['license'] === 'invalid') {
                throw new Exception($code. ': Your license is not valid.');
            }
            if ($licenseResult['license'] === 'invalid_item_id') {
                throw new Exception($code. ': Your license is not for this module.');
            }
            if ($licenseResult['license'] === 'inactive') {
                throw new Exception($code. ': Your license was not correctly activated, please request a new download package.');
            }
        }
    }
}
