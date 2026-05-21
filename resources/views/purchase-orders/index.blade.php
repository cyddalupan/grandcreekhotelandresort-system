<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Purchase Orders</h2>
            <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition text-sm font-medium">+ New PO</a>
        </div>
    </x-slot>

    <div class="py-6">
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-gray-400">
                <p class="text-sm text-gray-500">Draft</p>
                <p class="text-2xl font-bold text-gray-800">{{ $draftCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-amber-400">
                <p class="text-sm text-gray-500">Pending (Approved/Sent/Partial)</p>
                <p class="text-2xl font-bold text-amber-600">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-400">
                <p class="text-sm text-gray-500">Received</p>
                <p class="text-2xl font-bold text-emerald-600">{{ $receivedCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-400">
                <p class="text-sm text-gray-500">Total POs</p>
                <p class="text-2xl font-bold text-blue-600">{{ $orders->total() }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="draft" @selected(request('status')=='draft')>Draft</option>
                        <option value="approved" @selected(request('status')=='approved')>Approved</option>
                        <option value="sent" @selected(request('status')=='sent')>Sent</option>
                        <option value="partially_received" @selected(request('status')=='partially_received')>Partially Received</option>
                        <option value="received" @selected(request('status')=='received')>Received</option>
                        <option value="cancelled" @selected(request('status')=='cancelled')>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" placeholder="PO# or supplier..." value="{{ request('search') }}" class="w-full rounded border-gray-300 text-sm">
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Filter</button>
                    <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">PO #</th>
                        <th class="text-left px-4 py-3 font-medium">Supplier</th>
                        <th class="text-right px-4 py-3 font-medium">Total</th>
                        <th class="text-center px-4 py-3 font-medium">Items</th>
                        <th class="text-center px-4 py-3 font-medium">Status</th>
                        <th class="text-center px-4 py-3 font-medium">Created</th>
                        <th class="text-center px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-blue-600">{{ $po->po_number }}</td>
                        <td class="px-4 py-3">{{ $po->supplier->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-right">₱{{ number_format($po->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-center">{{ count($po->items) }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-200 text-gray-700',
                                    'approved' => 'bg-blue-100 text-blue-700',
                                    'sent' => 'bg-amber-100 text-amber-700',
                                    'partially_received' => 'bg-purple-100 text-purple-700',
                                    'received' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$po->status] ?? 'bg-gray-100' }}">
                                {{ str_replace('_', ' ', ucwords($po->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $po->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
