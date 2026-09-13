@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    <!-- Top Cards -->
    <div id="topCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gray-200 animate-pulse rounded-2xl h-32"></div>
        <div class="bg-gray-200 animate-pulse rounded-2xl h-32"></div>
        <div class="bg-gray-200 animate-pulse rounded-2xl h-32"></div>
        <div class="bg-gray-200 animate-pulse rounded-2xl h-32"></div>
    </div>

    <!-- الصف الرئيسي: المهام (يسار) + المواد (يمين) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">

        <!-- بطاقة المهام -->
        <div class="bg-white rounded-2xl shadow p-6 lg:col-span-2 max-h-[600px] overflow-y-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-right">المهام</h2>

            <!-- Form لإضافة مهمة -->
            <form id="taskForm" class="flex flex-col gap-3 text-right">
                <input type="text" id="taskTitle" placeholder="عنوان المهمة" 
                       class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" id="taskDescription" placeholder="وصف المهمة (اختياري)" 
                       class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    إضافة
                </button>
            </form>

            <!-- قائمة المهام -->
            <div class="mt-4">
                <table class="w-full text-right text-gray-700">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 px-2">المهمة</th>
                            <th class="py-2 px-2">الوصف</th>
                            <th class="py-2 px-2">من أضافها</th>
                            <th class="py-2 px-2">منذ</th>
                            <th class="py-2 px-2">إجراء</th>
                        </tr>
                    </thead>
                    <tbody id="tasksTable">
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-400">جارٍ تحميل المهام...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- بطاقة المواد والمواد المسقطة -->
        <div class="bg-white rounded-2xl shadow p-6 max-w-full">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-right">المواد ونسبة النجاح</h2>
            <div id="coursesContainer" class="space-y-4 text-right max-h-[300px] overflow-y-auto pr-2"></div>

            <h3 class="text-xl font-semibold mt-6 mb-2 text-right">المواد المسقطة</h3>
            <div id="droppedCoursesContainer" class="space-y-2 text-right max-h-[200px] overflow-y-auto pr-2 border-t border-gray-200 pt-2"></div>
        </div>

    </div>

<!-- الصف الثاني: الأقسام + الفصل الحالي -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- بطاقة الأقسام -->
    <div class="bg-white rounded-2xl shadow p-6 max-w-full">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-right">الأقسام وعدد الطلاب</h2>
        <div id="departmentsContainer" class="space-y-4 text-right"></div>
    </div>

    <!-- بطاقة الفصل الحالي -->
    <div class="bg-white rounded-2xl shadow p-6 max-w-full">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-right">
          <div class="flex items-center justify-between">
    <div>
         تقرير الاقسام: <span id="activeSemesterName">...</span>
    </div>

    <select id="semesterFilter" class="border rounded p-1 text-sm">
        <option value="">جاري التحميل...</option>
    </select>
    <button id="printSectionBtn"
        class="bg-gray-800 text-white text-xs px-3 py-1 rounded hover:bg-gray-700 transition">
    طباعة
</button>
</div>
        </h2>
        <p class="text-sm text-gray-500 mb-4" id="activeSemesterDates">...</p>
<div id="studentsPerSectionContainer"
     class="space-y-4 text-right max-h-[300px] overflow-y-auto pr-2">
</div>    </div>
</div>


    <!-- بطاقة الطلبة حسب الجنس والجنسية -->
    <div class="bg-white rounded-2xl shadow p-6 max-w-full mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-right">توزيع الطلبة</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- الرسم الدائري حسب الجنس -->
            <div class="text-center">
                <canvas id="studentsGenderChart" class="max-h-[250px]"></canvas>
                <p class="mt-2 text-sm text-gray-500">حسب الجنس</p>
            </div>
            <!-- الرسم الدائري حسب الجنسية -->
            <div class="text-center">
                <canvas id="studentsNationalityChart" class="max-h-[250px]"></canvas>
                <p class="mt-2 text-sm text-gray-500">حسب الجنسية</p>
            </div>
        </div>
    </div>

</div>

