<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDepartmentRequest;
use App\Models\Department;
use App\Services\Shared\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::query()->withCount(['positions', 'users'])->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.departments.form', ['department' => new Department]);
    }

    public function store(SaveDepartmentRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $department = Department::create($request->validated() + ['is_active' => true]);
        $auditLog->record($request->user(), $department, 'DEPARTMENT_CREATED', [
            'changed_fields' => array_keys($request->validated()),
        ]);

        return redirect()->route('admin.departments.index')->with('status', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.form', compact('department'));
    }

    public function update(SaveDepartmentRequest $request, Department $department, AuditLogService $auditLog): RedirectResponse
    {
        $department->update($request->validated());
        $auditLog->record($request->user(), $department, 'DEPARTMENT_UPDATED', [
            'changed_fields' => array_keys($request->validated()),
        ]);

        return redirect()->route('admin.departments.index')->with('status', 'Departemen berhasil diperbarui.');
    }
}
