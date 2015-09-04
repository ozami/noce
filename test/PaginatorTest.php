<?php
use Noce\Paginator;

class PaginatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Noce\Paginator::__construct
     */
    public function testNoItem()
    {
        $pages = new Paginator(0, 0, 10);
        $this->assertSame(null, $pages->current_page);
        $this->assertSame(null, $pages->current_position);
        $this->assertSame(null, $pages->current_page_first_item);
        $this->assertSame(null, $pages->current_page_last_item);
        $this->assertSame(null, $pages->first_page);
        $this->assertSame(null, $pages->first_page_position);
        $this->assertSame(null, $pages->last_page);
        $this->assertSame(null, $pages->last_page_position);
        $this->assertSame(null, $pages->window_first_page);
        $this->assertSame(null, $pages->window_first_page_position);
        $this->assertSame(null, $pages->window_last_page);
        $this->assertSame(null, $pages->window_last_page_position);
        $this->assertSame(false, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(null, $pages->previous_page);
        $this->assertSame(null, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(0, $pages->total_item_count);
        $this->assertSame(0, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(PHP_INT_MAX, $pages->pages_per_window);
        $this->assertSame(0, count($pages));
        $a = array();
        foreach ($pages as $page) {
            $a[] = $page;
        }
        $this->assertSame(array(), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testOneItem()
    {
        $pages = new Paginator(1, 0, 10);
        $this->assertSame(1, $pages->current_page);
        $this->assertSame(0, $pages->current_position);
        $this->assertSame(1, $pages->current_page_first_item);
        $this->assertSame(1, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(1, $pages->last_page);
        $this->assertSame(0, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(1, $pages->window_last_page);
        $this->assertSame(0, $pages->window_last_page_position);
        $this->assertSame(false, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(null, $pages->previous_page);
        $this->assertSame(null, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(1, $pages->total_item_count);
        $this->assertSame(1, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(PHP_INT_MAX, $pages->pages_per_window);
        $this->assertSame(1, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testFirstPage()
    {
        $pages = new Paginator(30, 0, 10);
        $this->assertSame(1, $pages->current_page);
        $this->assertSame(0, $pages->current_position);
        $this->assertSame(1, $pages->current_page_first_item);
        $this->assertSame(10, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(3, $pages->last_page);
        $this->assertSame(20, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(3, $pages->window_last_page);
        $this->assertSame(20, $pages->window_last_page_position);
        $this->assertSame(false, $pages->has_previous_page);
        $this->assertSame(true, $pages->has_next_page);
        $this->assertSame(null, $pages->previous_page);
        $this->assertSame(null, $pages->previous_page_position);
        $this->assertSame(2, $pages->next_page);
        $this->assertSame(10, $pages->next_page_position);
        $this->assertSame(30, $pages->total_item_count);
        $this->assertSame(3, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(PHP_INT_MAX, $pages->pages_per_window);
        $this->assertSame(3, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0), array(2, 10), array(3, 20)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testLastPage()
    {
        $pages = new Paginator(30, 20, 10);
        $this->assertSame(3, $pages->current_page);
        $this->assertSame(20, $pages->current_position);
        $this->assertSame(21, $pages->current_page_first_item);
        $this->assertSame(30, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(3, $pages->last_page);
        $this->assertSame(20, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(3, $pages->window_last_page);
        $this->assertSame(20, $pages->window_last_page_position);
        $this->assertSame(true, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(2, $pages->previous_page);
        $this->assertSame(10, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(30, $pages->total_item_count);
        $this->assertSame(3, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(PHP_INT_MAX, $pages->pages_per_window);
        $this->assertSame(3, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0), array(2, 10), array(3, 20)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testLastPageWithOffset()
    {
        $pages = new Paginator(30, 21, 10);
        $this->assertSame(4, $pages->current_page);
        $this->assertSame(21, $pages->current_position);
        $this->assertSame(22, $pages->current_page_first_item);
        $this->assertSame(30, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(4, $pages->last_page);
        $this->assertSame(21, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(4, $pages->window_last_page);
        $this->assertSame(21, $pages->window_last_page_position);
        $this->assertSame(true, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(3, $pages->previous_page);
        $this->assertSame(11, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(30, $pages->total_item_count);
        $this->assertSame(4, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(PHP_INT_MAX, $pages->pages_per_window);
        $this->assertSame(4, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0), array(2, 1), array(3, 11), array(4, 21)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testFirstPageWithWindow()
    {
        $pages = new Paginator(1000, 0, 10, 6);
        $this->assertSame(1, $pages->current_page);
        $this->assertSame(0, $pages->current_position);
        $this->assertSame(1, $pages->current_page_first_item);
        $this->assertSame(10, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(100, $pages->last_page);
        $this->assertSame(990, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(6, $pages->window_last_page);
        $this->assertSame(50, $pages->window_last_page_position);
        $this->assertSame(false, $pages->has_previous_page);
        $this->assertSame(true, $pages->has_next_page);
        $this->assertSame(null, $pages->previous_page);
        $this->assertSame(null, $pages->previous_page_position);
        $this->assertSame(2, $pages->next_page);
        $this->assertSame(10, $pages->next_page_position);
        $this->assertSame(1000, $pages->total_item_count);
        $this->assertSame(100, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(6, $pages->pages_per_window);
        $this->assertSame(6, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0), array(2, 10), array(3, 20), array(4, 30), array(5, 40), array(6, 50)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testLastPageWithWindow()
    {
        $pages = new Paginator(1000, 990, 10, 6);
        $this->assertSame(100, $pages->current_page);
        $this->assertSame(990, $pages->current_position);
        $this->assertSame(991, $pages->current_page_first_item);
        $this->assertSame(1000, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(100, $pages->last_page);
        $this->assertSame(990, $pages->last_page_position);
        $this->assertSame(95, $pages->window_first_page);
        $this->assertSame(940, $pages->window_first_page_position);
        $this->assertSame(100, $pages->window_last_page);
        $this->assertSame(990, $pages->window_last_page_position);
        $this->assertSame(true, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(99, $pages->previous_page);
        $this->assertSame(980, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(1000, $pages->total_item_count);
        $this->assertSame(100, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(6, $pages->pages_per_window);
        $this->assertSame(6, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(95, 940), array(96, 950), array(97, 960), array(98, 970), array(99, 980), array(100, 990)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testNegativePosition()
    {
        $pages = new Paginator(1000, -1, 10, 6);
        $this->assertSame(1, $pages->current_page);
        $this->assertSame(0, $pages->current_position);
        $this->assertSame(1, $pages->current_page_first_item);
        $this->assertSame(10, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(100, $pages->last_page);
        $this->assertSame(990, $pages->last_page_position);
        $this->assertSame(1, $pages->window_first_page);
        $this->assertSame(0, $pages->window_first_page_position);
        $this->assertSame(6, $pages->window_last_page);
        $this->assertSame(50, $pages->window_last_page_position);
        $this->assertSame(false, $pages->has_previous_page);
        $this->assertSame(true, $pages->has_next_page);
        $this->assertSame(null, $pages->previous_page);
        $this->assertSame(null, $pages->previous_page_position);
        $this->assertSame(2, $pages->next_page);
        $this->assertSame(10, $pages->next_page_position);
        $this->assertSame(1000, $pages->total_item_count);
        $this->assertSame(100, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(6, $pages->pages_per_window);
        $this->assertSame(6, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(1, 0), array(2, 10), array(3, 20), array(4, 30), array(5, 40), array(6, 50)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testOverrun()
    {
        $pages = new Paginator(1000, 1000, 10, 6);
        $this->assertSame(101, $pages->current_page);
        $this->assertSame(999, $pages->current_position);
        $this->assertSame(1000, $pages->current_page_first_item);
        $this->assertSame(1000, $pages->current_page_last_item);
        $this->assertSame(1, $pages->first_page);
        $this->assertSame(0, $pages->first_page_position);
        $this->assertSame(101, $pages->last_page);
        $this->assertSame(999, $pages->last_page_position);
        $this->assertSame(96, $pages->window_first_page);
        $this->assertSame(949, $pages->window_first_page_position);
        $this->assertSame(101, $pages->window_last_page);
        $this->assertSame(999, $pages->window_last_page_position);
        $this->assertSame(true, $pages->has_previous_page);
        $this->assertSame(false, $pages->has_next_page);
        $this->assertSame(100, $pages->previous_page);
        $this->assertSame(989, $pages->previous_page_position);
        $this->assertSame(null, $pages->next_page);
        $this->assertSame(null, $pages->next_page_position);
        $this->assertSame(1000, $pages->total_item_count);
        $this->assertSame(101, $pages->total_page_count);
        $this->assertSame(10, $pages->items_per_page);
        $this->assertSame(6, $pages->pages_per_window);
        $this->assertSame(6, count($pages));
        $a = array();
        foreach ($pages as $page => $position) {
            $a[] = array($page, $position);
        }
        $this->assertSame(array(array(96, 949), array(97, 959), array(98, 969), array(99, 979), array(100, 989), array(101, 999)), $a);
    }

    /**
     * @covers Noce\Paginator::__construct
     */
    public function testInvalidArgs()
    {
        try {
            $pages = new Paginator(-1, 0, 10);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }

        try {
            $pages = new Paginator(0, 0, 0);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }

        try {
            $pages = new Paginator(0, 0, 10, -1);
            $this->fail();
        }
        catch (InvalidArgumentException $e) {
        }
    }
    
    public function test()
    {
        
    }
}