<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    
    const response = await fetch('/dashboard/analytics');
    const data = await response.json();
    
const semesterSelect = document.getElementById('semesterFilter');
const activeSemesterName = document.getElementById('activeSemesterName');



document.getElementById('printSectionBtn').addEventListener('click', () => {
    printActiveSemesterSections();
});
    // ===== Top Cards =====
    const topCards = [
        { title: ' الطلبة', value: data.top_cards.students, icon: '👥', borderGradient: 'from-blue-500 to-blue-600' },
        { title: ' اعضاء هيئة التدريس', value: data.top_cards.teachers, icon: '👨‍🏫', borderGradient: 'from-green-500 to-green-600' },
        { title: ' المقررات', value: data.top_cards.courses, icon: '📚', borderGradient: 'from-purple-500 to-purple-600' },
        { title: 'الفصول', value: data.top_cards.semester_packages, icon: '📘', borderGradient: 'from-orange-500 to-orange-600' }
    ];

    const topContainer = document.getElementById('topCards');
    topContainer.innerHTML = '';
    topCards.forEach(card => {
        topContainer.innerHTML += `
            <div class="border-t-4 ${card.borderGradient} border-b-0 border-l-0 border-r-0
                        bg-white rounded-2xl shadow-xl p-6
                        hover:scale-105 transition-transform duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">${card.title}</p>
                        <h2 class="text-3xl font-bold mt-1">${card.value}</h2>
                    </div>
                    <div class="text-4xl opacity-80">${card.icon}</div>
                </div>
            </div>
        `;
    });

    // ===== بطاقة الأقسام =====
    const deptContainer = document.getElementById('departmentsContainer');
    deptContainer.innerHTML = '';
    const deptColors = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-orange-500', 'bg-red-500', 'bg-teal-500', 'bg-pink-500', 'bg-yellow-500'];
    let maxStudents = Math.max(...data.departments.map(d => d.total_students));
    data.departments.forEach((dept, index) => {
        const percentage = maxStudents ? (dept.total_students / maxStudents) * 100 : 0;
        const color = deptColors[index % deptColors.length];
        deptContainer.innerHTML += `
            <div class="space-y-1">
                <div class="flex justify-between text-sm font-medium text-gray-700">
                    <span>${dept.total_students} طالب</span>
                    <span>${dept.department_name}</span>
                </div>
                <div class="w-full bg-gray-200 h-3 rounded-full">
                    <div class="h-3 rounded-full ${color}" style="width: ${percentage}%;"></div>
                </div>
            </div>
        `;
    });
renderActiveSemester(data.activeSemester);
    // ===== بطاقة الفصل الحالي =====
