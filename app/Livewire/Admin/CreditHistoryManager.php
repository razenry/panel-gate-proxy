<?php

namespace App\Livewire\Admin;

use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\CreditService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CreditHistoryManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterCurrency = '';

    public ?int $filterUser = null;

    public ?int $filterActor = null;

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showReverseModal = false;

    public ?int $reversingTransactionId = null;

    public string $reversalReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterCurrency' => ['except' => ''],
        'filterUser' => ['except' => null],
        'filterActor' => ['except' => null],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterCurrency()
    {
        $this->resetPage();
    }

    public function updatingFilterUser()
    {
        $this->resetPage();
    }

    public function updatingFilterActor()
    {
        $this->resetPage();
    }

    public function confirmReverse(int $id)
    {
        $this->reversingTransactionId = $id;
        $this->reversalReason = '';
        $this->showReverseModal = true;
    }

    public function reverseTransaction(CreditService $creditService)
    {
        $transaction = CreditTransaction::findOrFail($this->reversingTransactionId);

        try {
            $creditService->reverseTransaction(
                $transaction,
                Auth::id(),
                $this->reversalReason ?: null
            );

            Flux::toast(text: 'Transaction reversed successfully.', variant: 'success');
            $this->showReverseModal = false;
        } catch (\Exception $e) {
            $this->addError('reversal', $e->getMessage());
        }
    }

    public function render()
    {
        $query = CreditTransaction::with(['user', 'actor'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('reason', 'like', '%'.$this->search.'%')
                        ->orWhere('id', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterCurrency, fn ($q) => $q->where('currency_code', $this->filterCurrency))
            ->when($this->filterUser, fn ($q) => $q->where('user_id', $this->filterUser))
            ->when($this->filterActor, fn ($q) => $q->where('actor_id', $this->filterActor))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc');

        $transactions = $query->paginate(15);
        $users = User::orderBy('name')->get();
        $actors = User::where('is_admin', true)->orderBy('name')->get();

        return view('livewire.admin.credit-history-manager', [
            'transactions' => $transactions,
            'users' => $users,
            'actors' => $actors,
        ]);
    }
}
