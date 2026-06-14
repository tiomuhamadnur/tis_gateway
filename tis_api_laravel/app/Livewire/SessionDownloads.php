<?php

namespace App\Livewire;

use App\Models\Session;
use Livewire\Component;
use Livewire\WithPagination;

class SessionDownloads extends Component
{
    use WithPagination;

    public string $search = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function applyFilter(string $from, string $to): void
    {
        $this->dateFrom = $from;
        $this->dateTo = $to;
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function render()
    {
        $sessions = Session::query()
            ->withCount('uploadedFiles')
            ->with(['uploadedFiles:id,session_id,original_filename,path'])
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('session_id', 'like', $search)
                        ->orWhere('rake_id', 'like', $search)
                        ->orWhereHas('uploadedFiles', function ($fileQuery) use ($search) {
                            $fileQuery->where('original_filename', 'like', $search);
                        });
                });
            })
            ->when($this->dateFrom, fn ($query) => $query->whereDate('read_time', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('read_time', '<=', $this->dateTo))
            ->orderByDesc('read_time')
            ->paginate(10);

        return view('cms.session-downloads', compact('sessions'));
    }
}