function renderActiveSemester(activeSemester) {
    activeSemesterName.textContent = activeSemester?.semester_type ?? 'لا يوجد';
    activeSemesterDates.textContent = activeSemester?.semester_dates ?? '';

    studentsPerSectionContainer.innerHTML = '';

    if (activeSemester?.students_per_section?.length > 0) {
        const maxCount = Math.max(
            ...activeSemester.students_per_section.map(s => s.students_count)
        );

        const colors = ['bg-blue-500','bg-green-500','bg-purple-500','bg-orange-500','bg-red-500'];

        activeSemester.students_per_section.forEach((section, index) => {
            const percentage = maxCount ? (section.students_count / maxCount) * 100 : 0;
            const color = colors[index % colors.length];

            studentsPerSectionContainer.innerHTML += `
                <div class="space-y-2 border rounded-xl p-3 bg-gray-50">

    <div class="flex justify-between text-sm font-bold text-gray-800">
        <span>${section.section_name}</span>
        <span>${section.percentage}%</span>
    </div>

    <div class="flex justify-between text-sm text-gray-600">
        <span>👨 ذكور: ${section.male_count}</span>
        <span>👩 إناث: ${section.female_count}</span>
    </div>

    <div class="text-sm text-gray-700">
        المجموع: ${section.students_count} طالب
    </div>

    <div class="w-full bg-gray-200 h-3 rounded-full">
        <div class="h-3 rounded-full ${color}"
            style="width: ${percentage}%;">
        </div>
    </div>

</div>
                  
            `;
        });
    } else {
        studentsPerSectionContainer.innerHTML =
            `<p class="text-sm text-gray-400">لا يوجد طلاب في الفصل الحالي</p>`;
    }
}


    // ===== بطاقة المواد =====
    const courseContainer = document.getElementById('coursesContainer');
    courseContainer.innerHTML = '';
    const courseColors = ['bg-blue-400', 'bg-green-400', 'bg-purple-400', 'bg-orange-400', 'bg-red-400', 'bg-teal-400', 'bg-pink-400', 'bg-yellow-400'];
    data.courses.forEach((course, index) => {
        const percentage = course.success_rate; 
        const color = courseColors[index % courseColors.length];
        courseContainer.innerHTML += `
            <div class="space-y-1">
                <div class="flex justify-between text-sm font-medium text-gray-700">
                    <span>${percentage.toFixed(1)}% ناجح</span>
                    <span>${course.course_name}</span>
                </div>
                <div class="w-full bg-gray-200 h-3 rounded-full">
                    <div class="h-3 rounded-full ${color}" style="width: ${percentage}%;"></div>
                </div>
            </div>
        `;
    });

    // ===== بطاقة المواد المسقطة =====
    const droppedContainer = document.getElementById('droppedCoursesContainer');
    droppedContainer.innerHTML = '';
    if(data.extra && data.extra.dropped_courses_list && data.extra.dropped_courses_list.length > 0){
        data.extra.dropped_courses_list.forEach((course) => {
            droppedContainer.innerHTML += `
                <div class="flex justify-between text-sm font-medium text-red-600">
                    <span>${course.course_name}</span>
                    <span>مسقطة</span>
                </div>
            `;
        });
    } else {
        droppedContainer.innerHTML = `<p class="text-sm text-gray-400">لا توجد مواد مسقطة حاليا</p>`;
    }

    // ===== الرسوم البيانية =====

    // الطلبة حسب الجنس
    new Chart(document.getElementById('studentsGenderChart'), {
        type: 'pie',
        data: {
            labels: ['ذكور', 'إناث'],
            datasets: [{
                data: [data.extra.gender_distribution.male, data.extra.gender_distribution.female],
                backgroundColor: ['#3B82F6', '#EC4899']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // الطلبة حسب الجنسية
    const nationalityData = data.extra.nationality_distribution;
    new Chart(document.getElementById('studentsNationalityChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(nationalityData),
            datasets: [{
                data: Object.values(nationalityData),
                backgroundColor: Object.keys(nationalityData).map(() => '#' + Math.floor(Math.random()*16777215).toString(16))
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // ===== بطاقة المهام =====
    const tasksTable = document.getElementById('tasksTable');
    const taskForm = document.getElementById('taskForm');
    const taskTitle = document.getElementById('taskTitle');
    const taskDescription = document.getElementById('taskDescription');

    async function loadTasks() {
        const res = await fetch('/tasks');
        const tasks = await res.json();
        tasksTable.innerHTML = '';

        if (tasks.length === 0) {
            tasksTable.innerHTML = `<tr>
                <td colspan="5" class="text-center py-4 text-gray-400">لا توجد مهام</td>
            </tr>`;
            return;
        }

        tasks.forEach(task => {
            const createdAt = new Date(task.created_at);
            tasksTable.innerHTML += `
                <tr class="border-b">
                    <td class="py-2 px-2">${task.title}</td>
                    <td class="py-2 px-2">${task.description ?? '-'}</td>
                    <td class="py-2 px-2">${task.creator?.full_name ?? 'غير معروف'}</td>
                    <td class="py-2 px-2">${createdAt.toLocaleString()}</td>
                    <td class="py-2 px-2">
                        <button onclick="deleteTask(${task.id})" 
                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                            حذف
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    window.deleteTask = async function(id) {
        if (!confirm('هل تريد حذف هذه المهمة؟')) return;

        await fetch(`/tasks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        loadTasks();
    };

    taskForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!taskTitle.value.trim()) return alert('يجب إدخال عنوان المهمة');

        await fetch('/tasks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                title: taskTitle.value,
                description: taskDescription.value
            })
        });

        taskTitle.value = '';
        taskDescription.value = '';
        loadTasks();
    });

    loadTasks();
    semesterSelect.innerHTML = '<option value=""> الفصل الحالي</option>';

data.semesters.forEach(s => {
    semesterSelect.innerHTML += `
        <option value="${s.id}">
            ${s.term_type} - ${s.start_date} → ${s.end_date}
        </option>
    `;
});
    semesterSelect.addEventListener('change', async (e) => {
    const semesterId = e.target.value;

    const res = await fetch(`/dashboard/analytics?semester_id=${semesterId}`);
    const newData = await res.json();

    // تحديث فقط الفصل الحالي بدون لمس باقي الداشبورد
    renderActiveSemester(newData.activeSemester);
});
function printActiveSemesterSections() {

    // اسم الفصل الحالي
    const semesterName =
        document.getElementById('activeSemesterName').textContent;

    // استخراج السنة من الفلتر
    const semesterSelect = document.getElementById('semesterFilter');
    const selectedOption =
        semesterSelect.options[semesterSelect.selectedIndex];

    // مثال:
    // خريفي - 2025-09-01 → 2026-01-01
    const optionText = selectedOption?.textContent ?? '';

    // استخراج أول سنة من النص
    const yearMatch = optionText.match(/\d{4}/);
    const reportYear = yearMatch ? yearMatch[0] : 'الحالية ';

    // بيانات الأقسام
    const sections = [
        ...document.querySelectorAll('#studentsPerSectionContainer > div')
    ];

    let rows = '';

    sections.forEach(section => {

        const sectionName =
            section.querySelector('.font-bold span:first-child')
            ?.textContent ?? '-';

        const maleText =
            section.querySelector('.text-gray-600 span:first-child')
            ?.textContent ?? '';

        const femaleText =
            section.querySelector('.text-gray-600 span:last-child')
            ?.textContent ?? '';

        const totalText =
            section.querySelector('.text-gray-700')
            ?.textContent ?? '';

        const maleCount =
            maleText.replace('👨 ذكور:', '').trim();

        const femaleCount =
            femaleText.replace('👩 إناث:', '').trim();

        const totalCount =
            totalText
            .replace('المجموع:', '')
            .replace('طالب', '')
            .trim();

        rows += `
            <tr>
                <td>${sectionName}</td>
                <td>${maleCount}</td>
                <td>${femaleCount}</td>
                <td>${totalCount}</td>
            </tr>
        `;
    });

    const printWindow = window.open('', '', 'width=900,height=700');

    printWindow.document.write(`
        <html dir="rtl">
        <head>
            <title>تقرير الأقسام</title>

            <style>
                body{
                    font-family: Tahoma, Arial, sans-serif;
                    padding: 30px;
                    direction: rtl;
                    color: #222;
                }

                h1{
                    text-align: center;
                    margin-bottom: 10px;
                }

                h2{
                    text-align: center;
                    color: #555;
                    margin-bottom: 25px;
                    font-size: 18px;
                }

                table{
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }

                th, td{
                    border: 1px solid #ccc;
                    padding: 12px;
                    text-align: center;
                }

                th{
                    background: #f3f4f6;
                    font-weight: bold;
                }

                tr:nth-child(even){
                    background: #fafafa;
                }
            </style>
        </head>

        <body>

            <h1>
                تقرير الأقسام لسنة ${reportYear}
            </h1>

            <h2>
                الفصل: ${semesterName}
            </h2>

            <table>
                <thead>
                    <tr>
                        <th>اسم القسم</th>
                        <th>ذكور</th>
                        <th>إناث</th>
                        <th>المجموع</th>
                    </tr>
                </thead>

                <tbody>
                    ${rows}
                </tbody>
            </table>

            <script>
                window.onload = function () {
                    window.print();
                    window.close();
                }
            <\/script>

        </body>
        </html>
    `);

    printWindow.document.close();
}
});
</script>
@endsection
