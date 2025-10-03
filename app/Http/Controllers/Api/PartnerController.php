<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Models\PartnerDocument;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all partners.
     */
    public function index()
    {
        try {
            $partners = Partner::with('products', 'orders')->get();
            return $this->success(PartnerResource::collection($partners), 'Partners retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve partners: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Fetch a single partners by ID.
     */
    public function show($id)
    {
        try {
            $partners = Partner::with('products', 'orders')->find($id);

            if (!$partners) {
                return $this->error('Partner not found', 404);
            }

            return $this->success(new PartnerResource($partners), 'Partner retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve partners: ' . $e->getMessage(), 500);
        }
    }

    public function statusUpdate(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        $action = $request->status;
        $status = $action == 'accept' ? 'active' : 'suspended';

        Partner::find($request->id)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Partner {$status} successfully"
        ]);
    }

    public function sendEmail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'to' => 'required|email',
            'businessName' => 'required|string',
            'message' => 'required|string',
            'deadline' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors(), 422);
        }

        $data = $validator->validated();

        try {

            Mail::to($data['to'])->send(new RequestInformationEmail(
                $data['businessName'],
                $data['message'],
                $data['deadline']
            ));

            return $this->success(null, 'Email sent successfully', 200);
        } catch (\Exception $e) {

            return $this->error('Failed to send email', null, 500);
        }
    }


    public function store(Request $request)
    {

        $rules = [

            'businessName' => "required",
            'email'        => 'required|email|unique:partners,email',
            'phone'             => ['required', 'regex:/^[0-9-]{7,20}$/'],
            'ownerName' => "required",
            'days' => "required",
            'storetime'        => 'required',
            'address' => "required",
            'location'        => 'required',
            'businesstype' => "required",
            'tax_id'        => 'required',
            'profileImage' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'licenseImages.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'ownerIdImages.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            return $this->error('validation failed', $validator->errors(), 422);
        }

        // $partner = Partner::create($request->except(['profileImage', 'licenseImages', 'ownerIdImages']));
        $partner = Partner::create([
            "name" => $request->ownerName,
            "business_name" => $request->businessName,
            "email" => $request->email,
            "phone" => $request->phone,
            "category" => $request->businesstype,
            "location" => $request->location,
            "address" => $request->address,
            "store_available_days" => $request->days,
            "store_available_time" => $request->storetime,
            "tax_id" => $request->tax_id,
            "status" => "active",
        ]);


        if ($request->hasFile('profileImage')) {
            $path = $request->file('profileImage')->store('partners/profile', 'hetzner');

            $partner->update(['profile_photo' => $path]);
        }

        if ($request->hasFile('license_front')) {

            $path = $request->file('license_front')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       =>  'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('license_back')) {

            $path = $request->file('license_back')->store('partners/licenses', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'license',
                'side'       =>  'back',
                'file_path'  => $path,
            ]);
        }


        if ($request->hasFile('ownerId_front')) {

            $path = $request->file('ownerId_front')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'front',
                'file_path'  => $path,
            ]);
        }

        if ($request->hasFile('ownerId_back')) {
            $path = $request->file('ownerId_back')->store('partners/owner_ids', 'hetzner');
            PartnerDocument::create([
                'partner_id' => $partner->id,
                'type'       => 'owner_id',
                'side'       => 'back',
                'file_path'  => $path,
            ]);
        }

        return $this->success(null, 'Partner created successfully', 200);
    }


    // public function downloadDocuments($partnerId)
    // {
    //     $documents = PartnerDocument::where('partner_id', $partnerId)->get();

    //     if ($documents->isEmpty()) {
    //         return response()->json(['error' => 'No documents found'], 404);
    //     }

    //     $zip = new ZipArchive;
    //     $zipFileName = "partner_{$partnerId}_documents.zip";
    //     $zipPath = storage_path("app/temp/{$zipFileName}");

    //     if (!file_exists(dirname($zipPath))) {
    //         mkdir(dirname($zipPath), 0777, true);
    //     }

    //     if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    //         foreach ($documents as $doc) {

    //             $filePath = Storage::disk('public')->path($doc->file_path);

    //             if (file_exists($filePath)) {
    //                 $zip->addFile($filePath, basename($filePath));
    //             }
    //         }
    //         $zip->close();
    //     } else {
    //         return response()->json(['error' => 'Could not create zip file'], 500);
    //     }

    //     if (!file_exists($zipPath)) {
    //         return response()->json(['error' => 'Zip file not created'], 500);
    //     }

    //     return response()->download($zipPath)->deleteFileAfterSend(true);
    // }
    public function downloadDocuments($partnerId)
{
    $documents = PartnerDocument::where('partner_id', $partnerId)->get();

    if ($documents->isEmpty()) {
        return response()->json(['error' => 'No documents found'], 404);
    }

    $zip = new ZipArchive;
    $zipFileName = "partner_{$partnerId}_documents.zip";
    $zipPath = storage_path("app/temp/{$zipFileName}");

    if (!file_exists(dirname($zipPath))) {
        mkdir(dirname($zipPath), 0777, true);
    }

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($documents as $doc) {
            $relativePath = $doc->file_path; // e.g. "partners/licenses/file.jpg"

            if (Storage::disk('hetzner')->exists($relativePath)) {
                // Download file content from Hetzner
                $fileContents = Storage::disk('hetzner')->get($relativePath);

                // Save temporarily to local storage
                $tempFilePath = storage_path("app/temp/" . basename($relativePath));
                file_put_contents($tempFilePath, $fileContents);

                // Add the temp file into zip
                $zip->addFile($tempFilePath, basename($relativePath));
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
}
