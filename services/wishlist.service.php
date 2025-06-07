<?php
require_once __DIR__ . '/../model/wishlist.model.php';

class WishlistService
{
    private $wishlistModel;

    public function __construct($db)
    {
        $this->wishlistModel = new Wishlist($db);
    }

    public function addToWishlist($customer_id, $product_id)
    {
        return $this->wishlistModel->add($customer_id, $product_id);
    }

    public function removeFromWishlist($customer_id, $product_id)
    {
        return $this->wishlistModel->remove($customer_id, $product_id);
    }

    public function getWishlistByCustomer($customer_id)
    {
        return $this->wishlistModel->getByCustomer($customer_id);
    }
    
    public function getWishlistCount($customer_id)
    {
        return $this->wishlistModel->countByCustomer($customer_id);
    }
}
?>