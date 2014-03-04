<?php

class Productlist
{
    private $imgbase64;
    private $title;
    private $description;
    private $price;
    private $number;

    public function __construct($title=null, $description=null)
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function getImgbase64()
    {
        return $this->imgbase64;
    }
    public function setImgbase64($image64)
    {
        $this->imgbase64 = $image64;
        return $this;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
    public function getNumber()
    {
        return $this->number;
    }
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }



}
