<?php
use Noce\Html;

class HtmlTest extends PHPUnit_Framework_TestCase
{
    const specials = "&<>'\"";
    const escaped = "&amp;&lt;&gt;&#039;&quot;";
    
    /**
     * @covers Noce\Html::escape
     */
    public function testEscape()
    {
        foreach (array("", null, "&", "<", ">", "'", '"', "あ", "a") as $s) {
            $this->assertSame(htmlspecialchars($s, ENT_QUOTES), Html::escape($s));
        }
    }

    /**
     * @covers Noce\Html::getEscapedHtml
     */
    public function testGetEscapedHtml()
    {
        $h = new Html();
        $this->assertSame("", $h->getEscapedHtml());
        
        $h = Html::h(self::specials);
        $this->assertSame(self::escaped, $h->getEscapedHtml());
        
        $h = Html::h()->tag("br");
        $this->assertSame("<br />", $h->getEscapedHtml());
        
        $h = Html::h()->tag("p");
        $this->assertSame("<p></p>", $h->getEscapedHtml());
        
        $h = Html::h()->tag("br")->attr("name", self::specials);
        $this->assertSame('<br name="' . self::escaped . '" />', $h->getEscapedHtml());
        
        $h = Html::h(self::specials)->tag("br");
        $this->assertSame("<br />", $h->getEscapedHtml());
        
        $h = Html::h(self::specials)->tag("p");
        $this->assertSame("<p>" . self::escaped . "</p>", $h->getEscapedHtml());
    }
    
    /**
     * @covers Noce\Html::getEscapedChildren
     */
    public function testGetEscapedChildren()
    {
        $h = new Html();
        $this->assertSame("", $h->getEscapedChildren());
        $h->append(self::specials);
        $this->assertSame(self::escaped, $h->getEscapedChildren());
        $h->append(Html::h(self::specials));
        $this->assertSame(self::escaped . self::escaped, $h->getEscapedChildren());
    }
    
    /**
     * @covers Noce\Html::getEscapedAttributes
     */
    public function testGetEscapedAttributes()
    {
        $h = new Html();
        $this->assertSame("", $h->getEscapedAttributes());
        $h->attr("name1", "value1");
        $this->assertSame('name1="value1"', $h->getEscapedAttributes());
        $value2 = self::specials;
        $h->attr("name2", $value2);
        $this->assertSame('name1="value1" name2="' . htmlspecialchars($value2, ENT_QUOTES) . '"', $h->getEscapedAttributes());
    }
    
    /**
     * @covers Noce\Html::append
     */
    public function testAppend()
    {
        $html = new Html();
        $r = $html->append("");
        $this->assertSame($html, $r);
        $this->assertSame(array(), $html->_children);

        $html = new Html();
        $r = $html->append(null);
        $this->assertSame($html, $r);
        $this->assertSame(array(), $html->_children);

        $html = new Html();
        $r = $html->append("test1");
        $this->assertSame($html, $r);
        $this->assertSame(array("test1"), $html->_children);
        $r = $html->append("test2");
        $this->assertSame($html, $r);
        $this->assertSame(array("test1", "test2"), $html->_children);
        $r = $html->append("");
        $this->assertSame($html, $r);
        $this->assertSame(array("test1", "test2"), $html->_children);
    }
    
    /**
     * @covers Noce\Html::prepend
     */
    public function testPrepend()
    {
        $html = new Html();
        $r = $html->prepend("");
        $this->assertSame($html, $r);
        $this->assertSame(array(), $html->_children);
        
        $html = new Html();
        $r = $html->prepend(null);
        $this->assertSame($html, $r);
        $this->assertSame(array(), $html->_children);
        
        $html = new Html();
        $r = $html->prepend("test1");
        $this->assertSame($html, $r);
        $this->assertSame(array("test1"), $html->_children);
        $r = $html->prepend("test2");
        $this->assertSame($html, $r);
        $this->assertSame(array("test2", "test1"), $html->_children);
        $r = $html->prepend("");
        $this->assertSame($html, $r);
        $this->assertSame(array("test2", "test1"), $html->_children);
    }
    
    /**
     * @covers Noce\Html::tag
     */
    public function testTag()
    {
        $html = new Html();
        $r = $html->tag("test");
        $this->assertSame($r, $html);
        $this->assertSame("test", $html->_tag);

        $r = $html->tag(null);
        $this->assertSame($r, $html);
        $this->assertSame("", $html->_tag);

        $r = $html->tag(0);
        $this->assertSame($r, $html);
        $this->assertSame("0", $html->_tag);

        $r = $html->tag("-:_.");
        $this->assertSame($r, $html);
        $this->assertSame("-:_.", $html->_tag);
        
        foreach (str_split("!\"#$%&'()=^~\\|@`[{}];+*,<>/?\r\n\t ") as $c) {
            try {
                $html = new Html();
                $html->tag("test{$c}test");
                $this->fail("Could not catch '$c'");
            }
            catch (InvalidArgumentException $e) {
            }
        }
    }

