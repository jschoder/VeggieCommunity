<?php
namespace vc\object;

class TicketMessage
{
    public static $primaryKey = array('id');

    public static $fields = array(
        'id' => array(
            'type' => 'integer',
            'dbmapping' => 'id',
            'autoincrement' => true ),
        'ticketId' => array(
            'type' => 'integer',
            'dbmapping' => 'ticket_id'),
        'byAdmin' => array(
            'type' => 'integer',
            'dbmapping' => 'by_admin',
            'default' => 0),
        'email' => array(
            'type' => 'text',
            'dbmapping' => 'email',
            'default' => null ),
        'body' => array(
            'type' => 'text',
            'dbmapping' => 'body' ),
        'createdAt' => array(
            'type' => 'datetime',
            'dbmapping' => 'created_at',
            'default' => '{{CURRENT_TIME}}' )
    );

    public $id;
    public $ticketId;
    public $byAdmin;
    public $email;
    public $body;
    public $createdAt;
}
