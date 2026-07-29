<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Models\School;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', EventCategory::class);

        $schoolId   = auth()->user()->school_id;
        $categories = EventCategory::where('school_id', $schoolId)
            ->orderBy('name')
            ->withCount('events')
            ->get();

        return view('calendar.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', EventCategory::class);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'is_holiday' => ['boolean'],
            'color'      => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        EventCategory::create([
            'school_id'  => auth()->user()->school_id,
            'name'       => $data['name'],
            'is_holiday' => $request->boolean('is_holiday'),
            'color'      => $data['color'],
        ]);

        return back()->with('success', 'Kategori "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, EventCategory $category)
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'is_holiday' => ['boolean'],
            'color'      => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category->update([
            'name'       => $data['name'],
            'is_holiday' => $request->boolean('is_holiday'),
            'color'      => $data['color'],
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(EventCategory $category)
    {
        $this->authorize('delete', $category);

        if ($category->events()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki event terkait.');
        }

        $name = $category->name;
        $category->delete();

        return back()->with('success', 'Kategori "' . $name . '" berhasil dihapus.');
    }
}
