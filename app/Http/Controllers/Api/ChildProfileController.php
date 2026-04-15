<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ChildProfileController extends Controller
{
    /**
     * Get all child profiles for authenticated parent
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $profiles = ChildProfile::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($profiles);
    }

    /**
     * Create a new child profile
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'avatar' => 'nullable|string|max:10',
            'tribe_preferences' => 'nullable|array',
            'tribe_preferences.*' => 'exists:tribes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile = ChildProfile::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'dob' => $request->date_of_birth,
            'total_stars' => 0,
        ]);

        return response()->json($profile, 201);
    }

    /**
     * Get a specific child profile
     */
    public function show(Request $request, $id)
    {
        $profile = ChildProfile::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($profile);
    }

    /**
     * Update a child profile
     */
    public function update(Request $request, $id)
    {
        $profile = ChildProfile::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date|before:today',
            'avatar' => 'nullable|string|max:10',
            'tribe_preferences' => 'nullable|array',
            'tribe_preferences.*' => 'exists:tribes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('date_of_birth')) $data['dob'] = $request->date_of_birth;
        
        $profile->update($data);

        return response()->json($profile);
    }

    /**
     * Delete a child profile
     */
    public function destroy(Request $request, $id)
    {
        $profile = ChildProfile::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $profile->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }
}
