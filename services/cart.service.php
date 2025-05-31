<?php
require_once __DIR__ . '/../model/cart.model.php';

class CartService
{
    private $cartModel;

    public function __construct($db)
    {
        $this->cartModel = new Cart($db);
    }

    public function getOrCreateCartId($customer_id)
    {
        $cart = $this->cartModel->getCartWithItemsByCustomer($customer_id);
        if ($cart && isset($cart['cart_id'])) {
            return $cart;
        } else {
            $newCart = $this->cartModel->create($customer_id);
            return $newCart ? $newCart['cart_id'] : null;
        }
    }

    public function addToCart($customer_id, $product_id, $quantity)
    {
        $cart_id = $this->getOrCreateCartId($customer_id);
        if ($cart_id) {
            return $this->cartModel->addToCart($cart_id['cart_id'], $product_id, $quantity);
        }
        return false;
    }

    public function removeFromCart($cart_item_id, $product_id)
    {
        return $this->cartModel->removeFromCart($cart_item_id, $product_id);
    }
}