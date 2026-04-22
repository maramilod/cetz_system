<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institution;
use App\Models\Student;
use Carbon\Carbon;

class GraduatedStudentsController extends Controller
{
public function index(Request $request)
{
    $query = Student::with(['section.department']) // جلب الشعبة والقسم مع الطالب
                    ->where('current_status', 'متخرج');

    // فلتر البحث
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('student_number', 'like', "%{$search}%");
        });
    }

    $students = $query->orderBy('full_name')->paginate(20);

    return view('students.graduated', compact('students'));
}

public function certificate(Request $request, Student $student)
{
    if (!in_array($student->current_status, ['متخرج', 'graduated'], true)) {
        abort(404);
    }

    $student->load([
        'section.department',
        'enrollments.courseOffering.course',
        'enrollments.courseOffering.semester',
        'enrollments.grade',
    ]);

    $institution = Institution::first();

    $completedEnrollments = $student->enrollments
    ->filter(function ($enrollment) {

        $grade = (float) ($enrollment->grade?->total ?? 0);
        $courseName = $enrollment->courseOffering?->course?->name ?? '';

        $isGraduationProject =
            mb_strpos($courseName, 'مشروع') !== false &&
            mb_strpos($courseName, 'تخرج') !== false;

        // 🚫 لا نحسب مشروع التخرج إذا درجته صفر
        if ($isGraduationProject && $grade <= 0) {
            return false;
        }

        return $enrollment->grade && $enrollment->courseOffering?->course;
    });
  //  $completedEnrollments = $student->enrollments
     //   ->filter(fn ($enrollment) => $enrollment->grade && $enrollment->courseOffering?->course);

    $totalUnits = $completedEnrollments->sum(
        fn ($enrollment) => (float) ($enrollment->courseOffering?->course?->units ?? 0)
    );

    $weightedTotal = $completedEnrollments->sum(function ($enrollment) {
        $units = (float) ($enrollment->courseOffering?->course?->units ?? 0);
        $grade = (float) ($enrollment->grade?->total ?? 0);

        return $units > 0 ? $units * $grade : $grade;
    });

    $percentage = $totalUnits > 0
        ? round($weightedTotal / $totalUnits, 2)
        : round((float) $completedEnrollments->avg(fn ($enrollment) => $enrollment->grade?->total ?? 0), 2);

    $latestSemester = $completedEnrollments
        ->map(fn ($enrollment) => $enrollment->courseOffering?->semester)
        ->filter()
        ->sortByDesc('start_date')
        ->first();

    $graduationSemester = $latestSemester
        ? trim(($latestSemester->term_type ? $latestSemester->term_type . ' ' : '') . Carbon::parse($latestSemester->start_date)->format('Y'))
        : '-';

    $birthDate = $student->dob ? Carbon::parse($student->dob) : null;
    $birthText = $birthDate ? $birthDate->format('Y/m/d') : '-';
    $genderValue = trim((string) $student->gender);
    $isFemale = in_array($genderValue, ['أنثى', 'انثى', 'female', 'Female', 'F'], true);
    $studentNumber = preg_replace('/\D+/', '', (string) ($student->student_number ?: $student->manual_number ?: ''));

    $certificateData = [
        'institution_name_ar' => $institution?->name ?: 'كلية التقنية الهندسية',
        'institution_name_en' => $institution?->name ?: 'College Of Engineering Technology / Zuwara',
        'issue_date' => Carbon::now()->format('Y/m/d'),
        'student_name' => $student->full_name,
        'student_number' => $studentNumber !== '' ? $studentNumber : '-',
        'nationality' => $this->nationalityLabel($student->nationality, $isFemale),
        'birth_date' => $birthText,
        'department_name' => $student->section?->department?->name ?: '-',
        'section_name' => $student->section?->name ?: 'عام',
        'percentage' => number_format($percentage, 2),
        'classification' => $this->classificationLabel($percentage),
        'graduation_semester' => $graduationSemester,
        'student_label' => $isFemale ? 'الطالبة' : 'الطالب',
        'student_label_with_lam' => $isFemale ? 'للطالبة' : 'للطالب',
        'obtained_verb' => $isFemale ? 'تحصلت' : 'تحصل',
        'pronoun_suffix' => $isFemale ? 'ها' : 'ه',
    ];

    if ($request->boolean('print')) {
        return view('students.graduation-certificate-print', compact('student', 'certificateData'));
    }

    return view('students.graduation-certificate', compact('student', 'certificateData'));
}

private function classificationLabel(float $percentage): string
{
    return match (true) {
        $percentage >= 85 => 'ممتاز',
        $percentage >= 75 => 'جيد جداً',
        $percentage >= 65 => 'جيد',
        $percentage >= 50 => 'مقبول',
        default => 'ضعيف',
    };
}

private function nationalityLabel(?string $nationality, bool $isFemale): string
{
    $value = trim((string) $nationality);

    if ($value === '') {
        return '-';
    }

    return match ($value) {
        'ليبيا', 'ليبي', 'ليبية' => $isFemale ? 'ليبية' : 'ليبي',
        'مصر', 'مصري', 'مصرية' => $isFemale ? 'مصرية' : 'مصري',
        'تونس', 'تونسي', 'تونسية' => $isFemale ? 'تونسية' : 'تونسي',
        'الجزائر', 'جزائري', 'جزائرية' => $isFemale ? 'جزائرية' : 'جزائري',
        'المغرب', 'مغربي', 'مغربية' => $isFemale ? 'مغربية' : 'مغربي',
        default => $value,
    };
}

}