    /**
     * @covers Noce\Html::attr
     */
    public function testAttr()
    {
        $html = new Html();
        $r = $html->attr("name", "value");
        $this->assertSame($r, $html);
        $this->assertSame(array("name" => "value"), $html->_attributes);

        $v = "value changed";
        $r = $html->attr("name", $v);
        $this->assertSame($r, $html);
        $this->assertSame(array("name" => $v), $html->_attributes);

        $html = new Html();
        try {
            $r = $html->attr(null, "value");
            $this->fail("Could not catch empty name");
        }
        catch (InvalidArgumentException $e) {
        }

        $html = new Html();
        $r = $html->attr("-:_.", "value");
        $this->assertSame($r, $html);
        $this->assertSame(array("-:_." => "value"), $html->_attributes);
        
        foreach (str_split("!\"#$%&'()=^~\\|@`[{}];+*,<>/?\r\n\t ") as $c) {
            $html = new Html();
            try {
                $html->attr("test{$c}test", "value");
                $this->fail("Could not catch '$c'");
            }
            catch (InvalidArgumentException $e) {
            }
        }
    }

    /**
     * @covers Noce\Html::attrs
     */
    public function testAttrs()
    {
        $html = new Html();
        $r = $html->attrs(array());
        $this->assertSame($r, $html);
        $this->assertSame(array(), $html->_attributes);

        $html = new Html();
        $v = array("name1" => "value1", "name2" => "value2");
        $r = $html->attrs($v);
        $this->assertSame($r, $html);
        $this->assertSame($v, $html->_attributes);
    }
    
    /**
     * @covers Noce\Html::id
     */
    public function testId()
    {
        $html = new Html();
        $r = $html->id("test");
        $this->assertSame($r, $html);
        $this->assertSame(array("id" => "test"), $html->_attributes);
    }

    /**
     * @covers Noce\Html::style
     */
    public function testStyle()
    {
        $html = new Html();
        $r = $html->style("name", "value");
        $this->assertSame($r, $html);
        $this->assertSame(array("style" => "name: value;"), $html->_attributes);
        
        $r = $html->style("name", "value");
        $this->assertSame($r, $html);
        $this->assertSame(array("style" => "name: value; name: value;"), $html->_attributes);
    }
    
    /**
     * @covers Noce\Html::styles
     */
    public function testStyles()
    {
        $html = new Html();
        $r = $html->styles(array("name1" => "value1", "name2" => "value2"));
        $this->assertSame($r, $html);
        $this->assertSame(array("style" => "name1: value1; name2: value2;"), $html->_attributes);
        
        $r = $html->styles(array("name1" => "value1"));
        $this->assertSame($r, $html);
        $this->assertSame(array("style" => "name1: value1; name2: value2; name1: value1;"), $html->_attributes);
    }
    
    /**
     * @covers Noce\Html::addClass
     */
    public function testAddClass()
    {
        $html = new Html();
        $r = $html->addClass("");
        $this->assertSame($r, $html);
        $this->assertSame(array("class" => ""), $html->_attributes);

        $r = $html->addClass("test");
        $this->assertSame($r, $html);
        $this->assertSame(array("class" => "test"), $html->_attributes);

        $r = $html->addClass("test");
        $this->assertSame($r, $html);
        $this->assertSame(array("class" => "test test"), $html->_attributes);
    }
    
    /**
     * @covers Noce\Html::close
     */
    public function testClose()
    {
        $html = new Html();
        $r = $html->close(Html::SELF_CLOSE);
        $this->assertSame($r, $html);
        $this->assertSame(Html::SELF_CLOSE, $html->_close);
        
        $r = $html->close(null);
        $this->assertSame($r, $html);
        $this->assertSame(null, $html->_close);
    }
    
    /**
     * @covers Noce\Html::calcClose
     */
    public function testCalcClose()
    {
        $html = new Html();
        $html->close(Html::NO_CLOSE);
        $this->assertSame(Html::NO_CLOSE, $html->calcClose());
        $html->close(Html::SELF_CLOSE);
        $this->assertSame(Html::SELF_CLOSE, $html->calcClose());
        $html->close(Html::CLOSE);
        $this->assertSame(Html::CLOSE, $html->calcClose());
        
        $html->close(Html::AUTO_CLOSE);
        $html->tag("test");
        $this->assertSame(Html::CLOSE, $html->calcClose());
        $html->tag("br");
        $this->assertSame(Html::SELF_CLOSE, $html->calcClose());
    }
    
