<?php

namespace Unlimit;

$GLOBALS["LIB_LOCATION"] = __DIR__;

require_once __DIR__ . '/unlimit_exception.php';
require_once DIR_SYSTEM . 'engine/autoloader.php';

/**
 * Unlimit cURL RestClient
 */
class ULRestClient
{
    public static $check_loop;

    /**
     * @param  string  $api_base_url
     * @param  array  $request
     *
     * @return false|CurlHandle
     * @throws UnlimitException|JsonException
     */
    private static function build_request($api_base_url, $request)
    {
        self::prepare_build_request($request);

        // Set headers
        $headers              = ["accept: application/json"];
        $json_content         = true;
        $form_content         = false;
        $default_content_type = true;

        self::prepare_headers($request, $default_content_type, $headers, $json_content, $form_content);

        if ($default_content_type) {
            $headers[] = "content-type: application/json";
        }

        $connect = self::build_connection($request, $headers, $api_base_url);

        // Set data
        if (isset($request["data"])) {
            self::format_request_data($request, $json_content, $form_content);
            curl_setopt($connect, CURLOPT_POSTFIELDS, $request["data"]);
        }

        return $connect;
    }

    /**
     * @param  array  $request
     * @param  array  $headers
     * @param  string  $api_base_url
     *
     * @return bool|resource
     */
    protected static function build_connection($request, $headers, $api_base_url)
    {
        $connect = curl_init();
        curl_setopt($connect, CURLOPT_USERAGENT, "Unlimit PHP SDK v" . Unlimit::VERSION);
        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $request["method"]);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        // Set parameters and url
        if (isset($request["params"]) && is_array($request["params"]) && count($request["params"]) > 0) {
            $request["uri"] .= (strpos($request["uri"], "?") === false) ? "?" : "&";
            $request["uri"] .= self::build_query($request["params"]);
        }

        curl_setopt($connect, CURLOPT_URL, $api_base_url . $request["uri"]);

