<?php

namespace App\Livewire\Client;

use App\Models\CreditTransaction;
use App\Services\PaymentGatewayService;
use App\Services\TopUpService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Credits extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // Top Up State
    public bool $showTopUpModal = false;

    public float $topUpAmount = 10.00;

    public string $selectedGateway = '';

    public string $currency = 'USD';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function openTopUpModal()
    {
        $this->resetValidation();
        $this->showTopUpModal = true;
    }

    public function initiateTopUp(TopUpService $topUpService, PaymentGatewayService $gatewayService)
    {
        $this->validate([
            'topUpAmount' => 'required|numeric|min:5|max:1000',
            'selectedGateway' => 'required|string',
        ]);

        try {
            $invoice = $topUpService->createInvoice(
                Auth::user(),
                $this->topUpAmount,
                $this->selectedGateway,
                $this->currency
            );

            $redirectUrl = $gatewayService->createPaymentSession($invoice);

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            $this->addError('topUpAmount', $e->getMessage());
        }
    }

    public function render(PaymentGatewayService $gatewayService)
    {
        $user = Auth::user();

        $transactions = CreditTransaction::where('user_id', $user->id)
            ->when($this->search, function ($q) {
                $q->where('reason', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.client.credits', [
            'user' => $user,
            'transactions' => $transactions,
            'gateways' => $gatewayService->getEnabledGateways(),
            'recentActivity' => CreditTransaction::where('user_id', $user->id)->latest()->take(3)->get(),
        ]);
    }
}