    /**
     * @covers Noce\Html::p
     */
    public function testPWithString()
    {
        foreach (array("", null, "&", "<", ">", "'", '"', "あ", "a") as $s) {
            ob_start();
            Html::p($s);
            $printed = ob_get_clean();
            $this->assertSame(htmlspecialchars($s, ENT_QUOTES), $printed);
        }
    }

    /**
     * @covers Noce\Html::p
     */
    public function testPWithObject()
    {
        foreach (array("", null, "&", "<", ">", "'", '"', "あ", "a") as $s) {
            ob_start();
            Html::p(Html::h($s));
            $printed = ob_get_clean();
            $this->assertSame(htmlspecialchars($s, ENT_QUOTES), $printed);
        }
    }
    
    /**
     * @covers Noce\Html::aTag
     */
    public function testATag()
    {
        $this->assertEquals(
            Html::h()->tag("a"),
            Html::aTag()
        );
        
        $this->assertEquals(
            Html::h()->tag("a")->attr("href", "test.html"),
            Html::aTag("test.html"));
        
        $this->assertEquals(
            Html::h()->tag("a")->attrs(array("href" => "test.html", "target" => "_blank")),
            Html::aTag("test.html", array("target" => "_blank")));
        
        $this->assertEquals(
            Html::h()->tag("a")->attr("href", "test.html"),
            Html::aTag("test.html", array("target" => "_auto_blank")));
        $this->assertEquals(
            Html::h()->tag("a")->attrs(array("href" => "http://example.com/test.html", "target" => "_blank")),
            Html::aTag("http://example.com/test.html", array("target" => "_auto_blank")));
        $this->assertEquals(
            Html::h()->tag("a")->attrs(array("href" => "https://example.com/test.html", "target" => "_blank")),
            Html::aTag("https://example.com/test.html", array("target" => "_auto_blank")));
    }

    /**
     * @covers Noce\Html::optionTag
     */
    public function testOptionTag()
    {
        $this->assertEquals(
            new Html(),
            Html::optionTag(array()));
        
        $this->assertEquals(
            Html::h(Html::h("label1")->tag("option")->attr("value", "value1")),
            Html::optionTag(array("value1" => "label1")));
        
        $expected = new Html();
        $expected->append(Html::h("label1")->tag("option")->attr("value", "value1"));
        $expected->append(Html::h("label2")->tag("option")->attr("value", "value2"));
        $this->assertEquals(
            $expected,
            Html::optionTag(array("value1" => "label1", "value2" => "label2")));
        
        $expected = new Html();
        $expected->append(Html::h("label1")->tag("option")->attrs(array("value" => "value1", "selected" => "selected")));
        $expected->append(Html::h("label2")->tag("option")->attrs(array("value" => "value2")));
        $this->assertEquals(
            $expected,
            Html::optionTag(array("value1" => "label1", "value2" => "label2"), "value1"));

        $expected = new Html();
        $expected->append(Html::h("label1")->tag("option")->attrs(array("value" => "value1")));
        $expected->append(Html::h("label2")->tag("option")->attrs(array("value" => "value2", "selected" => "selected")));
        $this->assertEquals(
            $expected,
            Html::optionTag(array("value1" => "label1", "value2" => "label2"), "value2"));

        $expected = new Html();
        $expected->append(Html::h("label1")->tag("option")->attrs(array("value" => "value1", "selected" => "selected")));
        $expected->append(Html::h("label2")->tag("option")->attrs(array("value" => "value2", "selected" => "selected")));
        $this->assertEquals(
            $expected,
            Html::optionTag(array("value1" => "label1", "value2" => "label2"), array("value1", "value2", "value3")));
    }
    
    /**
     * @covers Noce\Html::nl2br
     */
    public function testNl2Br()
    {
        $html = Html::h("\rhello\nworld\rthis is\r\n a test.\n");
        $html->nl2br();
        $this->assertEquals(
            "<br />\rhello<br />\nworld<br />\rthis is<br />\r\n a test.<br />\n",
            $html->getEscapedHtml()
        );
    }
    
    /**
     * @covers Noce\Html::nl2br
     */
    public function testNl2BrEmpty()
    {
        $html = Html::h();
        $html->nl2br();
        $this->assertEquals(
            "",
            $html->getEscapedHtml()
        );
    }
    
    /**
     * @covers Noce\Html::nl2br
     */
    public function testNl2BrRecurse()
    {
        $html = Html::h("hello\nworld");
        $html->append(Html::h("\nthis is a test\n"));
        $html->nl2br();
        $this->assertEquals(
            "hello<br />\nworld<br />\nthis is a test<br />\n",
            $html->getEscapedHtml()
        );
    }
    
    /**
     * @covers ::p
     * @runInSeparateProcess
     */
    public function testShorthandP()
    {
        Noce\ShorthandLoader::load("Html::p");
        $this->expectOutputString(self::escaped);
        p(self::specials);
    }
}
