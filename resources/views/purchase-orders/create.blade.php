<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">New Purchase Order</h2>
    </x-slot>

    <div class="py-6">
        <form id="poForm" method="POST" action="{{ route('purchase-orders.store') }}" class="max-w-4xl mx-auto space-y-6">
            @csrf

            {{-- Supplier --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Supplier Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select name="supplier_id" id="supplier_id" required class="w-full rounded border-gray-300 text-sm">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id')==$supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PO Date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" disabled class="w-full rounded border-gray-200 text-sm bg-gray-50">
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Line Items</h3>
                    <button type="button" id="addItemBtn" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">+ Add Item</button>
                </div>
                <div id="itemsContainer">
                    <p class="text-gray-400 text-sm" id="noItemsMsg">Click "Add Item" to add line items.</p>
                </div>
                <input type="hidden" name="items" id="itemsInput" value="[]">
                @error('items') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Summary --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-lg font-medium text-gray-800">Total:</span>
                    <span id="totalDisplay" class="text-2xl font-bold text-emerald-600">₱0.00</span>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-lg shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 text-sm" placeholder="Optional notes...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('purchase-orders.index') }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Create PO</button>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
const suppliers = @json($suppliers);
let itemCount = 0;

document.getElementById('addItemBtn').addEventListener('click', function() {
    const container = document.getElementById('itemsContainer');
    const noMsg = document.getElementById('noItemsMsg');
    if (noMsg) noMsg.remove();

    const idx = itemCount++;
    const div = document.createElement('div');
    div.className = 'item-row grid grid-cols-12 gap-3 items-end p-3 bg-gray-50 rounded-lg mb-2';
    div.dataset.index = idx;

    div.innerHTML = `
        <div class="col-span-4">
            <label class="block text-xs text-gray-600 mb-1">Item Name</label>
            <input type="text" name="item_name_${idx}" class="item-name w-full rounded border-gray-300 text-sm" placeholder="e.g. Toilet Paper" required>
        </div>
        <div class="col-span-1">
            <label class="block text-xs text-gray-600 mb-1">Qty</label>
            <input type="number" name="item_qty_${idx}" class="item-qty w-full rounded border-gray-300 text-sm" min="1" value="1" required>
        </div>
        <div class="col-span-1">
            <label class="block text-xs text-gray-600 mb-1">Unit</label>
            <input type="text" name="item_unit_${idx}" class="item-unit w-full rounded border-gray-300 text-sm" placeholder="pcs" value="pcs">
        </div>
        <div class="col-span-2">
            <label class="block text-xs text-gray-600 mb-1">Unit Price</label>
            <input type="number" name="item_price_${idx}" class="item-price w-full rounded border-gray-300 text-sm" min="0" step="0.01" value="0" required>
        </div>
        <div class="col-span-3">
            <label class="block text-xs text-gray-600 mb-1">Subtotal</label>
            <input type="text" class="item-subtotal w-full rounded border-gray-200 bg-gray-100 text-sm font-medium" readonly value="₱0.00">
        </div>
        <div class="col-span-1">
            <button type="button" class="remove-item mt-5 px-2 py-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm" onclick="this.closest('.item-row').remove(); updateTotal();">✕</button>
        </div>
    `;

    container.appendChild(div);

    // Auto-update subtotal
    div.querySelectorAll('.item-qty, .item-price').forEach(el => {
        el.addEventListener('input', updateTotal);
    });
});

function updateTotal() {
    const rows = document.querySelectorAll('.item-row');
    let items = [];
    let total = 0;

    rows.forEach((row, i) => {
        const name = row.querySelector('.item-name')?.value || '';
        const qty = parseFloat(row.querySelector('.item-qty')?.value || 0);
        const unit = row.querySelector('.item-unit')?.value || 'pcs';
        const price = parseFloat(row.querySelector('.item-price')?.value || 0);
        const subtotal = qty * price;

        const subEl = row.querySelector('.item-subtotal');
        if (subEl) subEl.value = '₱' + subtotal.toFixed(2);

        if (name && qty > 0) {
            items.push({ name, qty, unit, unit_price: price, total: subtotal });
            total += subtotal;
        }
    });

    document.getElementById('totalDisplay').textContent = '₱' + total.toFixed(2);
    document.getElementById('itemsInput').value = JSON.stringify(items);
}

// Form submit: ensure items are serialized
document.getElementById('poForm').addEventListener('submit', function() {
    updateTotal();
});
</script>
