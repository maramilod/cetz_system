@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="teachersList(@js($teachers))">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <h1 class="text-2xl font-bold">إدارة الأساتذة</h1>

        <!-- إضافة أستاذ -->
  <form method="POST" action="{{ route('teachers.store') }}"
      class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-6">
    @csrf

    <input type="text" name="full_name" placeholder="الاسم الكامل" class="border rounded px-3 py-2" required>
    <input type="text" name="working_id" placeholder="الرقم الوظيفي" class="border rounded px-3 py-2" required>
    <input type="email" name="email" placeholder="البريد الإلكتروني" class="border rounded px-3 py-2" >

    <!-- الرتبة الأكاديمية -->
    <select name="academic_rank_id" class="border rounded px-3 py-2" required>
        <option value="">اختر الرتبة الأكاديمية</option>
        @foreach($academicRanks as $rank)
            <option value="{{ $rank->id }}">{{ $rank->name }}</option>
        @endforeach
    </select>

    <!-- الوضع الوظيفي -->
    <select name="employment_status_id" class="border rounded px-3 py-2" required>
        <option value="">اختر الوضع الوظيفي</option>
        @foreach($employmentStatuses as $status)
            <option value="{{ $status->id }}">{{ $status->name }}</option>
        @endforeach
    </select>

    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
        إضافة أستاذ
    </button>
</form>

<button
    class="bg-indigo-600 text-white px-4 py-2 rounded mb-4"
    @click="printAllTeachersCurrentSemester()">
    طباعة الأساتذة – الفصل الحالي فقط
</button>

<button class="bg-gray-600 text-white px-4 py-2 rounded mb-4" 
        @click="printAllTeachers()">
    طباعة كل الأساتذة
</button>
<button class="bg-green-600 text-white px-4 py-2 rounded mb-4" 
        @click="exportAllTeachersExcel()">
    تصدير كل الأساتذة إلى Excel
</button>

        <!-- قائمة الأساتذة -->
        <table class="min-w-full text-sm border">
           <thead class="bg-gray-100">
        <tr>
            <th class="border px-3 py-2 text-right">الاسم الكامل</th>
            <th class="border px-3 py-2 text-right">الرقم الوظيفي</th>
            <th class="border px-3 py-2 text-right">الرتبة الأكاديمية</th>
            <th class="border px-3 py-2 text-right">الوضع الوظيفي</th>
            <th class="border px-3 py-2 text-right">البريد الإلكتروني</th>
            <th class="border px-3 py-2 text-right">مجموع الساعات</th>
<th class="border px-3 py-2 text-right">مجموع الوحدات</th>

            <th class="border px-3 py-2 text-right">الحالة</th>
            <th class="border px-3 py-2 text-right">إجراءات</th>
        </tr>
    </thead>
            <tbody>
                <template x-for="teacher in teachers" :key="teacher.id">
                    <tr class="hover:bg-gray-50">
                        <td class="border px-3 py-2" x-text="teacher.full_name"></td>
                        <td class="border px-3 py-2" x-text="teacher.working_id"></td>
<td class="border px-3 py-2"  x-text="teacher.academic_rank"></td>
<td  class="border px-3 py-2"  x-text="teacher.employment_status"></td>



                        <td class="border px-3 py-2" x-text="teacher.email"></td>
                            
<td class="border px-3 py-2" x-text="teacher.total_hours"></td>
<td class="border px-3 py-2" x-text="teacher.total_units"></td>
                        <td class="border px-3 py-2">
                            <input type="checkbox" x-model="teacher.active" @change="toggleActive(teacher)" class="rounded">
                        </td>
                        <td class="border px-3 py-2 flex gap-2">
                            <button type="button" class="bg-blue-600 text-white px-2 py-1 rounded" @click="editTeacher(teacher)">تعديل</button>
                              <button type="button"
        class="bg-yellow-600 text-white px-2 py-1 rounded"
        @click="goToPromotion(teacher.id)">
        ترقية
    </button>
                            <button type="button" class="bg-red-600 text-white px-2 py-1 rounded" @click="deleteTeacher(teacher)">حذف</button>
                 <button 
    class="bg-gray-600 text-white px-4 py-2 rounded mb-4" 
    @click="printReport(teacher)">
    طباعة تقرير هذا الأستاذ
