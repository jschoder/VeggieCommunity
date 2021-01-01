<?php
namespace vc\object\param;

class MonthPaginationObject extends AbstractPaginationObject
{
    const MONTH_BEFORE = 1;
    const MONTH_AFTER = 5;

    private $currentYear;
    private $currentMonth;
    private $minYear;
    private $maxYear;

    public function __construct($urlPattern, $currentYear, $currentMonth)
    {
        $this->currentYear = $currentYear;
        $this->currentMonth = $currentMonth;
        $this->minYear = date('Y') - 1;
        $this->maxYear = date('Y') + 2;

        $this->setUrlPattern($urlPattern);
    }

    public function getCurrent()
    {
        return $this->getUrl(array(
            '%YEAR%' => $this->currentYear,
            '%MONTH%' => $this->currentMonth
        ));
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getFirst()
    {
        return false;
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getPrev()
    {
        $year = $this->currentYear;
        $month = $this->currentMonth - 1;
        if ($month < 1) {
            $year--;
            $month = 12;
        }

        if ($year < $this->minYear) {
            return null;
        } else {
            return $this->getUrl(array(
                '%YEAR%' => $year,
                '%MONTH%' =>  $month
            ));
        }
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getNext()
    {
        $year = $this->currentYear;
        $month = $this->currentMonth + 1;
        if ($month > 12) {
            $year++;
            $month = 1;
        }

        if ($year > $this->maxYear) {
            return null;
        } else {
            return $this->getUrl(array(
                '%YEAR%' => $year,
                '%MONTH%' =>  $month
            ));
        }
    }

    /**
     * Return false for non-existent button and null for empty button.
     */
    public function getLast()
    {
        return false;
    }

    /**
     * Return an associative array with the url in the index and the caption in the value.
     */
    public function getItems()
    {
        $items = array();

        $month = $this->currentMonth - self::MONTH_BEFORE;
        $year = $this->currentYear;

        if ($month < 1) {
            $month += 12;
            $year--;
        }

        for ($i = 0; $i < self::MONTH_BEFORE + 1 + self::MONTH_AFTER; $i++) {
            if ($year >= $this->minYear && $year <= $this->maxYear) {
                $url = $this->getUrl(array(
                    '%YEAR%' => $year,
                    '%MONTH%' => $month
                ));
                $items[$url] = $month . '/' . $year;
            }

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        return $items;
    }

    public function setUrlPattern($urlPattern)
    {
        $urlPattern = str_replace(
            array('%25YEAR%25', '%25MONT%25'),
            array('%YEAR%', '%MONTH%'),
            $urlPattern
        );
        parent::setUrlPattern($urlPattern);
    }

    public function setMinYear($minYear)
    {
        $this->minYear = $minYear;
    }

    public function setMaxYear($maxYear)
    {
        $this->maxYear = $maxYear;
    }
}
