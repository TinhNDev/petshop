<?php
require_once __DIR__ . '/../model/categories.model.php';

class CategoriesService
{
    private $categoriesModel;

    public function __construct($db)
    {
        $this->categoriesModel = new Categories($db);
    }

    public function getAllCategories()
    {
        return $this->categoriesModel->findAll();
    }

    public function getCategory($id)
    {
        return $this->categoriesModel->findOne($id);
    }

    public function createCategory($data)
    {
        return $this->categoriesModel->create(
            $data['category_name'],
            $data['description']
        );
    }

    public function updateCategory($id, $data)
    {
        return $this->categoriesModel->update(
            $id,
            $data['category_name'],
            $data['description']
        );
    }

    public function getProductsByCategory($category_id)
    {
        return $this->categoriesModel->findProductsByCategory($category_id);
    }
}