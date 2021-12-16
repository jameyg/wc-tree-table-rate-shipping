<?php
namespace TrsVendors\Dgm\Shengine\Model;


class Customer
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    private $id;
}