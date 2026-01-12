<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UI\UiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MaterialDownloadController;
use App\Http\Controllers\CourseRegistrationController;
use App\Http\Controllers\GradesController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TeachingAssignmentController;




use App\Http\Middleware\PermissionMiddleware;

Route::get('/test-permission', function () {
    return 'Middleware يعمل!';
})->middleware(PermissionMiddleware::class.':manage_users');


// ================= Authentication =================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth']], function () {
 
// ================= Protected Routes =================


    // ---------- Dashboard ----------
    Route::get('/', [UiController::class, 'dashboard'])->name('dashboard');



    // صفحة نموذج الحضور
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->name('registration.attendance-form');

// إذا أردنا لاحقًا حفظ الحضور (POST)
Route::post('/attendance/save', [AttendanceController::class, 'save'])
    ->name('attendance.save');
    // ---------- Registration ----------
    Route::prefix('registration')->group(function () {
        Route::get('courses', [CourseRegistrationController::class, 'index'])->name('registration.courses');
        Route::get('courses/print', [CourseRegistrationController::class, 'print'])->name('registration.courses.print');



       // Route::view('/enrollment-stop', 'registration.enrollment-stop')->name('registration.enrollment-stop');

       // Route::view('attendance-form', 'registration.attendance-form')->name('registration.attendance-form');

       Route::get('student-certificate', [StudentController::class, 'createCertificate'])
    ->name('registration.student-certificate');

        Route::view('bank-report', 'registration.bank-report')->name('registration.bank-report');
        Route::view('department-report', 'registration.department-report')->name('registration.department-report');
    });

    // ---------- Graduates ----------
    Route::prefix('graduates')->name('graduates.')->group(function () {
        Route::view('transcript', 'graduates.transcript')->name('transcript');
        Route::view('list', 'graduates.list')->name('list');
    });

    // ---------- Data Management ----------
    Route::prefix('data-management')->name('data.')->group(function () {
        Route::view('backup', 'data_management.backup')->name('backup');
        Route::view('restore', 'data_management.restore')->name('restore');
        Route::view('reset', 'data_management.reset')->name('reset');
        Route::view('change-password', 'data_management.change-password')->name('change-password');
       // Route::view('users', 'data_management.users')->name('users');
        Route::view('institute-number', 'data_management.institute-number')->name('institute-number');
        Route::view('institute-info', 'data_management.institute-info')->name('institute-info');
    });

    // ---------- Accreditation ----------
    Route::view('accreditation', 'accreditation.index')->name('accreditation.index');
    Route::view('accreditation/student-approval', 'accreditation.student-approval')->name('accreditation.students');
    Route::view('accreditation/results-approval', 'accreditation.results-approval')->name('accreditation.results');

    // ---------- Study & Exams ----------
    Route::prefix('study-exams')->group(function () {
        Route::get('results', [GradesController::class, 'index'])->name('results.index');
Route::post('results', [GradesController::class, 'store'])->name('grades.store');
        Route::view('deprived', 'study_exams.deprived')->name('deprived.index');
        Route::view('grades', 'study_exams.grades')->name('grades.index');
        Route::view('final-results', 'study_exams.final-results')->name('final-results.index');
        Route::view('analysis', 'study_exams.analysis')->name('analysis.index');
        Route::view('projects', 'study_exams.projects')->name('projects.index');
        Route::view('projects-graduates', 'study_exams.projects-graduates')->name('projects.graduates');
        Route::view('second-round', 'study_exams.second-round')->name('second-round.index');
        Route::view('deprived-list', 'study_exams.deprived-list')->name('deprived-list.index');
        Route::view('grade-sheet', 'study_exams.grade-sheet')->name('grade-sheet.index');
        Route::view('statistics', 'study_exams.statistics')->name('statistics.index');
        Route::view('warnings', 'study_exams.warnings')->name('warnings.index');
    });

    // ---------- Users Management ----------
    Route::prefix('users')->middleware(PermissionMiddleware::class.':manage_users')->group(function () {
        Route::get('/', [UserController::class, 'index']);               // JSON
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{user}/toggle', [UserController::class, 'toggleStatus']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });
    Route::get('/users-page', [UserController::class, 'showPage'])->middleware(PermissionMiddleware::class.':manage_users')->name('users.index');

    // ---------- Roles Management ----------
    Route::prefix('roles')->middleware(PermissionMiddleware::class.':manage_roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);              
        Route::post('/', [RoleController::class, 'store']);             
        Route::put('/{role}', [RoleController::class, 'update']);       
        Route::delete('/{role}', [RoleController::class, 'destroy']);   
    });
    Route::get('/roles-page', [RoleController::class, 'showPage'])->middleware(PermissionMiddleware::class.':manage_roles')->name('roles.index');

    // ---------- Permissions ----------
    Route::prefix('permissions')->group(function () {
        Route::post('/', [PermissionController::class, 'store']);       
        Route::delete('/{permission}', [PermissionController::class, 'destroy']);
    });

    // ---------- Classrooms, Subjects, Departments ----------
    Route::resource('classrooms', ClassroomController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('departments', DepartmentController::class);
    Route::patch('departments/{department}/toggle', [DepartmentController::class, 'toggle'])->name('departments.toggle');


    Route::prefix('sections')->name('sections.')->group(function() {
    Route::get('/create/{department}', [SectionController::class, 'create'])->name('create');
    Route::post('/', [SectionController::class, 'store'])->name('store');
    Route::get('/{section}/edit', [SectionController::class, 'edit'])->name('edit');
    Route::put('/{section}', [SectionController::class, 'update'])->name('update');
    Route::delete('/{section}', [SectionController::class, 'destroy'])->name('destroy');
    Route::patch('/toggle/{section}', [SectionController::class, 'toggle'])->name('toggle');
});


Route::prefix('teaching-assignments')->name('teaching-assignments.')->group(function () {
    Route::get('/', [TeachingAssignmentController::class, 'index'])->name('index');
    Route::get('/create', [TeachingAssignmentController::class, 'create'])->name('create');
    Route::post('/', [TeachingAssignmentController::class, 'store'])->name('store');

    Route::get('/{id}/edit', [TeachingAssignmentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TeachingAssignmentController::class, 'update'])->name('update');
    Route::delete('/{id}', [TeachingAssignmentController::class, 'destroy'])->name('destroy');

    Route::get('/print', [TeachingAssignmentController::class, 'print'])->name('print');
});

  //  Route::resource('subject-distributions', SubjectDistributionController::class);
    //Route::get('subject-distributions/print', [SubjectDistributionController::class, 'print'])->name('subject-distributions.print');


Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::delete('/courses/{id}', [CourseController::class, 'destroy'])
    ->name('courses.destroy');
Route::patch('/courses/{id}/drop', [CourseController::class, 'drop'])
    ->name('courses.drop');

Route::patch('/courses/{id}/restore', [CourseController::class, 'restore'])
    ->name('courses.restore');


   Route::delete('/enrollment/{id}', [CourseController::class, 'deleteEnrollment'])->name('courses.enrollment.delete');
    Route::post('/update-course', [CourseController::class, 'updateCourse'])->name('courses.update');
    

    // ---------- Students Excel ----------
    Route::get('/students/excel', [StudentController::class, 'excel'])->name('students.excel');

    // ---------- Students CRUD ----------

Route::prefix('students')->name('students.')->group(function() {
    Route::get('/', [StudentController::class, 'index'])->name('index');        // قائمة الطلاب
    Route::get('/create', [StudentController::class, 'create'])->name('create'); // نموذج إضافة طالب
    Route::post('/', [StudentController::class, 'store'])->name('store');        // حفظ طالب جديد
    Route::get('/{student}', [StudentController::class, 'show'])->name('show');  // عرض طالب
    Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit'); // تعديل طالب
    Route::put('/{student}', [StudentController::class, 'update'])->name('update'); // تحديث طالب
    Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy'); // حذف طالب
});

 // ---------- Teachers CRUD ----------

Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
Route::patch('/teachers/{teacher}/toggle-active', [TeacherController::class, 'toggleActive'])
    ->name('teachers.toggle-active');


    Route::get('/enrollment-stop', [StudentController::class, 'enrollmentStop'])
    ->name('registration.enrollment-stop');
    Route::post('/enrollments/{id}/update-status', [StudentController::class, 'updateStatus']);



Route::get('semesters', [SemesterController::class, 'index'])->name('semesters.index');
Route::post('semesters', [SemesterController::class, 'store'])->name('semesters.store');
Route::get('/semesters/by-start-date', [CourseController::class, 'getSemestersByStartDate']);

Route::put('/semesters/package', [SemesterController::class, 'updatePackage'])->name('semesters.updatePackage');
Route::delete('/semesters/package', [SemesterController::class, 'destroyPackage'])->name('semesters.destroyPackage');
Route::get('/semesters/by-date-range', [CourseController::class, 'getSemestersByDateRange']);

Route::get('download-materials/print', [MaterialDownloadController::class, 'print'])->name('materials.download.print');
Route::get('download-materials', [MaterialDownloadController::class, 'index'])->name('materials.download');
Route::post('/enrollments', [EnrollmentController::class, 'store']);
// routes/web.php
Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);

//Route::get('/students/{student}/enrollments/{semester}', [EnrollmentController::class, 'currentEnrollments'])
 //   ->name('students.enrollments');


});
