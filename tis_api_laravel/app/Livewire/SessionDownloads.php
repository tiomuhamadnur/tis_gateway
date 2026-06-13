<?php

namespace App\Livewire;

use App\Models\Session;
use Livewire\Component;
use Livewire\WithPagination;

class SessionDownloads extends Component
{
    use WithPagination;

    public string $search = '';
    public string $from = '';
    public string $to = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFrom(): void
    {
        $this->resetPage();
    }

    public function updatingTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->from = '';
        $this->to = '';
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
            ->when($this->from, fn ($query) => $query->whereDate('read_time', '>=', $this->from))
            ->when($this->to, fn ($query) => $query->whereDate('read_time', '<=', $this->to))
            ->orderByDesc('read_time')
            ->paginate(10);

        return view('cms.session-downloads', compact('sessions'));
    }
}
