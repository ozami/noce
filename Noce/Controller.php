<?php
namespace Noce;

class Controller
{
    const CONTENT_TYPE_HTML = "text/html";
    const CONTENT_TYPE_JSON = "application/json";
    const CONTENT_TYPE_JSONP = "text/javascript";
    
    public $content_type = self::CONTENT_TYPE_HTML;
    
    public function act($action)
    {
        try {
            $method = array($this, $this->getActionMethod($action));
            if (!is_callable($method)) {
                return array();
            }
            return (array) call_user_func($method);
        }
        catch (BadRequest $e) {
            $this->handleBadRequest($e);
        }
        catch (Forbidden $e) {
            $this->handleForbidden($e);
        }
        catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getActionMethod($action)
    {
        $action = preg_replace("/[^0-9a-zA-Z]/", " ", $action);
        $action = strtolower($action);
        $action = ucwords($action);
        $action = str_replace(" ", "", $action);
        return "act" . $action;
    }
    
    public function getFormRequest()
    {
        parse_str(file_get_contents("php://input"), $form);
        return $form;
    }

    public function getJsonRequest()
    {
        return json_decode(file_get_contents("php://input"), true);
    }

    public function respond(array $args = array())
    {
        $default = array(
            "status" => 200,
            "header" => array(),
            "body" => null,
            "version" => "HTTP/1.1",
            "json" => null,
            "jsonp" => null,
            "jsonp_callback" => "callback");
        $args += $default;
        extract(array_intersect_key($args, $default));
        if (!isset($reason)) {
            $reasons = array(
                100 => "Continue",
                101 => "Switching Protocols",
                200 => "OK",
                201 => "Created",
                202 => "Accepted",
                203 => "Non-Authoritative Information",
                204 => "No Content",
                205 => "Reset Content",
                206 => "Partial Content",
                300 => "Multiple Choices",
                301 => "Moved Permanently",
                302 => "Found",
                303 => "See Other",
                304 => "Not Modified",
                305 => "Use Proxy",
                307 => "Temporary Redirect",
                400 => "Bad Request",
                401 => "Unauthorized",
                402 => "Payment Required",
                403 => "Forbidden",
                404 => "Not Found",
                405 => "Method Not Allowed",
                406 => "Not Acceptable",
                407 => "Proxy Authentication Required",
                408 => "Request Time-out",
                409 => "Conflict",
                410 => "Gone",
                411 => "Length Required",
                412 => "Precondition Failed",
                413 => "Request Entity Too Large",
                414 => "Request-URI Too Large",
                415 => "Unsupported Media Type",
                416 => "Requested range not satisfiable",
                417 => "Expectation Failed",
                500 => "Internal Server Error",
                501 => "Not Implemented",
                502 => "Bad Gateway",
                503 => "Service Unavailable",
                504 => "Gateway Time-out",
                505 => "HTTP Version not supported");
            $reason = @$reasons[$status] . "";
        }
        // complement with default Content-Type
        if (!isset($header["Content-Type"]) && $this->content_type != "") {
            $header["Content-Type"] = $this->content_type;
        }
        if ($body instanceof \SplFileInfo) {
            if (!isset($header["Content-Length"])) {
                list (,,,,,,, $header["Content-Length"]) = $body->fstat();
            }
        }
        else {
            if (!isset($json) && $header["Content-Type"] == "application/json") {
                $json = true;
            }
            if (!isset($jsonp) && $header["Content-Type"] == "text/javascript") {
                $jsonp = true;
            }
            if (@$jsonp) {
                $body = json_encode($body);
                $body = "$callback($body);";
                if (!isset($header["Content-Type"])) {
                    $header["Content-Type"] = "text/javascript";
                }
            }
            else if (@$json) {
                $body = json_encode($body);
                if (!isset($header["Content-Type"])) {
                    $header["Content-Type"] = "application/json";
                }
            }
            // calculate Content-Length
            $header["Content-Length"] = strlen($body);
            if (!isset($header["Content-Type"])) {
                $header["Content-Type"] = "text/html";
            }
        }
        header("$version $status $reason");
        foreach ($header as $field => $value) {
            $value = preg_replace("/(\r\n|\r|\n])[ \t]*/", "\r\n ", $value);
            header("$field: $value");
        }
        // write body
        if ($body instanceof \SplFileObject) {
            $body->rewind();
            $body->fpassthru();
        }
        else if ($body instanceof \SplFileInfo) {
            $body->openFile()->fpassthru();
        }
        else if ($body instanceof View) {
            $body->render();
        }
        else {
            echo $body;
        }
        exit();
    }
    
    public function ok($body = null, $header = array())
    {
        $this->respond(compact("body", "header"));
    }

    public function okJson($body = null, $header = array())
    {
        $header["Content-Type"] = self::CONTENT_TYPE_JSON;
        $this->respond(array("json" => true) + compact("body", "header"));
    }
    
    public function okSaveFile($body, $content_type, $file_name = null)
    {
        $header = array(
            "Content-Disposition" => "attachment",
            "Content-Type" => $content_type,
            "Pragma" => "public" // for IE8 or older with SSL
        );
        if ($file_name !== null) {
            // restrict file_name to us-ascii printables
            if (preg_match("/[^ -~]/m", $file_name)) {
                throw new \RuntimeException();
            }
            $header["Content-Disposition"] .= "; filename=$file_name";
        }
        $this->respond(compact("body", "header"));
    }

    public function found($url, $params = array(), $fragment = "")
    {
        $url = new Url($url, $params, $fragment);
        $url = $url->toString();
        $header = array("Location" => $url);
        $this->respond(array(
            "status" => 302, 
            "header" => $header));
    }
    
    public function notFound($body = "Not Found")
    {
        $this->respond(array(
            "status" => 404, 
            "body" => $body));
    }

    public function badRequest($args = array())
    {
        $this->respond(array("status" => 400) + $args);
    }

    public function badRequestJson($body, $header = array())
    {
        $header["Content-Type"] = self::CONTENT_TYPE_JSON;
        $this->respond(
            array("json" => true, "status" => 400)
            + compact("body", "header")
        );
    }

    public function unauthorized($auth_scheme, $realm, $body = "Unauthorized")
    {
        $this->respond(array(
            "status" => 401, 
            "header" => array("WWW-Authenticate" => "$auth_scheme realm=\"$realm\""),
            "body" => $body));
    }
    
    public function forbidden($args = array())
    {
        $this->respond(array("status" => 403) + $args);
    }

    public function internalServerError($body = null)
    {
        $this->respond(array(
            "status" => 500, 
            "body" => $body));
    }

    public function internalServerErrorJson($body = null)
    {
        $this->respond(array(
            "status" => 500,
            "json" => true,
            "body" => $body,
            "header" => array(
                "Content-Type" => self::CONTENT_TYPE_JSON
            )
        ));
    }
    
    public function handleBadRequest($e)
    {
        // TODO: log?
        $this->badRequest(array(
            "body" => $e->getData()
        ));
    }
    
    public function handleForbidden($e)
    {
        // TODO: log?
        $this->forbidden(array(
            "body" => $e->getData()
        ));
    }
    
    public function handleException($e)
    {
        if (class_exists("Noce\\Debug", true)) {
            Debug::dbg($e);
        }
        $this->internalServerError();
    }
}
