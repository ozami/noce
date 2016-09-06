<?php
namespace Noce;

class Html_Form implements \ArrayAccess
{
    public $_form;
    public $_error_decorator;
    public $_error_list_decorator;
    
    public function __construct($form = null)
    {
        $this->setForm($form);
        $this->_error_decorator = function($error) {
            return $error->tag("li");
        };
        $this->_error_list_decorator = function($errors) {
            return Html::h()->tag("ul")->append($errors);
        };
    }

    public function getForm()
    {
        return $this->_form;
    }

    public function setForm($form)
    {
        $this->_form = $form;
    }

    public function value($itemPath)
    {
        return Html::h($this->getItem($itemPath)->getValue());
    }
    
    public function number($itemPath)
    {
        return Html::h(number_format($this->getItem($itemPath)->getValue()));
    }

    public function error($item_paths = array())
    {
        $errs = array();
        foreach ((array) $item_paths as $p) {
            $item = $this->getItem($p);
            if (!$item->isError()) {
                continue;
            }
            $errs[] = $item->getErrorString();
        }
        if (!$errs) {
            return "";
        }
        $errs = array_unique($errs);
        // decorate
        $html = Html::h();
        foreach ($errs as $e) {
            $html->append(
                call_user_func($this->_error_decorator, Html::h($e))
            );
        }
        $html = call_user_func($this->_error_list_decorator, $html);
        return $html;
    }
    
    public function setErrorDecorator($decorator)
    {
        $this->_error_decorator = $decorator;
    }
    
    public function setErrorListDecorator($decorator)
    {
        $this->_error_list_decorator = $decorator;
    }
    
    public function selected($itemPath, $separator = ", ")
    {
        // $separator can be instance of Html
        $labels = (array) $this->getItem($itemPath)->getSelected();
        $h = new Html();
        $h->append(array_shift($labels));
        foreach ($labels as $label) {
            $h->append($separator);
            $h->append($label);
        }
        return $h;
    }

    public function input($itemPath, $type)
    {
        return Html::h()->tag("input")->attrs(array(
            "type" => $type,
            "name" => $this->makeName($itemPath),
            "value" => $this->getItem($itemPath)->getValue()
        ));
    }

    public function text($itemPath)
    {
        return $this->input($this, $itemPath, "text");
    }

    public function hidden($itemPath)
    {
        return $this->input($this, $itemPath, "hidden");
    }

    public function password($itemPath)
    {
        return $this->input($itemPath, "password");
    }

    public function textarea($itemPath)
    {
        $value = $this->getItem($itemPath)->getValue();
        return Html::h($value)->tag("textarea")->attrs(array(
            "name" => $this->makeName($itemPath)
        ));
    }

    public function checkbox($itemPath, $value, $label = true)
    {
        return $this->checkableInput("checkbox", $itemPath, $value, $label);
    }

    public function checkboxes($itemPath, $label = true)
    {
        return $this->checkableInputs("checkbox", $itemPath, $label);
    }

    public function radio($itemPath, $value, $label = true)
    {
        return $this->checkableInput("radio", $itemPath, $value, $label);
    }

    public function radios($itemPath, $label = true)
    {
        return $this->checkableInputs("radio", $itemPath, $label);
    }

    public function select($itemPath)
    {
        $multiple = $this->getItem($itemPath)->getMultiple();
        $attribs = array();
        $attribs["name"] = $this->makeName($itemPath);
        if ($multiple) {
            $attribs["multiple"] = "multiple";
            $attribs["name"] .= "[]";
        }
        return Html::h()
            ->tag("select")
            ->attrs($attribs)
            ->append($this->options($itemPath));
    }

    public function options($itemPath)
    {
        $input = $this->getItem($itemPath);
        return Html::optionTag($input->getOptions(), $input->getValue());
    }

    public function file($itemPath)
    {
        return $this->input($itemPath, "file");
    }

    public function date($itemPath, $format = "Y/n/j")
    {
        $html = $this->input($itemPath, "text");
        $value = $this->getItem($itemPath)->getValue();
        if ((string) (int) $value == (string) $value && $value >= 0) {
            $html->attr("value", date($format, (int) $value));
        }
        return $html;
    }

    public function makeName($itemPath)
    {
        $itemPath = explode("/", trim($itemPath, "/"));
        $name = array_shift($itemPath);
        foreach ($itemPath as $p) {
            $name .= "[$p]";
        }
        return $name;
    }

    public function checkableInputs($type, $itemPath, $label = true)
    {
        $html = new Html();
        foreach ($this->getItem($itemPath)->getOptions() as $value => $_) {
            $html->append($this->$type($itemPath, $value, $label));
        }
        return $html;
    }

    protected function checkableInput($type, $itemPath, $value, $label = true)
    {
        $item = $this->getItem($itemPath);
        // name
        $name = $this->makeName($itemPath);
        if ($item->getMultiple()) {
            $name .= "[]";
        }
        $html = Html::h()->tag("input")->attrs(compact("type", "name", "value"));
        // checked
        if (in_array("$value", (array) $item->getValue())) {
            $html->attr("checked", "checked");
        }
        // label
        if ($label) {
            $html = Html::h($html);
            $html->append($item->getOptionsLabel($value));
            $html = Html::h($html)->tag("label");
        }
        return $html;
    }
}
