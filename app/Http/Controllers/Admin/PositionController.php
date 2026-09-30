<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePositionRequest;
use App\Models\Department;
use App\Models\Position;
use App\Services\Shared\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        return view('admin.positions.index', [
            'positions' => Position::query()->with('department')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Position);
    }

    public function store(SavePositionRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $position = Position::create($request->validated() + ['is_active' => true]);
        $auditLog->record($request->user(), $position, 'POSITION_CREATED', [
            'changed_fields' => array_keys($request->validated()),
        ]);

        return redirect()->route('admin.positions.index')->with('status', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position): View
    {
        return $this->form($position);
    }

    public function update(SavePositionRequest $request, Position $position, AuditLogService $auditLog): RedirectResponse
    {
        $position->update($request->validated());
        $auditLog->record($request->user(), $position, 'POSITION_UPDATED', [
            'changed_fields' => array_keys($request->validated()),
        ]);

        return redirect()->route('admin.positions.index')->with('status', 'Jabatan berhasil diperbarui.');
    }

    private function form(Position $position): View
    {
        return view('admin.positions.form', [
            'position' => $position,
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }
}
