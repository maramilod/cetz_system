@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    {{-- نموذج البحث عن الطالب --}}
    <form action="{{ route('students.enrollments') }}" method="GET" class="mb-6 flex gap-2 items-center">
        <div x-data="studentSearchBox()" class="relative">
    
    <input type="text"
           x-model="search"
           @input.debounce.300="searchStudents()"
           @focus="open = true"
           @click.outside="open = false"
           placeholder="ابحث بالاسم أو الرقم"
           class="border rounded px-3 py-2 w-64">

    <!-- القيمة الفعلية التي سترسل -->
    <input type="hidden" name="student_number" :value="selectedNumber">

    <!-- النتائج -->
    <div x-show="open && results.length"
         class="absolute z-50 bg-white border w-full mt-1 max-h-60 overflow-auto rounded shadow">

        <template x-for="s in results" :key="s.id">
            <div @mousedown.prevent="select(s)"
                 class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                <span x-text="s.full_name"></span>
                -
                <span x-text="s.student_number"></span>
            </div>
        </template>
    </div>
</div>
        <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            عرض كشف المواد
        </button>
    </form>

    @if(session('error'))
        <div class="mb-4 rounded bg-red-50 border border-red-200 text-red-700 px-4 py-2">
            {{ session('error') }}
        </div>
    @endif

    @if($student)
    {{-- عنوان الطالب --}}
    <h1 class="text-2xl font-bold mb-6">
        كشف الطالب: {{ $student->full_name }}
        <span class="text-gray-600 text-base">
            ({{ $student->manual_number ?? $student->student_number }})
        </span>
    </h1>

    {{-- أزرار التصدير والطباعة --}}
    <div class="flex gap-2 mb-4">
        <button onclick="exportExcel()"
                class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
            Excel (الكل)
        </button>

        <button onclick="printTable()"
                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
            PDF (الكل)
        </button>

   @foreach($semesterEnrollments->groupBy(fn($s) => $s['year'].'_'.$s['term_type']) as $key => $group)
        @php
            $semester = $group->first();
        @endphp
        <button onclick="printSemester('{{ $semester['year'] }}','{{ $semester['term_type'] }}','{{ $semester['start_date'] }}','{{ $semester['end_date'] }}')"
                class="bg-yellow-500 hover:bg-yellow-700 text-white px-4 py-2 rounded">
            طباعة الفصل: {{ $semester['term_type'] }} {{ $semester['year'] }}
        </button>
    @endforeach

    </div>
{{-- عرض تنبيه الانقطاع في الصفحة إن وجد --}}
@if(isset($statusHistories) && $statusHistories->count() > 0)
    <div class="mb-6 p-4 bg-amber-50 border-r-4 border-amber-500 rounded-lg shadow-sm">
        <h3 class="text-amber-800 font-bold text-base mb-2 flex items-center gap-2">
            <span>⚠️</span>تنبية: تم ايقاف قيد الطالب في التواريخ الاتيه
        </h3>
        <ul class="list-disc list-inside text-amber-900 text-sm space-y-1">
            @foreach($statusHistories as $history)
                <li>
                    تم الايقاف من تاريخ: <strong>{{ $history->from_date }}</strong> 
                    إلى تاريخ: <strong>{{ $history->to_date ?? 'حتى الآن' }}</strong>
                    @if($history->reason)
                        <span class="text-gray-600">({{ $history->reason }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
    {{-- عرض الفصول والمواد --}}
    @forelse($semesterEnrollments as $semester)
        <h2 class="text-xl font-bold mt-6 mb-2">
            السنة: {{ $semester['year'] }} | الفصل: {{ $semester['term_type'] }} | اسم الفصل: {{ $semester['semester_name'] }}
        </h2>
        <div class="mb-8 rounded-lg shadow
            {{ $semester['active'] ? 'border-2 border-green-500 bg-green-50' : 'bg-white' }}">
            
            {{-- عنوان الفصل --}}
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h2 class="text-lg font-semibold">{{ $semester['semester_name'] }}</h2>
                <span class="px-3 py-1 text-sm rounded-full
                    {{ $semester['active'] ? 'bg-green-600 text-white' : 'bg-gray-400 text-white' }}">
                    {{ $semester['active'] ? 'فصل مفعل' : 'فصل غير مفعل' }}
                </span>
            </div>

            {{-- جدول المواد --}}
            <div class="overflow-x-auto p-4 mt-2">
                <table class="w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">رمز المادة</th>
                            <th class="border px-2 py-1">اسم المادة</th>
                            <th class="border px-2 py-1">الوحدات</th>
                            <th class="border px-2 py-1">الساعات</th>
                            <th class="border px-2 py-1">المجموع</th>
                            <th class="border px-2 py-1">الحالة</th>
                            <th class="border px-2 py-1">المحاولة</th>
                            <th class="border px-2 py-1">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semester['enrollments'] as $e)
                            <tr class="text-center">
                                <td class="border px-2 py-1">{{ $e['course_code'] }}</td>
                                <td class="border px-2 py-1 text-right">{{ $e['course_name'] }}</td>
                                <td class="border px-2 py-1">{{ $e['units'] }}</td>
                                <td class="border px-2 py-1">{{ $e['hours'] }}</td>
                                <td class="border px-2 py-1 font-semibold">{{ $e['total'] ?? '-' }}</td>
                                <td class="border px-2 py-1">
                                    @if($e['status'] === 'in_progress')
                                        <span class="text-blue-600">قيد الدراسة</span>
                                    @elseif($e['status'] === 'passed')
                                        <span class="text-green-600">ناجح</span>
                                    @elseif($e['status'] === 'failed')
                                        <span class="text-red-600">راسب</span>
                                    @elseif($e['status'] === 'equivalent')
                                        <span class="text-purple-600">معادلة</span>
                                    @else
                                        <span>—</span>
                                    @endif


                                </td>
                                <td class="border px-2 py-1">{{ $e['attempt'] }}</td>
                                <td class="border px-2 py-1">
                                    @if($e['grade'])
                                        <button onclick="showGradeDetails({{ json_encode($e) }})"
                                                class="text-blue-600 underline text-xs">
                                            عرض
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- المجاميع للفصل --}}
                <div class="mt-3 font-semibold text-gray-700 px-4">
                    مجموع الوحدات: {{ $semester['total_units'] }} |
                    مجموع الساعات: {{ $semester['total_hours'] }} |
                    معدل الفصل: {{ $semester['gpa'] ?? '-' }}
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">
            لا توجد مواد مسجلة لهذا الطالب
        </div>
    @endforelse

    {{-- زر الرجوع وزر التعيين كخريج --}}
    <div class="mt-6 flex gap-2">
        <a href="{{ route('students.index') }}"
           class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            رجوع
        </a>

        <form action="{{ route('students.setGraduated', $student->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                    onclick="return confirm('⚠️ هذا القرار نهائي ولا يمكن التراجع عنه!\n\nيرجى التأكد من مراجعة كل مواد الطالب قبل المتابعة.\n\nهل أنت متأكد من تعيين هذا الطالب كخريج؟');"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                تعيين كخريج
            </button>
        </form>
    </div>
    @else
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">
            ابحث بالاسم أو الرقم ثم اضغط <strong>عرض كشف المواد</strong>.
        </div>
    @endif

