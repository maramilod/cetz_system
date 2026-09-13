<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstituteController extends Controller
{
    public function saveNumber(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'type' => 'required|in:معهد,كلية',
        ]);

        $institute = Institution::first() ?? new Institution();
        $institute->official_number = $request->number;
$institute->authority_code  = $request->code;
$institute->type            = $request->type;
$institute->save();
Cache::forget('institute_data');


        return response()->json(['message' => 'تم حفظ البيانات بنجاح', 'institute' => $institute]);
    }

      public function saveInfo(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048', // 2MB
        ]);

        // جلب السجل الأول أو إنشاء جديد
        $institute = Institution::firstOrNew();

        // معالجة رفع الشعار
        if ($request->hasFile('logo')) {
            // حذف الشعار القديم إذا موجود
            if ($institute->logo) {
                Storage::disk('public')->delete($institute->logo);
            }

            // تخزين الشعار الجديد
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        $institute->fill($data);
        $institute->save();
// بعد حفظ البيانات
Cache::forget('institute_data');

        return response()->json([
            'message' => 'تم حفظ بيانات المؤسسة بنجاح',
            'institute' => $institute
        ]);
    }


}

