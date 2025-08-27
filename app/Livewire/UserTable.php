<?php

namespace App\Livewire;

use App\Models\User;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Illuminate\Database\Eloquent\Builder;

class UserTable extends DataTableComponent
{
    protected $model = User::class;
    public function configure(): void
    {
        $this->setPrimaryKey('user_id'); // Primary key

        // Search delay biar gak terlalu berat
        $this->setSearchDebounce(500);

        // Tambahin per-page selector (misal 10, 25, 50, 100)
        $this->setPerPageAccepted([10, 25, 50, 100]);
        $this->setDefaultPerPage(10);

        // Tambahin sorting default (misalnya berdasarkan created_at desc)
        $this->setDefaultSort('created_at', 'desc');

        // Tambahin custom placeholder untuk search
        $this->setSearchPlaceholder('🔍 Cari user...');

        // // Aktifkan toolbar
        // $this->setConfigurableAreas([
        //     'toolbar-left-start' => 'components.table.toolbar-left',
        //     'toolbar-right-end' => 'components.table.toolbar-right',
        // ]);

        // Tambahin empty message kalau data kosong
        $this->setEmptyMessage('😢 Tidak ada data ditemukan');

        // // Aktifkan highlight row ketika di-hover
        // $this->setTableRowClass(function($row) {
        //     return 'hover:bg-gray-100 transition duration-200';
        // });

        // Tambahin responsive
        $this->setTableWrapperAttributes([
            'class' => 'overflow-x-auto shadow-md rounded-lg border border-gray-200',
        ]);

        $this->setTableAttributes([
            'class' => 'min-w-full divide-y divide-gray-200 bg-white text-black',
        ]);

        $this->setTheadAttributes([
            'class' => 'bg-gray-100 text-black',
        ]);

        $this->setThAttributes(fn($column) => [
            'class' => 'px-4 py-2 text-left text-sm font-semibold text-gray-700',
        ]);

        $this->setTdAttributes(fn($column, $row, $columnIndex, $rowIndex) => [
            'class' => 'px-4 py-2 text-sm text-gray-800',
        ]);

        $this->setTableWrapperAttributes([
            'class' => 'overflow-x-auto shadow-md rounded-lg border border-gray-200',
        ]);

        $this->setEmptyMessage('😢 Tidak ada data ditemukan');
    }


    public function columns(): array
    {
        return [
            Column::make('User ID', 'user_id')
                ->sortable(),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),
            Column::make('Email', 'email')
                ->sortable(),
            Column::make('Created At', 'created_at')
                ->sortable(),
            Column::make('Action')
                ->label(function ($row) {
                    return view('livewire.users.includes.actions', ['user' => $row]);
                })
                ->html(),
        ];
    }

    public function query(): Builder
    {
        return User::query();
    }
}
