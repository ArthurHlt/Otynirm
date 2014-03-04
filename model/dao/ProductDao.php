<?php
use Timoran\Otynirm\dao\AbstractDao;
class ProductDao extends AbstractDao
{
    /* (non-PHPdoc)
     * @see AbstractDao::__construct()
    */
    public function __construct()
    {
        parent::__construct('Productlist');

    }
}
