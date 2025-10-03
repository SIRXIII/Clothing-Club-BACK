<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiderResource;
use App\Models\Rider;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class RiderController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $riders = Rider::with("orders")->get();

            return $this->success(RiderResource::collection($riders), 'Riders retrieved successfully');
        } catch (\Exception $e) {

            return $this->error('Failed to retrieve riders: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $req_data = $request->all();
        $rules = [
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required|email|unique:riders,email",
            'phone'             => ['required', 'regex:/^[0-9-]{7,20}$/'],
            "address" => "required",
            "profile_photo" => "nullable|mimes:jpg,jpeg,png|max:2048",
            'licenseImages.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'license_plate' => "required",
            "vehicle_type" => "required",
            "vehicle_name" => "required",
            "assigned_region" => "required",
            "insurance_expire_date" => "required",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return $this->error('validation failed', $validator->errors(), 422);
        }


        $rider = Rider::create($request->all());
        $rider->update(['rider_id' => "RID-" . $rider->id]);

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('riders/profile', 'hetzner');
            $rider->update(['profile_photo' => $path]);
        }

        if ($request->hasFile('license_front')) {
            $path = $request->file('license_front')->store('riders/licenses', 'hetzner');
            $rider->update(['license_front' => $path]);
        }

        if ($request->hasFile('license_back')) {
            $path = $request->file('license_back')->store('riders/licenses', 'hetzner');
            $rider->update(['license_back' => $path]);
        }

        return $this->success(null, 'Rider created successfully', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $rider = Rider::with("orders")->find($id);

            if (!$rider) {
                return $this->error('Rider not found', 404);
            }

            return $this->success(new RiderResource($rider), 'Rider retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve rider: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        // dd($request->all());
        $rider = Rider::find($id);

        if (!$rider) {
            return $this->error('Rider not found', [], 404);
        }

        $rules = [
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required|email|unique:riders,email," . $rider->id,
            'phone'             => ['required', 'regex:/^[0-9-]{7,20}$/'],
            "address" => "required",
            "profile_photo" => "nullable|mimes:jpg,jpeg,png|max:2048",
            "license_front" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
            "license_back" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
            "license_plate" => "required",
            "vehicle_type" => "required",
            "vehicle_name" => "required",
            "assigned_region" => "required",
            "insurance_expire_date" => "required",
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $rider->update($request->except(['profile_photo', 'license_front', 'license_back']));

        if ($request->hasFile('profile_photo')) {
            if ($rider->profile_photo && Storage::disk('public')->exists($rider->profile_photo)) {
                Storage::disk('public')->delete($rider->profile_photo);
            }
            $path = $request->file('profile_photo')->store('riders/profile', 'public');
            $rider->update(['profile_photo' => $path]);
        }

        if ($request->hasFile('license_front')) {
            if ($rider->license_front && Storage::disk('public')->exists($rider->license_front)) {
                Storage::disk('public')->delete($rider->license_front);
            }
            $path = $request->file('license_front')->store('riders/licenses', 'public');
            $rider->update(['license_front' => $path]);
        }


        if ($request->hasFile('license_back')) {
            if ($rider->license_back && Storage::disk('public')->exists($rider->license_back)) {
                Storage::disk('public')->delete($rider->license_back);
            }
            $path = $request->file('license_back')->store('riders/licenses', 'public');
            $rider->update(['license_back' => $path]);
        }

        return $this->success(null, 'Rider updated successfully', 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


public function downloadDocuments($riderId)
{
    $rider = Rider::find($riderId);

    if (!$rider) {
        return response()->json(['error' => 'Rider not found'], 404);
    }

    $docs = [];
    if ($rider->license_front) {
        $docs[] = $rider->license_front;
    }
    if ($rider->license_back) {
        $docs[] = $rider->license_back;
    }

    if (empty($docs)) {
        return response()->json(['error' => 'No documents found'], 404);
    }

    $zip = new \ZipArchive;
    $zipFileName = "rider_{$riderId}_documents.zip";
    $zipPath = storage_path("app/temp/{$zipFileName}");

    if (!file_exists(dirname($zipPath))) {
        mkdir(dirname($zipPath), 0777, true);
    }

    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
        foreach ($docs as $doc) {
            if (Storage::disk('hetzner')->exists($doc)) {
                $stream = Storage::disk('hetzner')->get($doc);
                $zip->addFromString(basename($doc), $stream);
            } elseif (filter_var($doc, FILTER_VALIDATE_URL)) {
                $fileContent = @file_get_contents($doc);
                if ($fileContent !== false) {
                    $zip->addFromString(basename(parse_url($doc, PHP_URL_PATH)), $fileContent);
                }
            }
        }
        $zip->close();
    } else {
        return response()->json(['error' => 'Could not create zip file'], 500);
    }

    if (!file_exists($zipPath)) {
        return response()->json(['error' => 'Zip file not created'], 500);
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}



    public function statusUpdate(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        $action = $request->status;
        $status = $action == 'active' ? 'active' : 'suspended';

        Rider::find($request->id)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Rider {$status} successfully"
        ]);
    }
}
