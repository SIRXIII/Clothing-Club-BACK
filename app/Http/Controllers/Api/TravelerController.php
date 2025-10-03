<?php

namespace App\Http\Controllers\Api;

use App\Exports\TravelersExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\TravelerResource;
use App\Models\Traveler;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class TravelerController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all travelers.
     */
    public function index()
    {
        try {
            $travelers = Traveler::with('addresses', 'orders')->get();
            return $this->success(TravelerResource::collection($travelers), 'Travelers retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve travelers: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Fetch a single traveler by ID.
     */
    public function show($id)
    {
        try {
            $traveler = Traveler::with('addresses', 'orders')->find($id);

            if (!$traveler) {
                return $this->error('Traveler not found', 404);
            }

            return $this->success(new TravelerResource($traveler), 'Traveler retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve traveler: ' . $e->getMessage(), 500);
        }
    }

     public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required',
        ]);

        $action = $request->status;
        $status = $action == 'Activate' ? 'active' : 'suspended';

        Traveler::whereIn('id', $request->ids)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Travelers {$status} successfully"
        ]);
    }

    public function export(Request $request)
    {
        $travelers = Traveler::whereIn('id', $request->ids)->get();
        return Excel::download(new TravelersExport($travelers), 'travelers (' . now()->format('Y-m-d H-i-s') . ').csv',\Maatwebsite\Excel\Excel::CSV);
    }

    public function destroy($id)
    {
        $traveler = Traveler::find($id);

        if (!$traveler) {


            return $this->error('Traveler not found ', 404);
        }

        $traveler->delete();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Traveler deleted successfully'
        // ]);
            return $this->success(null, 'Traveler deleted successfully', 200);

    }

}
