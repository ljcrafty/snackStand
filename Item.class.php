<?php
class Item
{
    private $name, $decription, $price, $quant, $imgName, $salePrice, $id, $quantCart;

    public function __constructorAttrs($attrs)
    {
        $this -> id            = $attrs['id'];
        $this -> name          = $attrs['name'];
        $this -> description   = $attrs['description'];
        $this -> price         = $attrs['price'];
        $this -> quant         = $attrs['quant'];
        $this -> imgName       = $attrs['imgName'];
        $this -> salePrice     = $attrs['salePrice'];
    }
    
    public function name()
    {
        return $this -> name;
    }

    public function descr()
    {
        return $this -> description;
    }

    public function price()
    {
        return $this -> price;
    }

    public function quant()
    {
        return $this -> quant;
    }

    public function imgName()
    {
        return $this -> imgName;
    }

    public function salePrice()
    {
        return $this -> salePrice;
    }

    public function id()
    {
        return $this -> id;
    }

    public function quantCart()
    {
        return $this -> quantCart;
    }

    public function attrs()
    {
        return array('id'   => $this -> id,
            'name'          => $this -> name,
            'description'   => $this -> description,
            'price'         => $this -> price,
            'quant'         => $this -> quant,
            'imgName'       => $this -> imgName,
            'salePrice'     => $this -> salePrice,
            'quantCart'     => $this -> quantCart
        );
    }
}
