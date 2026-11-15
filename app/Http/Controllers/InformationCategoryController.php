<?php

namespace App\Http\Controllers;

use App\Models\InformationCategory;
use Illuminate\Http\Request;

class InformationCategoryController extends Controller
{
    public function index()
    {
        return InformationCategory::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'information_category' => 'required|string|max:255|unique:information_categories,information_category',
            'points' => 'required|integer|min:0|max:1000',
        ]);

        return InformationCategory::create($request->only('information_category', 'points'));
    }

    public function show(InformationCategory $informationCategory)
    {
        return $informationCategory;
    }

    public function update(Request $request, InformationCategory $informationCategory)
    {
        $request->validate([
            'information_category' => 'required|string|max:255|unique:information_categories,information_category,' . $informationCategory->id,
            'points' => 'required|integer|min:0|max:1000',
        ]);

        $informationCategory->update($request->only('information_category', 'points'));

        return $informationCategory;
    }

    public function destroy(InformationCategory $informationCategory)
    {
        $informationCategory->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}