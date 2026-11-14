@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">📋 نموذج حضور وغياب - البحث الذكي</h1>
        <button class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm shadow-sm" onclick="printTable()">
            🖨️ طباعة
        </button>
    </div>

    {{-- خانة البحث مع حاوية المقترحات --}}
    <div class="bg-white rounded-xl shadow-sm border p-5">
        <div class="w-full relative">
            <label class="block text-sm font-medium text-gray-700 mb-2">ابحث واكتب اسم المادة أو القسم:</label>
            <input type="text" id="smartSearch" autocomplete="off" placeholder="اكتب حرفاً مثل (ر) لتظهر الخيارات..."
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition">
            
            {{-- قائمة المقترحات المنسدلة (تظهر وتختفي ديناميكياً) --}}
            <div id="suggestionsWrapper" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto divide-y divide-gray-100"></div>

            <div id="searchLoader" class="hidden absolute left-4 bottom-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div id="cardsContainer" class="space-y-6">
        <div class="text-center text-gray-400 py-12 bg-white rounded-xl border border-dashed">
            🔍 يرجى اختيار مادة من المقترحات لعرض جدول الحضور والغياب...
        </div>
    </div>
</div>

<script>
let globalAttendanceData = []; 
let debounceTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('smartSearch');
    const suggestionsWrapper = document.getElementById('suggestionsWrapper');
    const loader = document.getElementById('searchLoader');
    const container = document.getElementById('cardsContainer');

    // 1. الاستماع للكتابة لجلب المقترحات (تظهر حتى من أول حرف)
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();

        clearTimeout(debounceTimeout);

        if (query.length < 1) {
            suggestionsWrapper.innerHTML = '';
            suggestionsWrapper.classList.add('hidden');
            return;
        }

        debounceTimeout = setTimeout(() => {
            loader.classList.remove('hidden');

            // جلب المقترحات السريعة من السيرفر
            fetch(`{{ route('registration.attendance-suggestions') }}?term=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(suggestions => {
                loader.classList.add('hidden');
                
                if (suggestions.length === 0) {
                    suggestionsWrapper.innerHTML = '<div class="p-3 text-sm text-gray-500">لا توجد خيارات مطابقة</div>';
                    suggestionsWrapper.classList.remove('hidden');
                    return;
                }

                // بناء أزرار الخيارات داخل القائمة
                let html = '';
                suggestions.forEach(item => {
                    html += `
                        <button type="button" class="w-full text-right px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition block focus:outline-none" 
                                data-value="${item.value}">
                            🎯 ${item.label}
                        </button>
                    `;
                });

                suggestionsWrapper.innerHTML = html;
                suggestionsWrapper.classList.remove('hidden');
            });
        }, 300);
    });

    // 2. عند الضغط على خيار من القائمة المنسدلة
    suggestionsWrapper.addEventListener('click', (e) => {
        const button = e.target.closest('button');
        if (!button) return;

        const selectValue = button.getAttribute('data-value');
        searchInput.value = selectValue; // وضع النص المختار في خانة البحث
        suggestionsWrapper.classList.add('hidden'); // إخفاء القائمة

        // إطلاق دالة جلب الجداول الفعلية بناء على الخيار الذي تم نقره
        fetchFinalTables(selectValue);
    });

    // إخفاء القائمة عند النقر خارجها
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsWrapper.contains(e.target)) {
            suggestionsWrapper.classList.add('hidden');
        }
    });

    // 3. دالة جلب البيانات النهائية ورسم الجداول
    function fetchFinalTables(queryValue) {
        loader.classList.remove('hidden');
        container.innerHTML = '<div class="text-center py-12 text-gray-500">جاري جلب الجداول...</div>';

        fetch(`{{ route('registration.attendance-search') }}?term=${encodeURIComponent(queryValue)}`)
        .then(res => res.json())
        .then(data => {
            loader.classList.add('hidden');
            globalAttendanceData = data;

            if (data.length === 0) {
                container.innerHTML = '<div class="text-center py-12 text-red-500 border rounded-xl bg-white">لم يتم العثور على جداول لهذه المادة.</div>';
                return;
            }

            let html = '';
            data.forEach(item => {
                let studentRows = '';
                if (item.students.length > 0) {
                    item.students.forEach((student, index) => {
                        studentRows += `
                            <tr class="hover:bg-gray-50 transition">
                                <td class="border px-3 py-2 text-center">${index + 1}</td>
                                <td class="border px-3 py-2 text-center">${student.student_number}</td>
                                <td class="border px-3 py-2 text-right">${student.name}</td>
                                <td class="border px-3 py-2 text-right">${student.department_name}</td>
                              
                            </tr>`;
                    });
                } else {
                    studentRows = `<tr><td colspan="7" class="text-center py-4 text-gray-500">لا يوجد طلاب مسجلين.</td></tr>`;
                }

                html += `
                    <div class="bg-white rounded-xl shadow-sm border p-4">
                        <div class="mb-4 border-b pb-3">
                            <h2 class="font-bold text-xl text-gray-800">${item.course_name}</h2>
                            <div class="text-sm text-gray-600 mt-1">القسم/الشعبة: <strong>${item.section_name}</strong></div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border-collapse border border-gray-200">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th class="border px-3 py-2 text-center">#</th>
                                        <th class="border px-3 py-2 text-center">رقم الطالب</th>
                                        <th class="border px-3 py-2 text-right">الاسم</th>
                                        <th class="border px-3 py-2 text-right">القسم</th>
                                       
                                    </tr>
                                </thead>
                                <tbody>${studentRows}</tbody>
                            </table>
                        </div>
                    </div>`;
            });
            container.innerHTML = html;
        });
    }
});

// دالة الطباعة المحدثة المخصصة لـ A4 العرضية متجاوبة المساحة
// دالة الطباعة المحدثة المخصصة لـ A4 العرضية متجاوبة المساحة
function printTable() {
    if (globalAttendanceData.length === 0) {
        alert('لا توجد بيانات معروضة حالياً لطباعتها! يرجى البحث أولاً.');
        return;
    }

    let tableHtml = '';

    globalAttendanceData.forEach(item => {
        let tableContent = '';

        if (item.students.length === 0) {
            tableContent = '<p>لا يوجد طلاب مسجلين</p>';
        } else {
            tableContent = `
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th rowspan="2">ت.ر</th>
                            <th rowspan="2">اسم الطالب</th>
                            <th rowspan="2">رقم القيد</th>
                            <th rowspan="2">القسم</th>
                            <th colspan="17">الأسبوع رقم</th>
                        </tr>
                        <tr>
                            ${Array.from({length: 17}, (_, i) => `<th>${i + 1}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${item.students.map((student, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td style="text-align: right; padding-right: 5px;">${student.name}</td>
                                <td>${student.student_number}</td>
                                <td>${student.department_name}</td>${Array.from({length: 17}, () => `<td></td>`).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        tableHtml += `
            <div style="margin-bottom: 40px; page-break-after: always;">
                <div style="margin-bottom: 5px;"><strong>اسم المادة:</strong> ${item.course_name}</div>
                <div style="margin-bottom: 5px;"><strong>الفصل الدراسي:</strong> ${item.semester_type || ''} ${item.semester_year || ''}</div>
                <div style="margin-bottom: 15px;"><strong>القسم / الشعبة:</strong> ${item.section_name}</div>
                ${tableContent}
            </div>
        `;
    });

    const newWin = window.open('', '_blank', 'width=1100,height=700');
    newWin.document.write(`
        <html>
            <head>
                <title>نموذج حضور وغياب</title>
                <style>
                    /* إجبار المتصفح على طباعة الصفحة بالعرض (Landscape) */
                    @page { 
                        size: A4 landscape; 
                        margin: 8mm; 
                    }
                    
                    body { 
                        font-family: Arial, sans-serif; 
                        direction: rtl; 
                        margin: 0; 
                        padding: 0;
                        width: 100%;
                    }
                    
                    .print-letterhead { 
                        text-align: center; 
                        line-height: 1.6; 
                        margin-bottom: 15px; 
                        font-size: 14px; 
                        font-weight: bold;
                    }
                    
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-bottom: 20px; 
                        table-layout: fixed; 
                    }
                    
                    th, td { 
                        border: 1px solid #333; 
                        padding: 4px 2px; 
                        text-align: center; 
                        font-size: 11px; 
                        word-wrap: break-word;
                    }
                    
                    th { 
                        background-color: #e2e8f0; 
                        color: #000;
                    }
                    
                    /* تحديد نسب عرض الأعمدة لضمان عدم تداخل أسابيع السنة */
                    th:nth-child(1), td:nth-child(1) { width: 4%; }   /* ت.ر */
                    th:nth-child(2), td:nth-child(2) { width: 18%; text-align: right; } /* اسم الطالب */
                    th:nth-child(3), td:nth-child(3) { width: 9%; }   /* رقم القيد */
                    th:nth-child(4), td:nth-child(4) { width: 10%; }  /* القسم */
                    /* أعمدة الأسابيع الـ 17 الباقية ستتوزع بمرونة على المساحة المتبقية */

                    @media print {
                        body { 
                            -webkit-print-color-adjust: exact; 
                            print-color-adjust: exact; 
                        }
                        table { page-break-inside: auto; }
                        tr { page-break-inside: avoid; page-break-after: auto; }
                        thead { display: table-header-group; }
                    }
                </style>
            </head>
            <body>
                <div class="print-letterhead">
                    <div>وزارة التعليم التقني والفني</div>
                    <div>الهيئة الوطنية للتعليم التقني والفني</div>
                    <div>كلية التقنية الهندسية زوارة</div>
                </div>

                ${tableHtml}
            </body>
        </html>
    `);
    
    newWin.document.close();
    newWin.focus();
    
    // تأخير بسيط لضمان تحميل العناصر بالكامل قبل إظهار نافذة الطباعة
    setTimeout(() => {
        newWin.print();
        newWin.close();
    }, 400);
}
</script>
@endsection