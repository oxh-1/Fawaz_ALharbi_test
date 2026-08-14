<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RealEstateController extends Controller
{
    private array $properties = [
        [
            'id' => 'P1',
            'title' => 'KAFD Horizon Commercial Tower',
            'district' => 'King Abdullah Financial District (KAFD)',
            'city' => 'Riyadh',
            'type' => 'Commercial Tower',
            'sharePrice' => 500,
            'annualYield' => 12.4,
            'payoutFreq' => 'Quarterly',
            'occupancy' => 99.2,
            'leaseTerm' => '10 Years (Triple Net)',
            'targetAmount' => 45000000,
            'fundedAmount' => 39600000,
            'fundedPct' => 88,
            'description' => 'Prime Grade-A commercial headquarters in KAFD leased to multinational financial institutions with inflation-linked annual rent increases.'
        ],
        [
            'id' => 'P2',
            'title' => 'Al-Malqa Luxury Wellness Plaza',
            'district' => 'Al-Malqa District',
            'city' => 'Riyadh',
            'type' => 'Retail & Wellness Hub',
            'sharePrice' => 250,
            'annualYield' => 11.8,
            'payoutFreq' => 'Quarterly',
            'occupancy' => 95.0,
            'leaseTerm' => '7 Years',
            'targetAmount' => 28000000,
            'fundedAmount' => 25200000,
            'fundedPct' => 90,
            'description' => 'Boutique lifestyle and wellness commercial plaza featuring anchor fitness centers and high-end dining brands.'
        ],
        [
            'id' => 'P3',
            'title' => 'Red Sea Gateway Logistics Park',
            'district' => 'King Abdullah Economic City',
            'city' => 'Jeddah',
            'type' => 'Logistics Park',
            'sharePrice' => 1000,
            'annualYield' => 13.2,
            'payoutFreq' => 'Quarterly',
            'occupancy' => 100.0,
            'leaseTerm' => '15 Years Master Lease',
            'targetAmount' => 65000000,
            'fundedAmount' => 59800000,
            'fundedPct' => 92,
            'description' => 'High-throughput temperature-controlled pharmaceutical and e-commerce distribution hub next to King Abdullah Port.'
        ],
        [
            'id' => 'P4',
            'title' => 'Corniche Marina Executive Suites',
            'district' => 'Al-Khobar Waterfront',
            'city' => 'Al Khobar',
            'type' => 'Hospitality & Residences',
            'sharePrice' => 750,
            'annualYield' => 10.6,
            'payoutFreq' => 'Quarterly',
            'occupancy' => 92.4,
            'leaseTerm' => '8 Years',
            'targetAmount' => 38000000,
            'fundedAmount' => 28500000,
            'fundedPct' => 75,
            'description' => 'Luxury seafront serviced apartments operated by 5-star international hotel chain with guaranteed minimum floor yields.'
        ],
        [
            'id' => 'P5',
            'title' => 'NEOM Supply Chain Hub Alpha',
            'district' => 'Oxagon Industrial Port',
            'city' => 'NEOM & Red Sea',
            'type' => 'Logistics Park',
            'sharePrice' => 2000,
            'annualYield' => 14.5,
            'payoutFreq' => 'Quarterly',
            'occupancy' => 100.0,
            'leaseTerm' => '20 Years Government Offtake',
            'targetAmount' => 110000000,
            'fundedAmount' => 104500000,
            'fundedPct' => 95,
            'description' => 'Vision 2030 advanced manufacturing and clean hydrogen logistics park with direct deep-sea maritime terminal berths.'
        ]
    ];

    public function index(Request $request): JsonResponse
    {
        $city = $request->query('city');
        $search = strtolower($request->query('search', ''));

        $filtered = array_values(array_filter($this->properties, function ($p) use ($city, $search) {
            if ($city && $city !== 'All Cities' && $p['city'] !== $city) {
                return false;
            }
            if ($search) {
                $matchTitle = str_contains(strtolower($p['title']), $search);
                $matchDist = str_contains(strtolower($p['district']), $search);
                $matchCity = str_contains(strtolower($p['city']), $search);
                if (!$matchTitle && !$matchDist && !$matchCity) return false;
            }
            return true;
        }));

        return response()->json([
            'success' => true,
            'data' => $filtered,
            'stats' => [
                'total_vaults' => count($this->properties),
                'avg_yield' => 11.8,
                'total_aum' => 'SAR 285.0M',
                'occupancy' => '96.8%'
            ]
        ]);
    }

    public function invest(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|string',
            'fractions' => 'required|integer|min:1',
        ]);

        $prop = collect($this->properties)->firstWhere('id', $request->property_id);
        if (!$prop) {
            return response()->json(['success' => false, 'message' => 'Property vault not found'], 404);
        }

        $totalInvestment = $prop['sharePrice'] * $request->fractions;
        $quarterlyDividend = ($totalInvestment * ($prop['annualYield'] / 100)) / 4;

        return response()->json([
            'success' => true,
            'message' => 'Fractional property staking order confirmed successfully.',
            'staking' => [
                'stake_id' => 'STK-' . strtoupper(uniqid()),
                'property_id' => $prop['id'],
                'property_title' => $prop['title'],
                'fractions_acquired' => (int)$request->fractions,
                'total_invested' => $totalInvestment,
                'annual_yield' => $prop['annualYield'],
                'quarterly_payout' => $quarterlyDividend,
                'next_distribution_date' => now()->addMonths(3)->toDateString()
            ]
        ]);
    }
}
