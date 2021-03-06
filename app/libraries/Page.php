<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/12
 * Time: 16:52
 */

namespace app\libraries;

/**
 * Class Page
 * @package app\libraries
 * @property int $current
 * @property int $before
 * @property int $next
 * @property int $totalPages
 * @property int $pageSize
 * @property array $items
 */
class Page
{
    private $current;
    private $items;
    private $totalItems;
    private $pageSize;

    public function __construct($current = 1, $pageSize = 2)
    {
        $this->current = intval($current);
        $this->pageSize = intval($pageSize);
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @return int
     */
    public function getBefore(): int
    {
        if ($this->current === 1) {
            return $this->current;
        } else {
            return $this->current - 1;
        }
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        if ($this->totalItems % $this->pageSize === 0) {
            return intval($this->totalItems / $this->pageSize);
        } else {
            return intval($this->totalItems / $this->pageSize) + 1;
        }
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getNext()
    {
        if ($this->current === $this->getTotalPages()) {
            return $this->current;
        } else {
            return $this->current + 1;
        }
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = intval($totalItems);
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getIndex()
    {
        return ($this->current - 1) * $this->pageSize;
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function toArray()
    {
        return array(
            'items' => $this->getItems(),
            'totalItems' => $this->getTotalItems(),
            'totalPage' => $this->getTotalPages(),
            'pageSize' => $this->getPageSize(),
            'current' => $this->getCurrent(),
            'next' => $this->getNext(),
            'before' => $this->getBefore()
        );
    }
}