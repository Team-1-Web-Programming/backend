<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Donation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DonationAnalyticsController extends Controller
{
    // Summary of donations
    public function getSummary()
    {
        $userId = Auth::id();

        $totalDonations = Donation::where('donor_id', $userId)->count();
        $totalProducts = Donation::where('donor_id', $userId)->distinct('donation_product_id')->count();
        $totalItem = Donation::where('donor_id', $userId)->sum('amount');

        $summary = [
            'total_donations' => $totalDonations,
            'total_products' => $totalProducts,
            'total_item' => $totalItem,
        ];

        return response()->json($summary, 200);
    }

    // Analysis over time
    public function getAnalysis(Request $request)
    {
        $userId = Auth::id();
        
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date')) : Carbon::now()->subYear();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date')) : Carbon::now();
        $groupBy = $request->query('group_by', 'status');

        $validGroupBys = ['status', 'donation_product_id', 'donor_id', 'donee_id'];
        if (!in_array($groupBy, $validGroupBys)) {
            return response()->json(['error' => 'Invalid group_by field'], 400);
        }

        $analysis = Donation::select(
                DB::raw('DATE(created_at) as date'), 
                $groupBy, 
                DB::raw('COUNT(*) as count'), 
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('donor_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'), $groupBy)
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) use ($groupBy) {
                return [
                    'date' => $item->date,
                    'category' => $item->{$groupBy},
                    'count' => $item->count,
                    'total_amount' => $item->total_amount,
                ];
            });

        return response()->json($analysis, 200);
    }

    // Product distribution for donations
    public function getProductDistribution()
    {
        $userId = Auth::id();

        $distribution = Donation::select('donation_products.title as category', DB::raw('COUNT(donations.id) as count'))
            ->join('donation_products', 'donations.donation_product_id', '=', 'donation_products.id')
            ->where('donations.donor_id', $userId)
            ->groupBy('donation_products.title')
            ->get()
            ->map(function ($product) {
                return [
                    'category' => $product->category,
                    'count' => $product->count,
                ];
            });

        return response()->json($distribution, 200);
    }

    // Comparison metrics for donations
    public function getComparison(Request $request)
    {
        $userId = Auth::id();
        
        $period = $request->query('period', 'week');
        $comparisonRange = $request->query('comparison_range', 1);
        
        if ($period === 'month') {
            $currentStart = Carbon::now()->startOfMonth();
            $previousStart = Carbon::now()->subMonths($comparisonRange)->startOfMonth();
            $previousEnd = Carbon::now()->subMonths($comparisonRange)->endOfMonth();
        } elseif ($period === 'week') {
            $currentStart = Carbon::now()->startOfWeek();
            $previousStart = Carbon::now()->subWeeks($comparisonRange)->startOfWeek();
            $previousEnd = Carbon::now()->subWeeks($comparisonRange)->endOfWeek();
        } else {
            return response()->json(['error' => 'Invalid period type'], 400);
        }
    
        $currentTotal = Donation::where('donor_id', $userId)
            ->whereBetween('created_at', [$currentStart, Carbon::now()])
            ->sum('amount');
    
        $previousTotal = Donation::where('donor_id', $userId)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('amount');
    
        $percentageChange = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : 100;
    
        $comparisonMetrics = [
            'current_period' => $currentTotal,
            'previous_period' => $previousTotal,
            'percentage_change' => $percentageChange,
        ];
    
        return response()->json($comparisonMetrics, 200);
    }    
}
