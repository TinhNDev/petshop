<?php
require_once __DIR__ . '/../services/promotion.service.php';

class PromotionController
{
    private $service;
    public function __construct()
    {
        $this->service = new PromotionService();
    }

    public function create($data)
    {
        $result = $this->service->createPromotion($data);
        return [
            'status' => $result ? 201 : 400,
            'data' => ['success' => $result]
        ];
    }
    public function update($id, $data)
    {
        $result = $this->service->updatePromotion($id, $data);
        return [
            'status' => $result ? 200 : 400,
            'data' => ['success' => $result]
        ];
    }
    public function getDetail($id)
    {
        $result = $this->service->getDetailPromotion($id);
        return [
            'status' => $result ? 200 : 404,
            'data' => $result ? $result : ['message' => 'Promotion not foundsss']
        ];
    }

    public function getAll()
    {
        $result = $this->service->getAllPromotions();
        return [
            'status' => 200,
            'data' => $result
        ];
    }

    public function findByCode($code)
    {
        $result = $this->service->findByCode($code);
        return [
            'status' => $result ? 200 : 404,
            'data' => $result ? $result : ['message' => 'Promotion not found']
        ];
    }
}
?>