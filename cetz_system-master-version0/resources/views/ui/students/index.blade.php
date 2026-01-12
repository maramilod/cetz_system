@extends('layouts.app')

@section('content')
@php
$srv = [];
if (isset($students) && $students->count()) {
    foreach ($students as $s) {
        $srv[] = [
            'id' => $s->id,
            'student_number' => (string)($s->student_number ?? ''),
            'name' => (string)($s->name ?? ''),
            'department' => (string)($s->department ?? ''),
            'status' => (string)($s->status ?? 'active'),
            'nationality' => (string)($s->nationality ?? ''),
            'gender' => (string)($s->gender ?? ''),
            'passport' => (string)($s->passport_number ?? ''),
            'institute' => (string)($s->institute ?? ''),
            'dob' => (string)($s->date_of_birth ?? ''),
        ];
    }
}
$all = array_values($srv);
$deptList = array_values(array_unique(array_map(fn($r)=>$r['department'], $all)));
@endphp

<div x-data='studentsPage({ dataset: @json($all), departments: @json($deptList) })' x-init="init()">
    <!-- ... نفس باقي الصفحة ... -->
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('studentsPage', (initial) => ({
        all: [],
        records: [],
        departments: initial.departments || [],
        filters: { department: '', status: 'all', search: '' },

        init() {
            this.all = Array.isArray(initial.dataset) ? initial.dataset : [];
            if (!this.departments.length) {
                this.departments = Array.from(new Set(this.all.map(r => r.department))).filter(Boolean);
            }
            this.applyFilters();
        },

        applyFilters() {
            const term = this.filters.search.trim().toLowerCase();
            this.records = this.all.filter(r => {
                const okDept = !this.filters.department || r.department === this.filters.department;
                const okStatus = this.filters.status === 'all' || r.status === this.filters.status;
                const okTerm = !term || (
                    String(r.name||'').toLowerCase().includes(term) ||
                    String(r.student_number||'').toLowerCase().includes(term)
                );
                return okDept && okStatus && okTerm;
            });
        },

        studentShowUrl(s){
            return '/students/' + s.id;
        },
        studentEditUrl(s){
            return '/students/' + s.id + '/edit';
        }
    }));
});
</script>

@endsection
