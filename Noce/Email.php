<?php
namespace Noce;

class Email
{
    public $_fields = array();
    public $_body = "";

    public function __construct()
    {
    }

    public function getHeader($pos)
    {
        return $this->_fields[$pos];
    }

    public function setHeader($pos, $name, $value)
    {
        $name = $this->formatName($name);
        $this->_fields[$pos] = array($name, $value);
    }

    public function addHeader($name, $value)
    {
        $name = $this->formatName($name);
        $this->_fields[] = array($name, $value);
        return count($this->_fields) - 1;
    }

    public function removeHeader($pos)
    {
        unset($this->_fields[$pos]);
    }

    public function findHeader($name, $pos = 0)
    {
        $name = $this->formatName($name);
        $count = count($this->_fields);
        while ($pos < $count) {
            if ($this->_fields[$pos][0] == $name) {
                return $pos;
            }
            ++$pos;
        }
        return false;
    }

    public function formatName($name)
    {
        $name = ucwords(strtolower(str_replace("-", " ", $name)));
        return str_replace(" ", "-", $name);
    }

    public function getReturnPath()
    {
        $pos = $this->findHeader("return-path");
        if ($pos === false) {
            return null;
        }
        return trim($this->_fields[$pos][1], "<>");
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function setBody($body)
    {
        $this->_body = $body;
    }

    public function string()
    {
        $body = preg_replace("/(\r\n|\r|\n)/u", "\r\n", $this->_body);
        $body = mb_convert_encoding($this->_body, "UTF-8");
        $body = base64_encode($body);
        $body = chunk_split($body);
        if (!$this->findHeader("date")) {
            $this->addHeader("date", time());
        }
        if (!$this->findHeader("message-id")) {
            $this->addHeader("message-id", $this->newMessageId());
        }
        $head = $this->_fields;
        $head = $this->combineHeader($head);
        $head = $this->encodeHeader($head);
        $mimeHead = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n";
        return $head . $mimeHead . "\r\n" . $body;
    }

    public function send()
    {
        $msg = $this->string();
        $cmd = ini_get("sendmail_path");
        $returnPath = $this->getReturnPath();
        if ($returnPath != "") {
            $cmd .= " -f" . escapeshellarg($returnPath);
        }
        $proc = proc_open(
            $cmd,
            array(array("pipe", "r")), $pipes);
        fwrite($pipes[0], $msg);
        fclose($pipes[0]);
        $r = proc_close($proc);
        if ($r) {
            throw new \Exception("err_send_mail");
        }
    }

    public function needCombine($name)
    {
        return in_array($name, array(
            "Date", "Subject", "To", "Cc", "Bcc",
            "From", "Sender", "Reply-To", "Message-Id",
            "In-Reply-To", "References"));
    }

    public function combineHeader($fields)
    {
        $combined = array();
        while ($fields) {
            $name = $fields[0][0];
            if (!$this->needCombine($name)) {
                $combined[] = array_shift($fields);
                continue;
            }
            $values = array();
            foreach ($fields as $i => $field) {
                if ($field[0] == $name) {
                    $values[] = $field[1];
                    unset($fields[$i]);
                }
            }
            $combined[] = array($name, $values);
            $fields = array_values($fields);
        }
        return $combined;
    }

    public function encodeHeader($fields)
    {
        $s = "";
        foreach ($fields as $field) {
            $encoder = "encode" . str_replace("-", "", $field[0]);
            if (!method_exists($this, $encoder)) {
                $encoder = "encodeText";
            }
            $s .= $field[0] . ": " . $this->$encoder($field[1]) . "\r\n";
        }
        return $s;
    }

    public function encodeDate($value)
    {
        if (is_array($value)) {
            $value = $value[0];
        }
        return date("r", $value);
    }

    public function encodeTo($addresses)
    {
        return $this->encodeAddressList($addresses);
    }

    public function encodeCc($addresses)
    {
        return $this->encodeAddressList($addresses);
    }

    public function encodeFrom($addresses)
    {
        return $this->encodeAddressList($addresses);
    }

    public function encodeSender($addresses)
    {
        return $this->encodeAddress($addresses[0]);
    }

    public function encodeReplyTo($addresses)
    {
        return $this->encodeAddress($addresses[0]);
    }

    public function encodeAddress($value)
    {
        if (is_array($value)) {
            return sprintf(
                "%s <%s>", $this->encodeText($value[0]), $value[1]);
        }
        return $value;
    }

    public function encodeAddressList($values)
    {
        foreach ($values as $i => $value) {
            $values[$i] = $this->encodeAddress($value);
        }
        return join(",\r\n ", $values);
    }

    public function encodeMessageId($value)
    {
        if (is_array($value)) {
            $value = $value[0];
        }
        return "<$value>";
    }

    public function encodeInReplyTo($values)
    {
        return $this->encodeMessageIdList($values);
    }

    public function encodeReferences($values)
    {
        return $this->encodeMessageIdList($values);
    }

    public function encodeMessageIdList($values)
    {
        foreach ($values as $i => $value) {
            $values[$i] = $this->encodeMessageId($value);
        }
        return join(",\r\n ", $values);
    }

    public function encodeText($value)
    {
        if (is_array($value)) {
            $value = $value[0];
        }
        return mb_encode_mimeheader($value, "UTF-8", "B");
    }

    public function newMessageId()
    {
        $time = explode(" ", microtime());
        return sprintf(
            "%s.%s.%s@%s",
            $time[1],
            str_replace("0.", "", $time[0]), 
            mt_rand(), 
            php_uname("n"));
    }

    public function import($s)
    {
        $this->_fields = array();
        $s = preg_replace("/(\r\n|\r|\n)/u", "\n", $s);
        @list ($head, $body) = explode("\n\n", $s, 2);
        $head = preg_replace("/\n\\s+/u", " ", $head);
        $head = explode("\n", $head);
        foreach ($head as $field) {
            list ($name, $value) = explode(": ", $field, 2);
            $importer = "import" . str_replace("-", "", $name);
            if (method_exists($this, $importer)) {
                $this->$importer($value);
            }
            else {
                $this->addHeader($name, $value);
            }
        }
        $this->setBody($body);
    }

    public function importTo($value)
    {
        $this->importAddress("To", $value);
    }
    
    public function importCc($value)
    {
        $this->importAddress("Cc", $value);
    }

    public function importFrom($value)
    {
        $this->importAddress("From", $value);
    }

    public function importRreplyTo($value)
    {
        $this->importAddress("Reply-To", $value);
    }

    public function importAddress($name, $value)
    {
        $address = trim($value);
        if (preg_match("/(.*)\s+<(.+)>$/u", $address, $match)) {
            $address = array($match[1], $match[2]);
        }
        $this->addHeader($name, $address);
    }
}
