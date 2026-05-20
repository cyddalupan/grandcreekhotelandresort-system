@extends('layouts.app')

@section('page-header', 'Point of Sale')
@section('title', 'POS - ' . config('app.name'))

@section('content')
<div x-data="posRegister()" x-init="init()" class="h-[calc(100vh-8rem)] flex flex-col">
    {{-- Top Bar --}}
    <div class="bg-white rounded-xl shadow-sm px-4 py-3 mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-blue-900">Point of Sale</h1>
            <p class="text-xs text-gray-500"><span x-text="currentTime"></span></p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.history') }}" class="text-sm text-gray-600 hover:text-blue-900">Sales History</a>
            <span class="text-sm bg-blue-50 text-blue-700 px-2 py-1 rounded">Cart: <span x-text="cart.length"></span> items</span>
        </div>
    </div>

    <div class="flex-1 flex gap-4 min-h-0">
        {{-- LEFT — Product Grid --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Search + Categories --}}
            <div class="bg-white rounded-xl shadow-sm p-3 mb-3">
                <div class="flex gap-2 mb-2">
                    <input type="text" x-model="search" @input.debounce="filterItems" placeholder="Search items..."
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <select x-model="categoryFilter" @change="filterItems"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Quick category pills --}}
                <div class="flex flex-wrap gap-1.5">
                    <button @click="categoryFilter = ''; filterItems()"
                            class="px-2.5 py-1 text-xs rounded-full"
                            :class="categoryFilter === '' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        All
                    </button>
                    @foreach($categories as $cat)
                    <button @click="categoryFilter = '{{ $cat }}'; filterItems()"
                            class="px-2.5 py-1 text-xs rounded-full"
                            :class="categoryFilter === '{{ $cat }}' ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                        {{ $cat }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Items Grid --}}
            <div class="flex-1 overflow-y-auto bg-white rounded-xl shadow-sm p-3">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                    <template x-for="item in filteredItems" :key="item.id">
                        <button @click="addToCart(item)"
                                class="p-3 border-2 rounded-xl text-left hover:border-blue-400 transition-all"
                                :class="item.stock <= 0 ? 'border-red-200 bg-red-50 opacity-50 cursor-not-allowed' : 'border-gray-100 hover:shadow-md'"
                                :disabled="item.stock <= 0">
                            <p class="text-sm font-bold text-gray-800 truncate" x-text="item.name"></p>
                            <p class="text-lg font-bold text-green-700" x-text="'₱' + numberFormat(item.price)"></p>
                            <p class="text-xs text-gray-400" x-text="item.stock + ' ' + (item.unit || 'pcs') + ' · ' + (item.category || '—')"></p>
                        </button>
                    </template>
                    <div x-show="filteredItems.length === 0"
                         class="col-span-full py-12 text-center text-gray-500 text-sm">
                        No items found.
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT — Cart --}}
        <div class="w-80 lg:w-96 flex flex-col">
            <div class="bg-white rounded-xl shadow-sm p-4 flex-1 flex flex-col">
                <h2 class="text-sm font-bold text-gray-700 mb-2">Cart</h2>

                {{-- Cart Items (scrollable) --}}
                <div class="flex-1 overflow-y-auto space-y-2 mb-3">
                    <template x-for="(line, idx) in cart" :key="idx">
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="line.name"></p>
                                <p class="text-xs text-gray-500" x-text="'₱' + numberFormat(line.price) + ' × ' + line.qty"></p>
                            </div>
                            <p class="text-sm font-bold text-gray-800" x-text="'₱' + numberFormat(line.price * line.qty)"></p>
                            <div class="flex items-center gap-1">
                                <button @click="changeQty(idx, -1)" class="w-6 h-6 flex items-center justify-center rounded bg-gray-200 hover:bg-gray-300 text-xs font-bold">−</button>
                                <span class="w-6 text-center text-sm font-medium" x-text="line.qty"></span>
                                <button @click="changeQty(idx, 1)" class="w-6 h-6 flex items-center justify-center rounded bg-gray-200 hover:bg-gray-300 text-xs font-bold">+</button>
                            </div>
                            <button @click="removeItem(idx)" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </template>
                    <div x-show="cart.length === 0" class="py-8 text-center text-gray-400 text-sm">
                        Cart is empty. Click items to add.
                    </div>
                </div>

                {{-- Totals --}}
                <div class="border-t pt-3 space-y-1.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium" x-text="'₱' + numberFormat(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax (<span x-text="taxPercent"></span>%)</span>
                        <span class="font-medium" x-text="'₱' + numberFormat(taxAmount)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Discount</span>
                        <div class="flex items-center gap-1">
                            <span>₱</span>
                            <input type="number" x-model="discount" @input="calcTotals" min="0" step="0.25"
                                   class="w-20 px-1.5 py-0.5 border border-gray-300 rounded text-sm text-right">
                        </div>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t pt-1.5">
                        <span>Total</span>
                        <span class="text-blue-900" x-text="'₱' + numberFormat(total)"></span>
                    </div>
                </div>

                {{-- Checkout Button --}}
                <button @click="openCheckout()"
                        :disabled="cart.length === 0"
                        class="mt-4 w-full py-3 text-white font-bold rounded-xl text-base transition-all"
                        :class="cart.length === 0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-700 hover:bg-green-600'">
                    Checkout — ₱<span x-text="numberFormat(total)"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Checkout Modal --}}
    <div x-show="showCheckout" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showCheckout = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Complete Sale</h3>

            <div class="space-y-3 mb-4">
                {{-- Receipt items summary --}}
                <div class="max-h-32 overflow-y-auto bg-gray-50 rounded-lg p-3 text-sm">
                    <template x-for="(line, idx) in cart" :key="idx">
                        <div class="flex justify-between py-0.5">
                            <span x-text="line.qty + '× ' + line.name"></span>
                            <span class="font-medium" x-text="'₱' + numberFormat(line.price * line.qty)"></span>
                        </div>
                    </template>
                    <div class="border-t mt-1 pt-1 flex justify-between font-bold">
                        <span>Total</span>
                        <span x-text="'₱' + numberFormat(total)"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Method</label>
                        <select x-model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tendered Amount</label>
                        <input type="number" x-model="tendered" @input="calcChange" step="0.25" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-right">
                    </div>
                </div>

                <div class="flex justify-between text-sm bg-blue-50 rounded-lg p-3">
                    <span class="font-medium">Change:</span>
                    <span class="font-bold text-lg" :class="change >= 0 ? 'text-green-700' : 'text-red-600'" x-text="'₱' + numberFormat(change)"></span>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <input type="text" x-model="notes" placeholder="Optional..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex gap-3">
                <button @click="showCheckout = false" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="processSale()"
                        :disabled="!paymentMethod || tendered < total"
                        class="flex-1 px-4 py-2 rounded-lg text-sm font-bold text-white transition-all"
                        :class="!paymentMethod || tendered < total ? 'bg-gray-300 cursor-not-allowed' : 'bg-green-700 hover:bg-green-600'">
                    Complete Sale
                </button>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <div x-show="showSuccess" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showSuccess = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">Sale Complete!</h3>
            <p class="text-sm text-gray-500 mb-2" x-text="'Receipt #' + lastReceipt"></p>
            <p class="text-2xl font-bold text-green-700 mb-4" x-text="'₱' + numberFormat(lastTotal)"></p>
            <button @click="resetRegister()" class="px-6 py-2 bg-blue-900 hover:bg-blue-800 text-white rounded-lg text-sm font-medium">
                New Sale
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posRegister() {
    return {
        search: '',
        categoryFilter: '',
        allItems: @json($items->map(fn($i) => [
            'id'       => $i->id,
            'name'     => $i->name,
            'price'    => (float) $i->selling_price,
            'stock'    => $i->current_stock,
            'unit'     => $i->unit,
            'category' => $i->category,
        ])),
        filteredItems: [],
        cart: [],
        taxPercent: 12,
        discount: 0,
        subtotal: 0,
        taxAmount: 0,
        total: 0,

        showCheckout: false,
        paymentMethod: 'cash',
        tendered: 0,
        change: 0,
        notes: '',

        showSuccess: false,
        lastReceipt: '',
        lastTotal: 0,

        currentTime: '',

        init() {
            this.filterItems();
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
        },

        updateClock() {
            const now = new Date();
            const opts = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            this.currentTime = now.toLocaleTimeString('en-PH', opts);
        },

        filterItems() {
            let items = this.allItems;
            if (this.search) {
                const q = this.search.toLowerCase();
                items = items.filter(i => i.name.toLowerCase().includes(q));
            }
            if (this.categoryFilter) {
                items = items.filter(i => i.category === this.categoryFilter);
            }
            this.filteredItems = items;
        },

        addToCart(item) {
            if (item.stock <= 0) return;
            const existing = this.cart.find(i => i.id === item.id);
            if (existing) {
                if (existing.qty < item.stock) existing.qty++;
            } else {
                this.cart.push({ ...item, qty: 1 });
            }
            this.calcTotals();
        },

        changeQty(idx, delta) {
            const line = this.cart[idx];
            line.qty += delta;
            if (line.qty <= 0) {
                this.cart.splice(idx, 1);
            } else if (line.qty > line.stock) {
                line.qty = line.stock;
            }
            this.calcTotals();
        },

        removeItem(idx) {
            this.cart.splice(idx, 1);
            this.calcTotals();
        },

        calcTotals() {
            this.subtotal = this.cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            this.taxAmount = this.subtotal * (this.taxPercent / 100);
            const disc = parseFloat(this.discount) || 0;
            this.total = Math.max(0, this.subtotal + this.taxAmount - disc);
        },

        openCheckout() {
            if (this.cart.length === 0) return;
            this.paymentMethod = 'cash';
            this.tendered = 0;
            this.change = 0;
            this.notes = '';
            this.showCheckout = true;
        },

        calcChange() {
            this.change = (parseFloat(this.tendered) || 0) - this.total;
        },

        processSale() {
            if (!this.paymentMethod || this.tendered < this.total) return;

            const items = this.cart.map(i => ({
                item_id: i.id,
                name: i.name,
                price: i.price,
                quantity: i.qty,
                subtotal: i.price * i.qty,
            }));

            fetch('{{ route('pos.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    items: items,
                    subtotal: this.subtotal,
                    tax_percent: this.taxPercent,
                    tax_amount: this.taxAmount,
                    discount: this.discount,
                    total: this.total,
                    payment_method: this.paymentMethod,
                    tendered_amount: this.tendered,
                    change: this.change,
                    notes: this.notes,
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.showCheckout = false;
                    this.lastTotal = this.total;
                    this.showSuccess = true;
                }
            });
        },

        resetRegister() {
            this.cart = [];
            this.discount = 0;
            this.tendered = 0;
            this.change = 0;
            this.subtotal = 0;
            this.taxAmount = 0;
            this.total = 0;
            this.showSuccess = false;
            this.lastReceipt = '';
            this.lastTotal = 0;
            this.filterItems();
        },

        numberFormat(n) {
            return Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }
}
</script>
@endpush
