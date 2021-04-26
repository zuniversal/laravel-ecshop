<?php
// 3-18 
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Product extends Facade
{
    protected static function getFacadeAccessor() {// 
      return 'product';
    }
}
