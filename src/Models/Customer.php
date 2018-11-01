<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 01.11.18
 * Time: 13:34
 */

namespace Forbytes\Test\Models;

use DateTime;
use Forbytes\Test\DataLayer;

/**
 * Class Customer
 * @package Fobytes\Test
 * @property string email
 * @property DateTime createdAt
 */
class Customer
{
    public $email;

    public $createdAt;

    public function __construct($email, $createdAt)
    {
        $this->email = $email;
        $this->createdAt = $createdAt;
    }

    /**
     * @return bool
     */
    public function isNewCustomer()
    {
        return $this->createdAt < (new DateTime())->modify('-1 day');
    }

    /**
     * @return bool
     */
    public function hasNoOrders()
    {
        /** @var Order[] $orders */
        $orders = DataLayer::ListOrders();

        /** @var Order $order */
        foreach ($orders as $order) {
            if ($order->customerEmail === $this->email) {
                return false;
            }
        }

        return true;
    }
}