</div>

{{-- Modal الدرجات --}}
<div id="gradeModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-6">
        <h2 class="text-lg font-bold mb-4">تفاصيل الدرجات</h2>
        <table class="w-full text-sm border">
            <tbody id="gradeDetails"></tbody>
        </table>
        <div class="mt-4 text-right">
            <button onclick="closeModal()"
                    class="bg-gray-600 text-white px-4 py-1 rounded">
                إغلاق
            </button>
        </div>
    </div>
</div>

{{-- جداول مخفية للطباعة --}}
<div id="printTables" class="hidden">
    @forelse($semesterEnrollments as $semester)

<table
    data-start="{{ $semester['start_date'] }}"
    data-end="{{ $semester['end_date'] }}"
    data-active="{{ $semester['active'] ? '1' : '0' }}"
    data-year="{{ $semester['year'] }}"
    data-term_type="{{ $semester['term_type'] }}"
    data-semester_name="{{ $semester['semester_name'] }}"
>

            <thead>
                <tr>
                    <th>رمز المادة</th>
                    <th>اسم المادة</th>
                    <th>الوحدات</th>
                    <th>الساعات</th>
                    <th>المجموع</th>
                    <th>الحالة</th> <!-- أضف هذا العمود -->
                    <th>المحاولة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semester['enrollments'] as $e)
                    <tr>
                        <td>{{ $e['course_code'] }}</td>
                        <td>{{ $e['course_name'] }}</td>
                        <td>{{ $e['units'] }}</td>
                        <td>{{ $e['hours'] }}</td>
                        <td>{{ $e['total'] ?? '-' }}</td>
                        <td> <!-- أضف منطق الحالة هنا -->
                        @if($e['status'] === 'in_progress')  
                        @elseif($e['status'] === 'passed') 
                        @elseif($e['status'] === 'failed') 
                        @elseif($e['status'] === 'equivalent') معادلة
                        @else — @endif
                    </td>
                        <td>{{ $e['attempt'] }}</td>
                    </tr>
                @endforeach
                {{-- مجاميع الفصل للطباعة --}}
                <tr style="font-weight:bold; background-color:#f0f0f0;">
                    <td colspan="2" style="text-align:right;">مجاميع الفصل</td>
                    <td>{{ $semester['total_units'] }}</td>
                    <td>{{ $semester['total_hours'] }}</td>
                    <td>{{ $semester['gpa'] ?? '-' }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @empty
        <tr><td colspan="6">لا توجد مواد للطباعة</td></tr>
    @endforelse
</div>
<script>
function studentSearchBox() {
    return {
        search: '',
        results: [],
        open: false,
        selectedNumber: '',

        searchStudents() {
            const val = this.search.trim();

            if (val.length < 2) {
                this.results = [];
                return;
            }

            fetch(`/download-materials/search-autocompletefull?query=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    this.results = data;
                    this.open = true;
                })
                .catch(err => console.error(err));
        },

   select(student) {
    this.search = `${student.full_name} - ${student.student_number}`;
    this.selectedNumber = student.student_number;

    this.open = false;
    this.results = [];

    // انتظر DOM ليتحدث قبل الإرسال
    setTimeout(() => {
        this.$el.closest('form').submit();
    }, 50); 
}
    }
}
</script>
{{-- JS للتصدير والطباعة --}}
<script src="{{ asset('js/xlsx.min.js') }}"></script><script>
function showGradeDetails(enrollment) {
    const tbody = document.getElementById('gradeDetails');
    tbody.innerHTML = '';

    if (!enrollment.grade) {
        tbody.innerHTML = '<tr><td colspan="2" class="text-center">لا توجد درجات</td></tr>';
    } else {
        const grade = enrollment.grade;
        for (const [key, value] of Object.entries(grade)) {
            if (['id','student_id','enrollment_id'].includes(key)) continue;
            tbody.innerHTML += `<tr>
                <td class="border px-2 py-1 font-semibold text-right">${key.replace('_',' ')}</td>
                <td class="border px-2 py-1 text-center">${value ?? '-'}</td>
            </tr>`;
        }
    }

    document.getElementById('gradeModal').classList.remove('hidden');
    document.getElementById('gradeModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('gradeModal').classList.add('hidden');
    document.getElementById('gradeModal').classList.remove('flex');
}

function exportExcel() {
    const table = document.querySelector('table');
    if (!table) return alert('لا توجد بيانات لتصديرها.');

    const data = [];
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(tr => {
        const rowData = {};
        tr.querySelectorAll('td').forEach((td,i)=>{ rowData[headers[i]]=td.innerText.trim(); });
        data.push(rowData);
    });

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Students");
    XLSX.writeFile(wb, "students-list.xlsx");
}
   function calculateCumulative(targetStart, targetEnd) {
    const allTables = Array.from(document.querySelectorAll('#printTables table'));

    let cumulativePoints = 0;
    let cumulativeUnits = 0;
    let completedUnits = 0;

    // ترتيب الجداول حسب تاريخ البداية
    const sortedTables = allTables.sort((a, b) => {
        const aStart = new Date(a.dataset.start);
        const bStart = new Date(b.dataset.start);
        return aStart - bStart;
    });

    for (const table of sortedTables) {
        const tableStart = new Date(table.dataset.start);
        const tableEnd = new Date(table.dataset.end);
        const targetStartDate = new Date(targetStart);
        const targetEndDate = new Date(targetEnd);



     function toYMD(date) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

function yearMonth(date) {
    return date.getFullYear() * 100 + (date.getMonth() + 1); 
    // مثلا 2022-02 ستكون 202202
}

const tableStartYM = yearMonth(tableStart);
const tableEndYM = yearMonth(tableEnd);
const targetStartYM = yearMonth(targetStartDate);
const targetEndYM = yearMonth(targetEndDate);

        // طباعة السيمستر الجاري لمعرفة ما يتم حسابه
     

// إذا نهاية الفصل قبل بداية النطاق أو بداية الفصل بعد نهاية النطاق، نتخطاه
if (tableEndYM >= targetStartYM && tableStartYM != targetStartYM) {
    continue;
}


        table.querySelectorAll('tbody tr').forEach(tr => {
            const tds = tr.querySelectorAll('td');
            if (!tds.length) return;
            if (tds[0].innerText.includes('مجاميع')) return;

            const subjectCode = tds[0].innerText;
            const units = parseFloat(tds[2].innerText) || 0;
            const gradeText = tds[4].innerText;
            if (!gradeText || gradeText === '-') return;

            const grade = parseFloat(gradeText);

            // تجاهل مشروع التخرج إذا الدرجة صفر
            if (subjectCode === 'SE452' && grade === 0) return;

            cumulativePoints += units * grade;
            cumulativeUnits += units;
            completedUnits += units;

        });
    }

    const cumulativeGpa = cumulativeUnits ? (cumulativePoints / cumulativeUnits).toFixed(2) : '-';

    return {
        cumulativeGpa,
        completedUnits
    };
}

//////////////////////////////////////////  طباعة كل الفصول  //////////////////////////
function printTable() {
  const tables = document.querySelectorAll('#printTables table');
    if (!tables.length) return alert('لا توجد بيانات للطباعة.');

    const grouped = {};
    
function formatSemesterName(name) {
    return name
        .replace('العام', 'الأول')
        .replace('مشروع التخرج', 'الثامن');
}

    // تجميع كل فصل حسب تاريخ البداية والنهاية
    tables.forEach(table => {
        const start = table.dataset.start;
        const end = table.dataset.end;
        const year = table.dataset.year;
        const term_type = table.dataset.term_type;
        const key = start + '_' + end; // استخدم التواريخ كمعرف

        if (!grouped[key]) {
            grouped[key] = {
                headers: [],
                rows: [],
                total_units: 0,
                total_hours: 0,
                gpa_list: [],
                year,
                term_type,
                start,
                 semester_name: '',
        semester_counts: {},
                end
            };
            grouped[key].headers = Array.from(table.querySelectorAll('thead th'))
                                        .map(th => th.innerText.trim())
                                        .map(h => h === 'المحاولة' ? 'الدور' : h);
        }

        
      table.querySelectorAll('tbody tr').forEach(tr => {
    const tds = Array.from(tr.querySelectorAll('td'));
    if (!tds.length) return;
    if (tds[0].innerText.includes('مجاميع')) return;

    const row = tds.map(td => td.innerText.trim());

    grouped[key].rows.push(row);

    grouped[key].total_units += parseFloat(tds[2].innerText) || 0;
    grouped[key].total_hours += parseFloat(tds[3].innerText) || 0;

    if (tds[4].innerText && tds[4].innerText !== '-') {
        grouped[key].gpa_list.push(parseFloat(tds[4].innerText));

        const units = parseFloat(tds[2].innerText) || 0;
const grade = parseFloat(tds[4].innerText);


    }

    // 👇 هذا هو السطر الوحيد المهم
    const semName = table.dataset.semester_name;
    grouped[key].semester_counts[semName] =
        (grouped[key].semester_counts[semName] || 0) + 1;
});
    });

    // بناء HTML للطباعة
let combinedHtml = '';
let cumulativePoints = 0;
let cumulativeUnits = 0;
for (const [key, data] of Object.entries(grouped)) {
     let maxName = '';
    let maxCount = 0;

    for (const name in data.semester_counts) {
        if (data.semester_counts[name] > maxCount) {
            maxCount = data.semester_counts[name];
            maxName = name;
        }
    }
data.rows.forEach(r => {

    const subjectCode = r[0]; // كود المادة
    const units = parseFloat(r[2]) || 0;
    const grade = parseFloat(r[4]);

    // تجاهل مشروع التخرج إذا الدرجة = 0
    if (subjectCode === 'SE452' && grade === 0) {
        return;
    }

    if (!isNaN(grade)) {
        cumulativePoints += grade * units;
        cumulativeUnits += units;
    }
});
const cumulativeGpa = cumulativeUnits
    ? (cumulativePoints / cumulativeUnits)
    : 0;
    data.semester_name = maxName;
    combinedHtml += `
    
    <div class="semester" style="page-break-inside: avoid; margin-bottom:30px;">
        <!-- جدول المواد مع صف اسم الفصل -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
            <thead>
                <tr>
                    <th colspan="3" style="border:1px solid #ccc; padding:8px; background:#d9edf7; text-align:center; font-weight:bold;">الفصل الدراسي ${formatSemesterName(data.semester_name)}  - ${data.term_type} ${data.year}  م   </th>
                </tr>
                <tr>
                    <th style="border:1px solid #ccc; padding:6px;">اسم المادة</th>
                    <th style="border:1px solid #ccc; padding:6px;">عدد الوحدات</th>
                    <th style="border:1px solid #ccc; padding:6px;">الدرجة</th>
                </tr>
            </thead>
            <tbody>
                ${data.rows.map(r => `
                    <tr>
                        <td style="border:1px solid #ccc; padding:6px; text-align:center;">${r[1]}</td>
                        <td style="border:1px solid #ccc; padding:6px; text-align:center;">${r[2]}</td>
                        <td style="border:1px solid #ccc; padding:6px; text-align:center;">${r[4]}</td>
                    </tr>
                `).join('')}
                <!-- صف المعدل الفصلي -->
                <tr style="background:#f0f0f0; font-weight:bold;">
                    <td colspan="2" style="border:1px solid #ccc; padding:8px; text-align:right;">المعدل الفصلي</td>
                    <td style="border:1px solid #ccc; padding:8px; text-align:center;">
                        ${data.gpa_list.length ? (data.gpa_list.reduce((a,b)=>a+b,0)/data.gpa_list.length).toFixed(2) : '-'}
                    </td>
                </tr>
                <!-- صف المعدل التراكمي -->
                <tr style="background:#f0f0f0; font-weight:bold;">
                    <td colspan="2" style="border:1px solid #ccc; padding:8px; text-align:right;">المعدل التراكمي</td>
                    <td style="border:1px solid #ccc; padding:8px; text-align:center;">
${cumulativeGpa.toFixed(2)}                    </td>
                </tr>
            </tbody>
        </table>
    </div>`;
}
    const footerHtml = `
    <div class="footer">
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>قسم الخريجين</p>
        </div>
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>المسجل العام</p>
        </div>
        <div style="text-align:center;">
            <p>_____________________</p>
            <p>عميد الكلية</p>
        </div>
    </div>
    `;
const studentData = @json($studentData ?? []);

const studentInfoHtml = `
<div style="
    margin-bottom:25px;
    line-height:2;
    font-size:15px;
">


    <table style="
        width:100%;
        border:none;
        margin-bottom:20px;
    ">
        <tr>
         <td style="border:none; text-align:right;">
                <strong>اسم الطالب:</strong>
        ${studentData.name}
            </td>
              </tr>
                <tr>
            <td style="border:none; text-align:right;">
                <strong>تاريخ الميلاد:</strong>
                ${studentData.birthYear ? studentData.birthYear + ' ' +'م' : '-'}
            </td>

          <td style="border:none; text-align:left;">
                ${
                    studentData.graduationYear != '-'
                    ? `
                    <strong>سنة التخرج:</strong>
                    ${studentData.graduationYear}
                    `
                    : ''
                }
            </td>
        </tr>

        <tr>
            <td style="border:none; text-align:right;">
                <strong>رقم القيد:</strong>
                ${studentData.manualNumber || '-'}
            </td>
              <td style="border:none; text-align:left;">
                <strong>الجنسية:</strong>
                ${studentData.nationality || '-'}
            </td>

            
        </tr>

        <tr>
            <td style="border:none; text-align:right;">
                <strong>التخصص:</strong>
                ${studentData.department || '-'}
            </td>

            <td style="border:none; text-align:left;">
                <strong>الشعبة:</strong>
                ${studentData.section || '-'}
            </td>
        </tr>
      
    </table>
      <h3 style="text-align:right; margin-bottom:5px;">
        فيما يلي التقديرات التي تحصل عليها الطالب علما بأن جميع التقديرات من ( مائة) درجة.
        </h3>
</div>
`;
const win = window.open('', '_blank', 'width=900,height=1200');
win.document.write(`
    <html>
        <head>
            <title>كشف الدرجات </title>
            <style>
                @page { size: A4; margin: 50px; }
                body { font-family: Arial; direction: rtl; margin:0; padding:0; }
                h1, h2 { text-align: center; margin: 5px 0; }
                h3 { margin-bottom:5px; text-align:right; }
                table { width:100%; border-collapse:collapse; page-break-inside: avoid; margin-bottom:20px; }
                th, td { border:1px solid #ccc; padding:6px; text-align:center; }
                th { background:#f0f0f0; }
                tr:last-child td { font-weight:bold; background:#d1ffd1; }
                .semester { page-break-inside: avoid; margin-bottom: 60px; }
                .footer { position: fixed; bottom: 0; width:100%; display:flex; justify-content: space-between; font-family: Arial; background:white; }
            </style>
        </head>
        <body>
            <h1>كشف الدرجات</h1>
            <h2>  ${studentInfoHtml} </h2> <!-- اسم الطالب -->
            ${combinedHtml}
            ${footerHtml}
        </body>
    </html>
`);
win.document.close();
win.focus();
win.print();
win.close();
}


//////////////////////////  طباعة فصل معين فقط  //////////////////////////
function printSemester(year, termType,targetStart, targetEnd) {
    const tables = Array.from(document.querySelectorAll('#printTables table'))
                        .filter(t => t.dataset.year === year && t.dataset.term_type === termType);

    if (!tables.length) {
        alert('لا توجد مواد لهذا الفصل');
        return;
    }

    let headers = [];
    let rows = [];
    let totalUnits = 0;
    let totalHours = 0;
    let totalGrades = 0;
    let countedGrades = 0;

    

    tables.forEach((table, tableIndex) => {
        if (tableIndex === 0) {
            headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());
        }

        table.querySelectorAll('tbody tr').forEach(tr => {
            const tds = Array.from(tr.querySelectorAll('td'));
            if (!tds.length) return;
            if (tds[0].innerText.includes('مجاميع')) return;

            rows.push(tds.map(td => td.innerText.trim()));

            totalUnits += parseFloat(tds[2].innerText) || 0;
            totalHours += parseFloat(tds[3].innerText) || 0;

            if (tds[4].innerText && tds[4].innerText !== '-') {
                totalGrades += parseFloat(tds[4].innerText) || 0;
                countedGrades++;
            }
        });
    });


const { cumulativeGpa, completedUnits } = calculateCumulative(targetStart, targetEnd);
    const studentName = @json($student->full_name ?? '');
    const studentNumber = @json($student->manual_number ?? $student->student_number ?? '');
    const studentDepartment = @json($student->department->name ?? $student->department ?? '-');
    const semesterUnits = totalUnits; // مجموع الوحدات في هذا الفصل
let totalWeightedGrades = 0;
const semesterCounts = {};

tables.forEach(table => {

 table.querySelectorAll('tbody tr').forEach(tr => {
        const tds = Array.from(tr.querySelectorAll('td'));
        if (!tds.length) return;
        if (tds[0].innerText.includes('مجاميع')) return;
        const units = parseFloat(tds[2].innerText) || 0;
        const grade = parseFloat(tds[4].innerText) || 0;
        if (grade) {
            totalWeightedGrades += units * grade;
        }
    const semName = table.dataset.semester_name || '-';

    const rowsCount = table.querySelectorAll('tbody tr').length;

    semesterCounts[semName] =
        (semesterCounts[semName] || 0) + rowsCount;
});
 });

let semesterName = '-';
let maxCount = 0;
let isEighthSemester = false;

// 1. الفحص المباشر عن وجود مادة مشروع التخرج (SE452) أو مسمى الفصل الثامن
tables.forEach(table => {
    table.querySelectorAll('tbody tr').forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (!tds.length) return;

        const subjectCode = tds[0].innerText.trim();
        const semName = table.dataset.semester_name || '';

        if (subjectCode === 'SE452' || semName.includes('مشروع التخرج') || semName.includes('الثامن')) {
            isEighthSemester = true;
            semesterName = semName.includes('مشروع التخرج') ? 'مشروع التخرج' : semName;
        }
    });
});

// 2. إذا لم تكن هناك مادة مشروع تخرج، نعتمد طريقة الأكثر تكراراً (الأغلبية)
if (!isEighthSemester) {
    for (const name in semesterCounts) {
        if (semesterCounts[name] > maxCount) {
            maxCount = semesterCounts[name];
            semesterName = name;
        }
    }
}

const semesterGpa = totalUnits ? (totalWeightedGrades / totalUnits).toFixed(2) : '-';

function formatSemesterName(name) {
    return name
        .replace('العام', 'الأول')
        .replace('مشروع التخرج', 'الثامن');
}const studentInfoHtml = `
    <div style="
        margin-bottom:20px;
        line-height:2;
        font-size:15px;
    ">
        <table style="width:100%; border:none;">

            <tr>
                <td style="border:none; text-align:right;">
                    <strong>اسم الطالب:</strong>
                    ${studentName}
                </td>
            </tr>

            <tr>
                <td style="border:none; text-align:right;">
                    <strong>رقم القيد:     </strong>
                    ${studentNumber}
                </td>
            </tr>

              <tr>
                <td style="border:none; text-align:right;">
                    <strong>القسم:     </strong>
                    ${studentDepartment}
                </td>
            </tr>

            <tr>
                <td style="border:none; text-align:right;">
                    <strong>الفصل الدراسي:</strong>
                    ${termType} ${year}
                </td>
            </tr>

            <tr>
                <td style="border:none; text-align:right;">
                    <strong>رقم الفصل:</strong>
                    ${formatSemesterName(semesterName)}
                </td>
            </tr>

          

        </table>
    </div>

`;
const headerHtml = `
    <div style="text-align:center; margin-bottom:30px; line-height:1.5; font-family:Arial;">
        <div style="font-size:16px;">وزارة التعليم العالي والبحث العلمي</div>
        <div style="font-size:16px;">الهيئة الوطنية للتعليم التقني والفني</div>
        <div style="font-size:16px;">كلية التقنية الهندسية زوارة</div>
        <div style="font-size:20px; font-weight:bold; margin-top:10px;">(كشف الدرجات)</div>
        <div style="font-size:16px; margin-top:5px;">
            الفصل الدراسي: ${termType} ${year}
        </div>
    </div>
`;
// حساب المعدل الفصلي والوحدات المنجزة

let summaryHtml = `
    <table style="width:100%; border:none;">

            <tr>
                        <td style="border:none; text-align:right;">
                            <strong>المعدل الفصلي:  </strong>
               ${semesterGpa}
                        </td>

              
              
                        <td style="border:none; text-align:right;">
                            <strong>    المواد المنجزة:   </strong>${completedUnits}</td>
            </tr>
            <tr>
                
                        <td style="border:none; text-align:right;">
                            <strong>المعدل التراكمي:  </strong>${cumulativeGpa}</td>
              
            </tr>
        </table>
    </div>
`;
   let tableHtml = `
    <table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;direction:rtl;margin-bottom:100px;">
        <thead>
            <tr>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">ر.ت</th>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">رمز المادة</th>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">اسم المادة</th>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">عدد الوحدات</th>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">الدرجة</th>
                <th style="border:1px solid #ccc;padding:8px;background:#f0f0f0;">الوحدة × الدرجة</th>
                <th style="border:1px solid #ccc; padding:8px; background:#f0f0f0;">ملاحظة</th> <!-- العمود الجديد -->
            </tr>
        </thead>
        <tbody>
            ${rows.map((r, index) => {
                const units = parseFloat(r[2]) || 0;
                const grade = parseFloat(r[4]) || 0;
                const unitXGrade = (units * grade).toFixed(2);

            const status = r[5]; // استخراج الحالة (ناجح، راسب، إلخ) من البيانات الأصلية

                return `<tr>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${index + 1}</td>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${r[0]}</td>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${r[1]}</td>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${r[2]}</td>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${r[4]}</td>
                    <td style="border:1px solid #ccc;padding:8px;text-align:center;">${unitXGrade}</td>
                    <td style="border:1px solid #ccc; padding:18px; text-align:center;">${status || ''}</td> <!-- عرض الحالة -->
                    
                </tr>`;
            }).join('')}
            <tr style="font-weight:bold;background:#d1ffd1;">
                <td colspan="3" style="text-align:right;border:1px solid #ccc;">المجموع</td>
                <td style="border:1px solid #ccc;text-align:center;">${totalUnits}</td>
                <td style="border:1px solid #ccc;text-align:center;">${totalGrades}</td>
                <td style="border:1px solid #ccc;text-align:center;">${(totalUnits * (totalGrades / countedGrades || 0)).toFixed(2)}</td>
                <td style="border:1px solid #ccc;"></td>
            </tr>
        </tbody>
    </table>
`;
    const signaturesHtml = `
        <div style="position: fixed; bottom: 50px; width: 100%; display: flex; justify-content: space-between; font-family: Arial;">
            <div style="text-align:center;">
                <p>_____________________</p>
                <p>قسم الدراسة والامتحانات</p>
            </div>
            <div style="text-align:center;">
                <p>_____________________</p>
                <p>المسجل العام</p>
            </div>
        </div>
    `;


    const win = window.open('', '_blank', 'width=900,height=1200');
    win.document.write(`
        <html>
        <head>
            <title>كشف درجات الفصل</title>
            <style>
                @page { size: A4; margin: 50px; }
                body { font-family: Arial; direction: rtl; margin:0; padding:0; }
                h1 { text-align: center; margin-bottom: 20px; }
                table th, table td { border:1px solid #ccc; padding:6px; text-align:center; }
                table th { background-color:#f0f0f0; }
                table tr:last-child td { font-weight:bold; background-color:#d1ffd1; }
            </style>
        </head>
        <body>
            ${headerHtml}
            ${studentInfoHtml}
            ${tableHtml}
           ${summaryHtml}
            ${signaturesHtml}
        </body>
        </html>
    `);

    win.document.close();
    win.focus();
    win.print();
    win.close();
}

</script>
@endsection
