<?php
namespace Noce;

/**
 *
 * position starts from zero, item and page starts from one.
 */
class Paginator implements \Iterator, \Countable
{
    public $current_page;
    public $current_position;
    public $current_page_first_item;
    public $current_page_last_item;
    public $first_page;
    public $first_page_position;
    public $last_page;
    public $last_page_position;
    public $window_first_page;
    public $window_first_page_position;
    public $window_last_page;
    public $window_last_page_position;
    public $has_previous_page = false;
    public $has_next_page = false;
    public $previous_page;
    public $previous_page_position;
    public $next_page;
    public $next_page_position;
    public $total_item_count;
    public $total_page_count = 0;
    public $items_per_page;
    public $pages_per_window;
    public $_iterator;

    public function __construct($total_item_count, $current_position, $items_per_page, $pages_per_window = PHP_INT_MAX)
    {
        if ($total_item_count < 0) {
            throw new \InvalidArgumentException("total_item_count");
        }
        if ($items_per_page < 1) {
            throw new \InvalidArgumentException("items_per_page");
        }
        if ($pages_per_window < 1) {
            throw new \InvalidArgumentException("pages_per_window");
        }
        $this->total_item_count = (int) $total_item_count;
        $this->items_per_page = (int) $items_per_page;
        $this->pages_per_window = (int) $pages_per_window;

        if ($total_item_count == 0) {
            return;
        }

        // current_position
        $this->current_position = max(0, min($this->total_item_count - 1, (int) $current_position));

        // current_page
        $this->current_page = (int) ceil($this->current_position / $this->items_per_page) + 1;

        // current_page_first_item
        $this->current_page_first_item = $this->current_position + 1;
        
        // current_page_last_item
        $this->current_page_last_item = min($this->total_item_count, $this->current_position + $this->items_per_page);

        // total_page_count
        $this->total_page_count = (int) ceil($this->total_item_count / $this->items_per_page);
        $this->total_page_count += (bool) ($this->current_position % $this->items_per_page);

        // first_page
        $this->first_page = 1;

        // last_page
        $this->last_page = $this->total_page_count;

        // window_first_page and window_last_page
        $half_window = (min($this->pages_per_window, $this->total_page_count) - 1) / 2;
        $this->window_first_page = $this->current_page - floor($half_window);
        $this->window_last_page = $this->current_page + ceil($half_window);
        if ($this->window_last_page > $this->total_page_count) {
            $this->window_first_page -= $this->window_last_page - $this->total_page_count;
            $this->window_last_page = $this->total_page_count;
        }
        if ($this->window_first_page < 1) {
            $this->window_last_page -= $this->window_first_page - 1;
            $this->window_first_page = 1;
        }
        $this->window_first_page = (int) $this->window_first_page;
        $this->window_last_page = (int) $this->window_last_page;

        // has_previous_page
        $this->has_previous_page = $this->current_position > 0;

        // previous_page
        if ($this->has_previous_page) {
            $this->previous_page = $this->current_page - 1;
        }

        // has_next_page
        $this->has_next_page = $this->current_page < $this->total_page_count;

        // next_page
        if ($this->has_next_page) {
            $this->next_page = $this->current_page + 1;
        }

        // page positions
        foreach (array("previous_page", "next_page", "window_first_page", "window_last_page", "first_page", "last_page") as $page) {
            $this->{$page . "_position"} = $this->getPagePosition($this->{$page});
        }

        $this->rewind();
    }


    public function getPagePosition($page)
    {
        if ($page === null) {
            return null;
        }
        if ($page == 1) {
            return 0;
        }
        $position = ($page - 1) * $this->items_per_page;
        $first_page_count = $this->current_position % $this->items_per_page;
        if ($first_page_count) {
            $position += $first_page_count - $this->items_per_page;
        }
        return $position;
    }

    //
    // Iterator interface
    //

    public function current()
    {
        if (!$this->valid()) {
            return false;
        }
        return $this->getPagePosition($this->_iterator);
    }

    public function key()
    {
        if (!$this->valid()) {
            return null;
        }
        return $this->_iterator;
    }

    public function next()
    {
        ++$this->_iterator;
    }

    public function rewind()
    {
        $this->_iterator = $this->window_first_page;
    }

    public function valid()
    {
        return (
            $this->total_page_count > 0 &&
            $this->_iterator >= $this->window_first_page &&
            $this->_iterator <= $this->window_last_page);
    }

    //
    // Countable interface
    //

    public function count()
    {
        return min(
            $this->total_page_count,
            $this->window_last_page - $this->window_first_page + 1);
    }
}
