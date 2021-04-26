<?php
// 3-18 然后去 app/Providers/AppServiceProvider.php 把服务对象注册到服务容器里 
// 之后再创建一个 app/Facades\Product
namespace App;


class ProductService
{
    // Call to undefined method App\ProductService::getProduct()
    public function getProduct($id) 
    {
        echo '  getProduct  O(∩_∩)O~ $id </br>';// 
    }
}
