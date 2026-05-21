<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit PO: {{ $purchaseOrder->po_number }}</h2>
    </x-slot>

    <div class="py-6">
        <form id="poForm" method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}" class="max-w-4xl mx-auto space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Supplier Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select name="supplier_id" id="supplier_id" required class="w-full rounded border-gray-300 text-sm">
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" @selected($purchaseOrder->supplier_id == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PO Number</label>
                        <input type="text" value="{{ $purchaseOrder->po_number }}" disabled class="w-full rounded border-gray-200 text-sm bg-gray-50">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-800">Line Items</h3>
                    <button type="button" id="addItemBtn" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">+ Add Item</button>
                </div>
                <div id="itemsContainer"></div>
                <input type="hidden" name="items" id="itemsInput" value="">
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-lg font-medium text-gray-800">Total:</span>
                    <span id="totalDisplay" class="text-2xl font-bold text-emerald-600">₱0.00</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 text-sm">{{ old('notes', $purchaseOrder->notes) }}</textarea>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Update PO</button>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
let itemCount = 0;
const existingItems = @json($purchaseOrder->items);

function addItemRow(item = null) {
    const container = document.getElementById('itemsContainer');
    const idx = itemCount++;
    const div = document.createElement('div');
    div.className = 'item-row grid grid-cols-12 gap-3 items-end p-3 bg-gray-50 rounded-lg mb-2';
    div.dataset.index = idx;

    const name = item?.name || '';
    const qty = item?.qty || 1;
    const unit = item?.unit || 'pcs';
    const price = item?.unit_price || 0;

    div.innerHTML = `
        <div class="col-span-4">
            <label class="block text-xs text-gray-600 mb-1">Item Name</label>
            <input type="text" class="item-name w-full rounded border-gray-300 text-sm" value="${name}" placeholder="e.g. Toilet Paper" required>
        </div>
        <div class="col-span-1">
            <label class="block text-xs text-gray-600 mb-1">Qty</label>
            <input type="number" class="item-qty w-full rounded border-gray-300 text-sm" min="1" value="${qty}" required>
        </div>
        <div class="col-span-1">
            <label class="block text-xs text-gray-600 mb-1">Unit</label>
            <input type="text" class="item-unit w-full rounded border-gray-300 text-sm" value="${unit}">
        </div>
        <div class="col-span-2">
            <label class="block text-xs text-gray-600 mb-1">Unit Price</label>
            <input type="number" class="item-price w-full rounded border-gray-300 text-sm" min="0" step="0.01" value="${price}" required>
        </div>
        <div class="col-span-3">
            <label class="block text-xs text-gray-600 mb-1">Subtotal</label>
            <input type="text" class="item-subtotal w-full rounded border-gray-200 bg-gray-100 text-sm font-medium" readonly>
        </div>
        <div class="col-span-1">
            <button type="button" class="remove-item mt-5 px-2 py-1.5 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm" onclick="this.closest('.item-row').remove(); updateTotal();">✕</button>
        </div>
    `;

    container.appendChild(div);
    div.querySelectorAll('.item-qty, .item-price').forEach(el => el.addEventListener('input', updateTotal));
    updateTotal();
}

if (existingItems && existingItems.length > 0) {
    existingItems.forEach(item => addItemRow(item));
} else {
    addItemRow();
}

document.getElementById('addItemBtn').addEventListener('click', () => addItemRow());

function updateTotal() {
    const rows = document.querySelectorAll('.item-row');
    let items = [];
    let total = 0;

    rows.forEach(row => {
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

document.getElementById('poForm').addEventListener('submit', function() { updateTotal(); });
</script>
