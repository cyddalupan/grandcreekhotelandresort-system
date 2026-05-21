<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">PO: {{ $purchaseOrder->po_number }}</h2>
                <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
            </div>
            <div class="flex gap-2">
                @if($purchaseOrder->canCancel())
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
                @endif
                <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        @if(session('success')) <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('error') }}</div> @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- PO Details --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Order Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500">PO Number:</span> <span class="font-medium">{{ $purchaseOrder->po_number }}</span></div>
                        <div><span class="text-gray-500">Status:</span>
                            @php
                                $colors = ['draft'=>'bg-gray-200 text-gray-700','approved'=>'bg-blue-100 text-blue-700','sent'=>'bg-amber-100 text-amber-700','partially_received'=>'bg-purple-100 text-purple-700','received'=>'bg-emerald-100 text-emerald-700','cancelled'=>'bg-red-100 text-red-700'];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $colors[$purchaseOrder->status] ?? 'bg-gray-100' }}">{{ str_replace('_',' ',ucwords($purchaseOrder->status)) }}</span>
                        </div>
                        <div><span class="text-gray-500">Created By:</span> {{ $purchaseOrder->creator->name ?? 'N/A' }}</div>
                        <div><span class="text-gray-500">Created:</span> {{ $purchaseOrder->created_at->format('M d, Y h:i A') }}</div>
                        @if($purchaseOrder->approved_by)
                        <div><span class="text-gray-500">Approved By:</span> {{ $purchaseOrder->approver->name ?? 'N/A' }}</div>
                        <div><span class="text-gray-500">Approved:</span> {{ $purchaseOrder->approved_at?->format('M d, Y h:i A') }}</div>
                        @endif
                        @if($purchaseOrder->received_at)
                        <div><span class="text-gray-500">Received:</span> {{ $purchaseOrder->received_at->format('M d, Y h:i A') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Line Items</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="text-left px-3 py-2">Item</th>
                                <th class="text-center px-3 py-2">Qty Ordered</th>
                                <th class="text-center px-3 py-2">Unit</th>
                                <th class="text-right px-3 py-2">Unit Price</th>
                                <th class="text-right px-3 py-2">Total</th>
                                @if(in_array($purchaseOrder->status, ['sent','partially_received']))
                                <th class="text-center px-3 py-2">Qty Received</th>
                                @endif
                                @if($purchaseOrder->status === 'received')
                                <th class="text-center px-3 py-2">Received</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $item)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $item['name'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $item['qty'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $item['unit'] ?? 'pcs' }}</td>
                                <td class="px-3 py-2 text-right">₱{{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-3 py-2 text-right">₱{{ number_format($item['total'] ?? ($item['qty'] * $item['unit_price']), 2) }}</td>
                                @if(in_array($purchaseOrder->status, ['sent','partially_received']))
                                <td class="px-3 py-2 text-center text-emerald-600 font-medium">{{ $item['qty_received'] ?? 0 }}</td>
                                @endif
                                @if($purchaseOrder->status === 'received')
                                <td class="px-3 py-2 text-center text-emerald-600 font-medium">{{ $item['qty_received'] ?? $item['qty'] }}</td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 font-medium">
                                <td colspan="4" class="px-3 py-2 text-right">Total:</td>
                                <td class="px-3 py-2 text-right text-emerald-600">₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($purchaseOrder->notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Notes</h3>
                    <p class="text-sm text-gray-600">{{ $purchaseOrder->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Sidebar -- Actions --}}
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Actions</h3>
                    <div class="space-y-3">
                        @if($purchaseOrder->canApprove())
                        <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">Approve PO</button>
                        </form>
                        @endif
                        @if($purchaseOrder->canSend())
                        <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-amber-500 text-white rounded-lg text-sm hover:bg-amber-600 font-medium">Mark as Sent</button>
                        </form>
                        @endif
                        @if($purchaseOrder->canReceive())
                        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" id="receiveForm">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Receive All Items</label>
                                <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 font-medium">Receive All</button>
                            </div>
                            @if(count($purchaseOrder->items) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Receive Selected Quantities</label>
                                @foreach($purchaseOrder->items as $idx => $item)
                                <div class="flex items-center gap-2 mb-2 text-xs">
                                    <span class="flex-1 truncate">{{ $item['name'] }}</span>
                                    <input type="number" name="qty_{{ $idx }}" class="w-16 rounded border-gray-300 text-xs text-center" min="0" max="{{ $item['qty'] - ($item['qty_received'] ?? 0) }}" value="{{ $item['qty'] - ($item['qty_received'] ?? 0) }}" data-idx="{{ $idx }}">
                                </div>
                                @endforeach
                                <button type="button" onclick="receiveSelected()" class="w-full px-4 py-2 bg-emerald-100 text-emerald-700 rounded text-sm hover:bg-emerald-200">Receive Selected</button>
                            </div>
                            @endif
                            <input type="hidden" name="received_items" id="receivedItems" value="">
                        </form>
                        <script>
                        function receiveSelected() {
                            const items = {};
                            document.querySelectorAll('[data-idx]').forEach(inp => {
                                items[inp.dataset.idx] = parseFloat(inp.value) || 0;
                            });
                            document.getElementById('receivedItems').value = JSON.stringify(items);
                            document.getElementById('receiveForm').submit();
                        }
                        </script>
                        @endif
                        @if($purchaseOrder->canCancel())
                        <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}" onsubmit="return confirm('Cancel this PO?')">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200 font-medium">Cancel PO</button>
                        </form>
                        @endif
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Status Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($purchaseOrder->status, ['draft','approved','sent','partially_received','received']) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($purchaseOrder->status, ['draft','approved','sent','partially_received','received']) ? 'text-gray-800' : 'text-gray-400' }}">Draft</span>
                            @if($purchaseOrder->created_at)
                            <span class="text-xs text-gray-400">{{ $purchaseOrder->created_at->format('M d') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($purchaseOrder->status, ['approved','sent','partially_received','received']) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($purchaseOrder->status, ['approved','sent','partially_received','received']) ? 'text-gray-800' : 'text-gray-400' }}">Approved</span>
                            @if($purchaseOrder->approved_at)
                            <span class="text-xs text-gray-400">{{ $purchaseOrder->approved_at->format('M d') }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($purchaseOrder->status, ['sent','partially_received','received']) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($purchaseOrder->status, ['sent','partially_received','received']) ? 'text-gray-800' : 'text-gray-400' }}">Sent</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-3 h-3 rounded-full {{ in_array($purchaseOrder->status, ['received','partially_received']) ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                            <span class="{{ in_array($purchaseOrder->status, ['received','partially_received']) ? 'text-gray-800' : 'text-gray-400' }}">{{ $purchaseOrder->status === 'partially_received' ? 'Partially Received' : 'Received' }}</span>
                            @if($purchaseOrder->received_at)
                            <span class="text-xs text-gray-400">{{ $purchaseOrder->received_at->format('M d') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
