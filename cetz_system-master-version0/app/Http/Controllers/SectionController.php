<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Section;

class SectionController extends Controller
{

       public function create($departmentId)
{
    return view('sections.create', compact('departmentId'));
}

    

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $data['is_active'] = 1;
        $data['updated_by'] = Auth::id();

        Section::create($data);

        return redirect()->route('departments.index')->with('success', 'تم إضافة الشعبة بنجاح.');
    }

    public function edit(Section $section)
    {
        return view('sections.edit', compact('section'));
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data['updated_by'] = Auth::id();

        $section->update($data);

        return redirect()->route('departments.index')->with('success', 'تم تعديل الشعبة بنجاح.');
    }

    public function destroy(Section $section)
    {
        if ($section->students()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الشعبة لأنها تحتوي على طلاب.');
        }

        $section->delete();

        return back()->with('success', 'تم حذف الشعبة.');
    }

    public function toggle(Section $section)
    {
        $section->is_active = !$section->is_active;
        $section->updated_by = Auth::id();
        $section->save();

        return back()->with('success', 'تم تحديث حالة الشعبة.');
    }
}
