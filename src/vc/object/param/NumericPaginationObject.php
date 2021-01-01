<?php
namespace vc\object\param;

class NumericPaginationObject extends AbstractPaginationObject
{
    // Use an unequal number to center the current item
    const MAX_ITEMS = 7;

    private $currentIndex;
    private $totalCount;
    private $itemsPerPage;

    public function __construct($urlPattern, $currentIndex, $totalCount, $itemsPerPage = 1)
    {
        $this->currentIndex = $currentIndex;
        $this->totalCount = $totalCount;
        $this->itemsPerPage = $itemsPerPage;

        $this->setUrlPattern($urlPattern);
    }

    public function getCurrentIndex()
    {
        return $this->currentIndex;
    }

    public function getCurrent()
    {
        return $this->getUrl(array(
            '%INDEX%' => $this->currentIndex,
            '%LIMIT%' => $this->itemsPerPage
        ));
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getFirst()
    {
        if ($this->currentIndex === 0) {
            return null;
        } else {
            return $this->getUrl(array(
                '%INDEX%' => 0,
                '%LIMIT%' => $this->itemsPerPage
            ));
        }
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getPrev()
    {
        if ($this->currentIndex === 0) {
            return null;
        } else {
            return $this->getUrl(array(
                '%INDEX%' => max(0, $this->currentIndex - $this->itemsPerPage),
                '%LIMIT%' => $this->itemsPerPage
            ));
        }
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getNext()
    {
        if ($this->currentIndex + $this->itemsPerPage >= $this->totalCount) {
            return null;
        } else {
            return $this->getUrl(array(
                '%INDEX%' => $this->currentIndex + $this->itemsPerPage,
                '%LIMIT%' => $this->itemsPerPage
            ));
        }
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getLast()
    {
        if ($this->currentIndex + $this->itemsPerPage >= $this->totalCount) {
            return null;
        } else {
            $totalCount = $this->totalCount - 1;
            return $this->getUrl(array(
                '%INDEX%' => $totalCount - ($totalCount % $this->itemsPerPage),
                '%LIMIT%' => $this->itemsPerPage
            ));
        }
    }

    /**
     * Return an associative array with the url in the index and the caption in the value.
     */
    public function getItems()
    {
        $items = array();

        // Jump half the items (e.g.) back to the default
        $defaultI = $this->currentIndex - ($this->itemsPerPage * floor(self::MAX_ITEMS / 2));
        // But don't go back more than the maximum for the first item
        $last = $this->totalCount - ($this->totalCount % $this->itemsPerPage);
        $maxI = $last - ((self::MAX_ITEMS - 1) * $this->itemsPerPage);

        for ($i = max(0, min($defaultI, $maxI));
             $i < $this->totalCount && count($items) < self::MAX_ITEMS;
             $i += $this->itemsPerPage) {
            $url = $this->getUrl(array(
                '%INDEX%' => $i,
                '%LIMIT%' => $this->itemsPerPage
            ));
            $items[$url] = round($i / $this->itemsPerPage) + 1;
        }
        return $items;
    }

    public function setUrlPattern($urlPattern)
    {
        $urlPattern = str_replace(
            array('%25INDEX%25', '%25LIMIT%25'),
            array('%INDEX%', '%LIMIT%'),
            $urlPattern
        );
        parent::setUrlPattern($urlPattern);
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