        return $connect;
    }

    protected static function prepare_headers(
        $request,
        &$default_content_type,
        &$headers,
        &$json_content,
        &$form_content
    ): void {
        if (isset($request["headers"]) && is_array($request["headers"])) {
            foreach ($request["headers"] as $h => $v) {
                if ($h === "content-type") {
                    $default_content_type = false;
                    $json_content         = ($v === "application/json");
                    $form_content         = ($v === "application/x-www-form-urlencoded");
                }
                $headers[] = $h . ": " . $v;
            }
        }
    }

    /**
     * @param $request
     *
     * @throws UnlimitException
     */
    protected static function prepare_build_request($request)
    {
        if ( ! extension_loaded("curl")) {
            throw new UnlimitException("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }

        if ( ! isset($request["method"])) {
            throw new UnlimitException("No HTTP METHOD specified");
        }

        if ( ! isset($request["uri"])) {
            throw new UnlimitException("No URI specified");
        }
    }

    /**
     * @param $request
     * @param $json_content
     * @param $form_content
     *
     * @throws UnlimitException|JsonException
     */
    private static function format_request_data(&$request, $json_content, $form_content)
    {
        if ($json_content) {
            if (is_string($request["data"])) {
                json_decode($request["data"], true, 512, JSON_THROW_ON_ERROR);
            } else {
                $request["data"] = json_encode($request["data"], JSON_THROW_ON_ERROR);
            }

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error !== JSON_ERROR_NONE) {
//                    throw new UnlimitException("JSON Error [$json_error] - Data: " . $request["data"]);
                }
            }
        } elseif ($form_content) {
            $request["data"] = self::build_query($request["data"]);
        }
    }


    /**
     * @param  string  $api_url
     * @param  array  $request
     *
     * @return array|null
     * @throws JsonException
     * @throws UnlimitException
     */
    private static function exec(string $api_url, array $request)
    {
        $response = null;

        $connect       = self::build_request($api_url, $request);
        $api_result    = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        if ($api_result === false) {
            throw new UnlimitException(curl_error($connect));
        }

        if ( ! is_null($api_http_code) && ! is_null($api_result)) {
            $response = [
                "status"   => $api_http_code,
                "response" => json_decode($api_result, true, 512, JSON_THROW_ON_ERROR),
            ];
        }

        if ( ! is_null($response) && $response['status'] >= 400 && self::$check_loop === 0) {
            self::process_error_response($api_url, $request, $response);
        }

        self::$check_loop = 0;
        curl_close($connect);

        return $response;
    }

    protected static function prepare_response_message($response)
    {
        $message = null;
        if (isset($response['response'])) {
            if (isset($response['response']['message'])) {
                $message = $response['response']['message'];
            }

            if (isset($response['response']['cause'])) {

                $description = $response['response']['cause']['description'];
                $code        = $response['response']['cause']['code'];
                if (isset($code, $description)) {
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

    /**
     * @param $api_url
     * @param $request
     * @param $response
     *
     * @throws UnlimitException
     */
    protected static function process_error_response($api_url, $request, $response)
    {
        try {
            self::$check_loop = 1;
            $message          = self::prepare_response_message($response);
            $payloads         = null;
            $endpoint         = null;

            if ( ! is_null($request)) {
                if (isset($request["data"]) && ! is_null($request["data"])) {
                    $payloads = json_encode($request["data"], JSON_THROW_ON_ERROR);
                }

                if (isset($request["uri"]) && ! is_null($request["uri"])) {
                    $endpoint = $request["uri"];
                }
            }

            self::send_error_log([
                "api_url"  => $api_url,
                "endpoint" => $endpoint,
                "message"  => $message,
                "payloads" => $payloads
            ]);
        } catch (Exception $e) {
            throw new UnlimitException("error to call API LOGS" . $e);
        }
    }

    /**
     * @param  array|null  $params
     *
     * @return string
     */
    private static function build_query($params)
    {
        $elements = [];
        if (function_exists("http_build_query")) {
            return http_build_query($params);
        }

        foreach ($params as $name => $value) {
            $elements[] = "$name=" . urlencode($value);
        }

        return implode("&", $elements);
    }

    /**
     * @param  string  $api_url
     * @param$request
     *
     * @return array|null
     * @throws UnlimitException|JsonException
     */
    public static function get($api_url, $request)
    {
        $request["method"] = "GET";

        return self::exec($api_url, $request);
    }

    /**
     * @param  string  $api_url
     * @param  array|null  $request
     *
     * @return array|null
     * @throws JsonException
     * @throws UnlimitException
     */
    public static function patch(string $api_url, $request)
    {
        $request["method"] = "PATCH";

        return self::exec($api_url, $request);
    }

    /**
     * @param  string  $api_url
     * @param  array|null  $request
     *
     * @return array|null
     * @throws JsonException
     * @throws UnlimitException
     */
    public static function post($api_url, $request)
    {
        $request["method"] = "POST";

        if ( ! isset($request['headers'])) {
            $request['headers'] = [];
        }

        return self::exec($api_url, $request);
    }

    /**
     * @param  string  $api_url
     * @param  array|null  $request
     *
     * @return array|null
     * @throws JsonException
     * @throws UnlimitException
     */
    public static function put(string $api_url, $request)
    {
        $request["method"] = "PUT";

        return self::exec($api_url, $request);
    }

    /**
     * @param  string  $api_url
     * @param  array|null  $request
     *
     * @return array|null
     * @throws JsonException
     * @throws UnlimitException
     */
    public static function delete(string $api_url, $request)
    {
        $request["method"] = "DELETE";

        return self::exec($api_url, $request);
    }

    /**
     * @param  array|string  $errors
     *
     */
    public static function send_error_log(array|string $errors): void
    {
        $config = new \Opencart\System\Engine\Config();
        $config->addPath(DIR_CONFIG);
        $config->load('default');
        $config->load(strtolower(APPLICATION));
        $config->set('application', APPLICATION);
        $log = new \Opencart\System\Library\Log($config->get('error_filename'));

        if (is_array($errors)) {
            $errors = print_r($errors, true);
        }

        $log->write("error: " . $errors);
    }
}
