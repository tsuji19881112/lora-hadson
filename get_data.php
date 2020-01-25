<?php
date_default_timezone_set('Asia/Tokyo');
define("URL_DATA_STORAGE_API", "https://science_kitchine.data.thethingsnetwork.org/api/v2/query?last=7d");
define("TTN_ACCESS_KEY", "");

class DataStorage
{
    public function main()
    {
        $headers = array(
            "Accept: application/json",
            "Authorization: key ".TTN_ACCESS_KEY,
        );
        $option = array(CURLOPT_HTTPHEADER => $headers);
        $data = $this->_callApi(URL_DATA_STORAGE_API, null, $option);

        if (!in_array($data['curlInfo']['httpCode'], array(200, 204))) {
            echo 'データの取得に失敗しました。';
            exit();
        }
        if (empty($data['response'])) {
            echo 'データがありません。';
            exit();
        }

        $response = json_decode($data['response'], true);
        $sort = array();
        $data = array();
        foreach ($response as $key => $value) {
            $sort[] = $value['time'];
            if (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2})/', $value['time'], $matches)) {
                $timestamp = strtotime("{$matches[0]}Z");
                $value['time'] = date("Y-m-d H:i:s", $timestamp);
                $value['timestamp'] = $timestamp;
            }
            $data[] = $value;
        }
        array_multisort($sort, SORT_DESC, $data);
        var_dump($data);
    }

    protected function _callApi($url, $params = null, $opt = null, $timeout = 100)
    {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
        );
        if (!is_null($params)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $params;
        }
        if (!is_null($opt)) {
            $options = $options + $opt;
        }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $curlInfo = array(
            'httpCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        );
        curl_close($ch);
        return array('response' => $response, 'curlInfo' => $curlInfo);
    }
}
$storage= new DataStorage();
$storage->main();
?>
