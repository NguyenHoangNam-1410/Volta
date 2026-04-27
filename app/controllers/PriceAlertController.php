<?php
require_once __DIR__ . '/../helpers/Auth.php';
require_once __DIR__ . '/../helpers/ApiResponse.php';
require_once __DIR__ . '/../services/PriceAlertService.php';

class PriceAlertController
{
    private PriceAlertService $priceAlertService;

    public function __construct()
    {
        $this->priceAlertService = new PriceAlertService();
    }

    /**
     * GET /api/admin/price-alerts
     * Query Params: start_date, end_date, days
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $days = isset($_GET['days']) ? (int) $_GET['days'] : 30;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $result = $this->priceAlertService->getAlerts($days, $startDate, $endDate);

        ApiResponse::success($result);
    }
}
