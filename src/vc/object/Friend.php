<?php
namespace vc\object;

/*
 * Accepted-state:
 *  Denied/Deleted -> Entry deleted
 *     0 = not accepted yet
 *     1 = accepted
 */
class Friend
{
    const FILTER_ALL = 'FILTER_ALL';
    const FILTER_CONFIRMED = 'FILTER_CONFIRMED';
    const FILTER_TO_CONFIRM = 'FILTER_TO_CONFIRM';
    const FILTER_WAIT_FOR_CONFIRM = 'FILTER_WAIT_FOR_CONFIRM';
    
    public $friendid;
}
