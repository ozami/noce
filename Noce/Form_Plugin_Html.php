<?php
namespace Noce;

class Form_Plugin_Html
{
    public $_error_decorator;
    public $_error_list_decorator;
    
    public function __construct()
    {
        $this->_error_decorator = function($error) {
            return $error->tag("li");
        };
        $this->_error_list_decorator = function($errors) {
            return Html::h()->tag("ul")->append($errors);
        };
    }

    public function value(Form $form, $itemPath)
    {
        return Html::h($form->getItem($itemPath)->getValue());
    }
    
    public function number(Form $form, $itemPath)
    {
        return Html::h(number_format($form->getItem($itemPath)->getValue()));
    }

    public function error(Form $form, $item_paths = array())
    {
        $errs = array();
        foreach ((array) $item_paths as $p) {
            $item = $form->getItem($p);
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
    
    public function selected(Form $form, $itemPath, $separator = ", ")
    {
        // $separator can be instance of Html
        $labels = (array) $form->getItem($itemPath)->getSelected();
        $h = new Html();
        $h->append(array_shift($labels));
        foreach ($labels as $label) {
            $h->append($separator);
            $h->append($label);
        }
        return $h;
    }

    public function input(Form $form, $itemPath, $type)
    {
        return Html::h()->tag("input")->attrs(array(
            "type" => $type,
            "name" => $this->makeName($itemPath),
            "value" => $form->getItem($itemPath)->getValue()
        ));
    }

    public function text(Form $form, $itemPath)
    {
        return $this->input($form, $itemPath, "text");
    }

    public function hidden(Form $form, $itemPath)
    {
        return $this->input($form, $itemPath, "hidden");
    }

    public function password(Form $form, $itemPath)
    {
        return $this->input($itemPath, "password");
    }

    public function textarea(Form $form, $itemPath)
    {
        $value = $form->getItem($itemPath)->getValue();
        return Html::h($value)->tag("textarea")->attrs(array(
            "name" => $this->makeName($itemPath)
        ));
    }

    public function checkbox(Form $form, $itemPath, $value, $label = true)
    {
        return $this->checkableInput($form, "checkbox", $itemPath, $value, $label);
    }

    public function checkboxes(Form $form, $itemPath, $label = true)
    {
        return $this->checkableInputs($form, "checkbox", $itemPath, $label);
    }

    public function radio(Form $form, $itemPath, $value, $label = true)
    {
        return $this->checkableInput($form, "radio", $itemPath, $value, $label);
    }

    public function radios(Form $form, $itemPath, $label = true)
    {
        return $this->checkableInputs($form, "radio", $itemPath, $label);
    }

    public function select(Form $form, $itemPath)
    {
        $multiple = $form->getItem($itemPath)->getMultiple();
        $attribs = array();
        $attribs["name"] = $this->makeName($itemPath);
        if ($multiple) {
            $attribs["multiple"] = "multiple";
            $attribs["name"] .= "[]";
        }
        return Html::h()
            ->tag("select")
            ->attrs($attribs)
            ->append($this->options($form, $itemPath));
    }

    public function options(Form $form, $itemPath)
    {
        $input = $form->getItem($itemPath);
        return Html::optionTag($input->getOptions(), $input->getValue());
    }

    public function file(Form $form, $itemPath)
    {
        return $this->input($form, $itemPath, "file");
    }

    public function date(Form $form, $itemPath, $format = "Y/n/j")
    {
        $html = $this->input($itemPath, "text");
        $value = $form->getItem($itemPath)->getValue();
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

    public function checkableInputs(Form $form, $type, $itemPath, $label = true)
    {
        $html = new Html();
        foreach ($form->getItem($itemPath)->getOptions() as $value => $_) {
            $html->append($this->$type($form, $itemPath, $value, $label));
        }
        return $html;
    }

    protected function checkableInput(Form $form, $type, $itemPath, $value, $label = true)
    {
        $item = $form->getItem($itemPath);
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
