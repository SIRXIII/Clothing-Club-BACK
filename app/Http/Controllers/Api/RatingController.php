<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RatingResource;
use App\Models\Rider;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Rider $rider)
    {
       try {


        $ratings = $rider->ratings()->get();
        return $this->success(RatingResource::collection($ratings), 'Rider rating');

       } catch (\Exception $e) {
         return $this->error('Failed to retrieve rating of rider: ' . $e->getMessage(), 500);
       }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