</button>



                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('teachersList', (initialTeachers) => ({
        teachers: initialTeachers,
        newTeacher: { full_name: '', working_id: '', email: '', active: true },

        addTeacher() {
            if(!this.newTeacher.full_name || !this.newTeacher.working_id || !this.newTeacher.email) return;
            const id = Date.now();
            this.teachers.push({ ...this.newTeacher, id });
            this.newTeacher = { full_name: '',  working_id: '', email: '', active: true };
        },
  // تعديل أستاذ
        editTeacher(teacher) {
            const full_name = prompt("الاسم الكامل:", teacher.full_name);
            if(full_name === null) return;
         
            const working_id = prompt("الرقم الوظيفي:", teacher.working_id);
            if(working_id === null) return;
            const email = prompt("البريد الإلكتروني:", teacher.email);
            if(email === null) return;

            axios.put(`/teachers/${teacher.id}`, {
                full_name,
                working_id,
                email
            })
           
                teacher.full_name = full_name;
                teacher.working_id = working_id;
                teacher.email = email;
                alert('تم تعديل الأستاذ بنجاح');
           
          
        },
deleteTeacher(teacher) {
  const message = `⚠️ تنبيه هام:
حذف الأستاذ "${teacher.full_name}" سيؤدي إلى حذف كل البيانات المرتبطة به تلقائيًا، 
بما في ذلك:
- الرتب الأكاديمية
- الوضعيات الوظيفية
- التعيينات التدريسية

هل أنت متأكد أنك تريد المتابعة؟`;

    if (confirm(message)) {        axios.delete(`/teachers/${teacher.id}`)
        
            this.teachers = this.teachers.filter(t => t.id !== teacher.id);
        
    }
},


        toggleActive(teacher) {
            axios.patch(`/teachers/${teacher.id}/toggle-active`, {
                active: teacher.active ? 1 : 0
            })
            .then(res => {
                console.log(`${teacher.full_name} updated active: ${teacher.active}`);
            })
            .catch(err => {
                console.error(err);
                alert('حدث خطأ أثناء تحديث الحالة');
                teacher.active = !teacher.active;
            });
        },
        goToPromotion(teacherId) {
    window.location.href = `/teachers/${teacherId}/promotion`;
},
printReport(teacher) {
    if (!teacher) return alert('لم يتم اختيار أستاذ للطباعة');

    let html = `<html lang="ar">
    <head>
        <meta charset="UTF-8">
        <title>تقرير الأستاذ</title>
        <style>
            body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
            h1, h2, h3 { text-align: center; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
            th { background-color: #f0f0f0; }
            hr { border: 1px solid #ccc; margin: 40px 0; }
        </style>
    </head>
    <body>
        <h1>تقرير الأستاذ</h1>
        <h2>${teacher.full_name} - ${teacher.working_id}</h2>
    `;

    // جدول الرتب والحالة الوظيفية
    let maxRows = Math.max(teacher.ranks?.length || 0, teacher.employment_statuses?.length || 0);
    html += `<table>
        <thead>
            <tr>
                <th>الرتبة الأكاديمية</th>
                <th>من تاريخ</th>
                <th>إلى تاريخ</th>
                <th>الوضع الوظيفي</th>
                <th>من تاريخ</th>
                <th>إلى تاريخ</th>
            </tr>
        </thead>
        <tbody>`;
    for (let i = 0; i < maxRows; i++) {
        let rank = teacher.ranks[i] || {};
        let status = teacher.employment_statuses[i] || {};
        html += `<tr>
            <td>${rank.academicRank || '-'}</td>
            <td>${rank.from_date || '-'}</td>
            <td>${rank.to_date || 'الآن'}</td>
            <td>${status.employmentStatus || '-'}</td>
            <td>${status.from_date || '-'}</td>
            <td>${status.to_date || 'الآن'}</td>
        </tr>`;
    }
    html += `</tbody></table>`;

    // جدول عدد الوحدات والساعات لكل فصل
    let semesterData = {};
    teacher.teachingAssignments.forEach(a => {
        let semester = a.semester_name || 'غير محدد';
        if (!semesterData[semester]) semesterData[semester] = { total_units: 0, total_hours: 0 };
        semesterData[semester].total_units += parseFloat(a.course_units || 0);
        semesterData[semester].total_hours += parseFloat(a.course_hours || 0);
    });

    if (Object.keys(semesterData).length) {
        html += `<h3>عدد الوحدات والساعات لكل فصل</h3>
            <table>
                <thead>
                    <tr>
                        <th>الفصل الدراسي</th>
                        <th>عدد الوحدات</th>
                        <th>عدد الساعات</th>
                    </tr>
                </thead>
                <tbody>`;
        for (let sem in semesterData) {
            html += `<tr>
                <td>${sem}</td>
                <td>${semesterData[sem].total_units}</td>
                <td>${semesterData[sem].total_hours}</td>
            </tr>`;
        }
        html += `</tbody></table>`;
    }

    html += `</body></html>`;

    // الطباعة عبر iframe مخفي في نفس الصفحة
    let iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);
    iframe.contentDocument.open();
    iframe.contentDocument.write(html);
    iframe.contentDocument.close();
    iframe.contentWindow.focus();
    iframe.contentWindow.print();
    document.body.removeChild(iframe);
},
printAllTeachers() {
    if (!this.teachers.length) return alert('لا يوجد أساتذة للطباعة');

    let html = `<html lang="ar">
    <head>
        <meta charset="UTF-8">
        <title>تقرير كل الأساتذة</title>
        <style>
            body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
            th { background-color: #f0f0f0; }
        </style>
    </head>
    <body>
        <h1 style="text-align:center;">تقرير كل الأساتذة</h1>
        <table>
            <thead>
                <tr>
                    <th>الاسم الكامل</th>
                    <th>الرقم الوظيفي</th>
                    <th>الرتبة الأكاديمية</th>
                    <th>الوضع الوظيفي</th>
                    <th>البريد الإلكتروني</th>
                    <th>مجموع الساعات</th>
                    <th>مجموع الوحدات</th>
                </tr>
            </thead>
            <tbody>`;

    this.teachers.forEach(t => {
        html += `<tr>
            <td>${t.full_name}</td>
            <td>${t.working_id}</td>
            <td>${t.academic_rank || '-'}</td>
            <td>${t.employment_status || '-'}</td>
            <td>${t.email || '-'}</td>
            <td>${t.total_hours || 0}</td>
            <td>${t.total_units || 0}</td>
        </tr>`;
    });

    html += `</tbody></table></body></html>`;

    // الطباعة عبر iframe مخفي في نفس الصفحة
    let iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    let doc = iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    setTimeout(() => document.body.removeChild(iframe), 1000);
},
exportAllTeachersExcel() {
    if (!this.teachers.length) return alert('لا توجد بيانات لتصديرها.');

    // بناء مصفوفة بيانات لكل أستاذ
    const data = this.teachers.map(t => ({
        "الاسم الكامل": t.full_name,
        "الرقم الوظيفي": t.working_id,
        "الرتبة الأكاديمية": t.academic_rank || '-',
        "الوضع الوظيفي": t.employment_status || '-',
        "البريد الإلكتروني": t.email || '-',
        "مجموع الساعات": t.total_hours || 0,
        "مجموع الوحدات": t.total_units || 0,
    }));

    // إنشاء ورقة عمل وملف Excel
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Teachers");
    XLSX.writeFile(wb, "teachers-list.xlsx");
},
printAllTeachersCurrentSemester() {
    if (!this.teachers.length) return alert('لا توجد بيانات للطباعة.');

    let html = `<html lang="ar">
    <head>
        <meta charset="UTF-8">
        <title>تقرير كل الأساتذة - الفصل الحالي</title>
        <style>
            body { font-family: Arial, sans-serif; direction: rtl; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
            th { background-color: #f0f0f0; }
        </style>
    </head>
    <body>
        <h1 style="text-align:center;">تقرير كل الأساتذة - الفصل الحالي</h1>
        <table>
            <thead>
                <tr>
                    <th>الاسم الكامل</th>
                    <th>الرقم الوظيفي</th>
                    <th>الرتبة الأكاديمية</th>
                    <th>الوضع الوظيفي</th>
                    <th>البريد الإلكتروني</th>
                    <th>مجموع الساعات - الفصل الحالي</th>
                    <th>مجموع الوحدات - الفصل الحالي</th>
                </tr>
            </thead>
            <tbody>`;

    this.teachers.forEach(t => {
        html += `<tr>
            <td>${t.full_name}</td>
            <td>${t.working_id}</td>
            <td>${t.academic_rank || '-'}</td>
            <td>${t.employment_status || '-'}</td>
            <td>${t.email || '-'}</td>
            <td>${t.current_semester_hours || 0}</td>
            <td>${t.current_semester_units || 0}</td>
        </tr>`;
    });

    html += `</tbody></table></body></html>`;

    // الطباعة عبر iframe مخفي
    let iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    let doc = iframe.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();

    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    setTimeout(() => document.body.removeChild(iframe), 1000);
}

    }));
});
</script>
@endsection
