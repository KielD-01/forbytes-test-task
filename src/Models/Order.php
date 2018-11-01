<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 01.11.18
 * Time: 13:35
 */

namespace Forbytes\Test\Models;


use DateTime;

/**
 * Class Order
 * @package Fobytes\Test
 * @property string customerEmail
 * @property DateTime createdAt
 */
class Order
{
    public $customerEmail;

    public $createdAt;

    public function __construct($customerEmail, $createdAt)
    {
        $this->customerEmail = $customerEmail;
        $this->createdAt = $createdAt;
    }


}
