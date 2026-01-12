@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6 max-w-7xl mx-auto">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- بطاقة عدد الأساتذة -->
        <div class="bg-white text-blue-800 rounded-2xl shadow-lg p-6 border border-gray-200 text-center">
            <h2 class="text-2xl font-bold mb-2">الأساتذة النشطين</h2>
            <p class="text-5xl font-extrabold">{{ $teachersCount }}</p>
        </div>

        <!-- عدد الطلاب لكل مادة -->
        <div class="bg-white text-green-800 rounded-2xl shadow-lg p-6 border border-gray-200">
            <h2 class="text-xl font-bold mb-4 text-center">عدد الطلاب لكل مادة</h2>
            <canvas id="chartStudentsPerCourse" class="w-full h-64"></canvas>
        </div>

        <!-- دمج الطلاب لكل قسم والشعبة -->
        <div class="bg-white text-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 col-span-1 md:col-span-2">
            <h2 class="text-xl font-bold mb-4 text-center">عدد الطلاب لكل قسم وشعبة</h2>
            <canvas id="chartStudentsPerDepartmentSection" class="w-full h-64"></canvas>
        </div>

    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ===== بيانات الطلاب لكل مادة =====
    const courseLabels = @json($studentsPerCourse->pluck('course_name'));
    const courseData = @json($studentsPerCourse->pluck('students_count'));
    new Chart(document.getElementById('chartStudentsPerCourse'), {
        type: 'bar',
        data: {
            labels: courseLabels,
            datasets: [{
                label: 'عدد الطلاب',
                data: courseData,
                backgroundColor: courseLabels.map(() => `hsl(${Math.random()*360}, 70%, 50%)`),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'عدد الطلاب' } },
                x: { title: { display: true, text: 'المادة' } }
            }
        }
    });

    // ===== دمج الطلاب لكل قسم والشعبة =====
    const sectionLabels = @json($studentsPerSection->pluck('section_name'));
    const deptLabels = @json($studentsPerDepartment->pluck('department_name'));
    const sectionData = @json($studentsPerSection->pluck('students_count'));
    const deptData = @json($studentsPerDepartment->pluck('students_count'));

    new Chart(document.getElementById('chartStudentsPerDepartmentSection'), {
        type: 'line',
        data: {
            labels: sectionLabels, // كل الشُعب على المحور X
            datasets: [
                {
                    label: 'عدد الطلاب بالقسم',
                    data: deptData,
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79,70,229,0.1)',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'عدد الطلاب بالشعبة',
                    data: sectionData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: false,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' }, tooltip: { enabled: true } },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'عدد الطلاب' } },
                x: { title: { display: true, text: 'الشعبة' } }
            }
        }
    });
});
</script>

<style>
/* تحسين مظهر البطاقات */
.bg-white { background-color: #ffffff !important; }
</style>
@endsection
