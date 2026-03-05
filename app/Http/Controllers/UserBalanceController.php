<?php

namespace App\Http\Controllers;

use App\Services\UserDataService;
use Illuminate\Http\Request;

class UserBalanceController extends Controller
{
    /**
     * Return current user's credits and tokens as JSON.
     * Used by the frontend for real-time balance polling.
     */
    public function getBalance()
    {
        $accessToken = session('aisite_access_token');

        if (!$accessToken) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $service = new UserDataService();
        $credits = $service->getUserCredits($accessToken);

        if (!$credits) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch balance'], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $credits,
        ]);
    }
}
