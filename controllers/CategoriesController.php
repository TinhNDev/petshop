<?php
require_once __DIR__ . '/../services/categories.service.php';

class CategoriesController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new CategoriesService($db);
    }

    public function getAll()
    {
        $result = $this->service->getAllCategories();
        return [
            'status' => 200,
            'data' => $result
        ];
    }

    public function getOne($id)
    {
        $result = $this->service->getCategory($id);
        return [
            'status' => $result ? 200 : 404,
            'data' => $result ? $result : ['message' => 'Category not found']
        ];
    }

    public function create($data)
    {
        $result = $this->service->createCategory($data);
        return [
            'status' => $result ? 201 : 400,
            'data' => $result ? $result : ['message' => 'Create failed']
        ];
    }

    public function update($id, $data)
    {
        $result = $this->service->updateCategory($id, $data);
        return [
            'status' => $result ? 200 : 400,
            'data' => $result ? $result : ['message' => 'Update failed']
        ];
    }

    public function getProducts($category_id)
    {
        $result = $this->service->getProductsByCategory($category_id);
        return [
            'status' => 200,
            'data' => $result
        ];
    }
}