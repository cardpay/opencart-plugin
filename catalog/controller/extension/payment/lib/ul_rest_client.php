<?php

$GLOBALS["LIB_LOCATION"] = __DIR__;

require_once __DIR__ . '/unlimint_exception.php';

/**
 * Unlimint cURL RestClient
 */
class ULRestClient
{
    public static $check_loop = 0;

    private static function buildRequest($api_base_url, $request)
    {
        self::prepareBuildRequest($request);

        // Set headers
        $headers = ["accept: application/json"];
        $json_content = true;
        $form_content = false;
        $default_content_type = true;

        self::prepareHeaders($request, $default_content_type, $headers, $json_content, $form_content);

        if ($default_content_type) {
            array_push($headers, "content-type: application/json");
        }

        $connect = self::buildConnection($request, $headers, $api_base_url);

        // Set data
        if (isset($request["data"])) {
            self::formatRequestData($request, $json_content, $form_content);
            curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
        }

        return $connect;
    }

    protected static function buildConnection($request, $headers, $api_base_url)
    {
        $connect = curl_init();
        curl_setopt($connect, CURLOPT_USERAGENT, "Unlimint PHP SDK v" . Unlimint::VERSION);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        // Set parameters and url
        if (isset($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
            $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
            $request["uri"] .= self::buildQuery($request["params"]);
        }

        curl_setopt($connect, CURLOPT_URL, $api_base_url . $request["uri"]);

        return $connect;
    }

    protected static function prepareHeaders(&$request, &$default_content_type, &$headers, &$json_content, &$form_content)
    {
        if (isset($request["headers"]) && is_array($request["headers"])) {
            foreach ($request["headers"] as $h => $v) {
                $h = strtolower($h);
                $v = strtolower($v);
                if ($h == "content-type") {
                    $default_content_type = false;
                    $json_content = $v == "application/json";
                    $form_content = $v == "application/x-www-form-urlencoded";
                }
                array_push($headers, $h . ": " . $v);
            }
        }
    }

    protected static function prepareBuildRequest($request)
    {
        if (!extension_loaded("curl")) {
            throw new UnlimintException("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        if (!isset($request["method"])) {
            throw new UnlimintException("No HTTP METHOD specified");
        }

        if (!isset($request["uri"])) {
            throw new UnlimintException("No URI specified");
        }
    }

    private static function formatRequestData(&$request, $json_content, $form_content)
    {
        if ($json_content) {
            if (gettype($request["data"]) === "string") {
                json_decode($request["data"], true);
            } else {
                $request["data"] = json_encode($request["data"]);
            }

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new UnlimintException("JSON Error [{$json_error}] - Data: " . $request["data"]);
                }
            }
        } elseif ($form_content) {
            $request["data"] = self::buildQuery($request["data"]);
        }
    }


    /**
     * @throws UnlimintException
     */
    private static function exec($api_url, $request)
    {
        $response = null;

        $connect = self::buildRequest($api_url, $request);
        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === false) {
            throw new UnlimintException(curl_error($connect));
        }

        if ($api_http_code != null && $api_result != null) {
            $response = [
                "status" => $api_http_code,
                "response" => json_decode($api_result, true),
            ];
        }

        if ($response != null && $response['status'] >= 400 && self::$check_loop == 0) {
            self::processErrorResponse($api_url, $request, $response);
        }

        self::$check_loop = 0;
        curl_close($connect);

        return $response;
    }

    protected static function prepareResponseMessage($response)
    {
        $message = null;
        if (isset($response['response'])) {
            if (isset($response['response']['message'])) {
                $message = $response['response']['message'];
            }

            if (isset($response['response']['cause'])) {

                $description = $response['response']['cause']['description'];
                $code = $response['response']['cause']['code'];
                if (isset($code) && isset($description)) {
                    $message .= " - " . $code . ': ' . $description;
                } elseif (is_array($response['response']['cause'])) {

                    foreach ($response['response']['cause'] as $cause) {
                        $message .= " - " . $cause['code'] . ': ' . $cause['description'];
                    }

                }

            }
        }

        return $message;
    }

    protected static function processErrorResponse($api_url, $request, $response)
    {
        try {
            self::$check_loop = 1;
            $message = self::prepareResponseMessage($response);
            $payloads = null;
            $endpoint = null;
            $errors = [];

            if ($request != null) {
                if (isset($request["data"]) && $request["data"] != null) {
                    $payloads = json_encode($request["data"]);
                }

                if (isset($request["uri"]) && $request["uri"] != null) {
                    $endpoint = $request["uri"];
                }
            }

            $errors[] = [
                "endpoint" => $endpoint,
                "message" => $message,
                "payloads" => $payloads
            ];

            self::sendErrorLog($api_url);
        } catch (Exception $e) {
            throw new UnlimintException("error to call API LOGS" . $e);
        }
    }

    private static function buildQuery($params)
    {
        if (function_exists("http_build_query")) {
            return http_build_query($params, "", "&");
        }

        foreach ($params as $name => $value) {
            $elements[] = "{$name}=" . urlencode($value);
        }

        return implode("&", $elements);
    }

    public static function get($api_url, $request)
    {
        $request["method"] = "GET";

        return self::exec($api_url, $request);
    }

    public static function patch($api_url, $request)
    {
        $request["method"] = "PATCH";

        return self::exec($api_url, $request);
    }

    public static function post($api_url, $request)
    {
        $request["method"] = "POST";

        if (!isset($request['headers'])) {
            $request['headers'] = [];
        }

        return self::exec($api_url, $request);
    }

    public static function put($api_url, $request)
    {
        $request["method"] = "PUT";

        return self::exec($api_url, $request);
    }

    public static function delete($api_url, $request)
    {
        $request["method"] = "DELETE";

        return self::exec($api_url, $request);
    }

    public static function sendErrorLog($errors)
    {
        if (is_array($errors)) {
            $errors = print_r($errors, true);
        }

        return error_log("error: " . $errors);
    }
}
