<?php
require_once 'vendor/autoload.php';

use Timoran\Otynirm\Otynirm;
Otynirm::getInstance('mysqli', 'localhost', 'root', '', 'robotwithme')->loadEntitiesByFolder(__DIR__ .'/model');
$orm = Otynirm::getInstance();
include_once 'model/dao/UserDao.php';
include_once 'model/dao/ProductDao.php';
$userDao = new UserDao();
$productDao = new ProductDao();
$users = $userDao->findAll();
$product = new Productlist('jojo', 'jojo desc');

$user = $users[0];
// $user->setFirstname('Arthur');
// $userDao->update($user);
d($users);
