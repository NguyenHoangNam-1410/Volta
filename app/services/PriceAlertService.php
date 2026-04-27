<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../dao/PriceAlertDAO.php';

class PriceAlertService
{
    private PriceAlertDAO $priceAlertDAO;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->priceAlertDAO = new PriceAlertDAO($pdo);
    }

    public function getAlerts(int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        if (!$startDate || !$endDate) {
            $endDate = date('Y-m-d 23:59:59');
            $startDate = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
        }
        
        $effectiveDays = max(1, (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400));
        
        $products = $this->priceAlertDAO->getProductAnalytics($startDate, $endDate);
        
        $alerts = [];
        $summary = [
            'increase'  => 0,
            'decrease'  => 0,
            'clearance' => 0,
            'review'    => 0,
            'total'     => 0
        ];
        
        $now = time();

        foreach ($products as $p) {
            $soldQty = (int) $p['sold_quantity'];
            $stock = (int) $p['stock'];
            $totalOrders = (int) $p['total_orders'];
            $cancelledOrders = (int) $p['cancelled_orders'];
            
            $productAgeDays = max(1, (int) floor(($now - strtotime($p['created_at'])) / 86400));
            $sellThroughRate = $soldQty / max(1, $soldQty + $stock);
            $demandVelocity = $soldQty / $effectiveDays;
            $stockDaysRemaining = $stock / max(0.1, $demandVelocity);
            $cancelRate = $totalOrders > 0 ? $cancelledOrders / $totalOrders : 0;
            
            $alertType = null;
            $severity = 0;
            $recommendedAction = '';
            $suggestedPrice = null;
            $currentPrice = (float) $p['price'];
            
            // 1. Fast mover, low stock -> Increase
            if ($stockDaysRemaining < 7 && $demandVelocity > 1) {
                $alertType = 'increase';
                $severity = $stockDaysRemaining < 3 ? 5 : 4;
                $recommendedAction = 'High demand with low stock. Increase price to maximize margin.';
                $suggestedPrice = round($currentPrice * 1.10, 2); // +10%
            } 
            // 2. High demand -> Increase
            elseif ($sellThroughRate > 0.7) {
                $alertType = 'increase';
                $severity = $sellThroughRate > 0.85 ? 4 : 3;
                $recommendedAction = 'High sell-through rate. Consider a small price bump.';
                $suggestedPrice = round($currentPrice * 1.05, 2); // +5%
            }
            // 3. High cancel rate -> Review
            elseif ($cancelRate > 0.3 && $totalOrders > 3) {
                $alertType = 'review';
                $severity = $cancelRate > 0.5 ? 5 : 4;
                $recommendedAction = 'High cancellation rate. Review pricing competitiveness or product description.';
            }
            // 4. Dead stock -> Clearance
            elseif ($soldQty === 0 && $stock > 0 && $productAgeDays > 14) {
                $alertType = 'clearance';
                $severity = $productAgeDays > 30 ? 5 : 4;
                $recommendedAction = 'No sales recently. Heavy discount needed to clear inventory.';
                $suggestedPrice = round($currentPrice * 0.70, 2); // -30%
            }
            // 5. Slow mover -> Decrease
            elseif ($sellThroughRate < 0.15 && $stock > 10) {
                $alertType = 'decrease';
                $severity = $sellThroughRate < 0.05 ? 4 : 3;
                $recommendedAction = 'Slow moving inventory. Recommend a promotional discount.';
                $suggestedPrice = round($currentPrice * 0.85, 2); // -15%
            }
            
            if ($alertType) {
                $summary[$alertType]++;
                $summary['total']++;
                
                $alerts[] = [
                    'product_id' => (int) $p['product_id'],
                    'name' => $p['name'],
                    'price' => $currentPrice,
                    'stock' => $stock,
                    'alert_type' => $alertType,
                    'severity' => $severity,
                    'metrics' => [
                        'sold_quantity' => $soldQty,
                        'sell_through_rate' => round($sellThroughRate, 3),
                        'demand_velocity' => round($demandVelocity, 2),
                        'stock_days_remaining' => round($stockDaysRemaining, 1),
                        'cancel_rate' => round($cancelRate, 3)
                    ],
                    'recommended_action' => $recommendedAction,
                    'suggested_price' => $suggestedPrice
                ];
            }
        }
        
        // Sort alerts by severity DESC, then by demand velocity DESC
        usort($alerts, function($a, $b) {
            if ($a['severity'] !== $b['severity']) {
                return $b['severity'] - $a['severity'];
            }
            return $b['metrics']['demand_velocity'] <=> $a['metrics']['demand_velocity'];
        });
        
        return [
            'summary' => $summary,
            'alerts' => $alerts,
            'effective_filter' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $effectiveDays
            ]
        ];
    }
}
