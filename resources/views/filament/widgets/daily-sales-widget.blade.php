<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 Penjualan Per Hari (7 Hari Terakhir)
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                        <th class="text-center py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Pesanan</th>
                        <th class="text-right py-2 px-3 font-semibold text-gray-600 dark:text-gray-300">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->getDailySales() as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800 {{ $row['isToday'] ? 'bg-emerald-50 dark:bg-emerald-900/20' : '' }}">
                            <td class="py-2 px-3 {{ $row['isToday'] ? 'font-bold text-emerald-700 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $row['hari'] }}
                                @if($row['isToday'])
                                    <span class="ml-1 text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-800 dark:text-emerald-300 rounded-full px-2 py-0.5">Hari ini</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $row['jumlah'] > 0 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-800' }}">
                                    {{ $row['jumlah'] }}x
                                </span>
                            </td>
                            <td class="py-2 px-3 text-right font-semibold {{ $row['pendapatan'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                        <td class="py-2 px-3 font-bold text-gray-700 dark:text-gray-200">Total 7 Hari</td>
                        <td class="py-2 px-3 text-center font-bold text-blue-600 dark:text-blue-400">
                            {{ collect($this->getDailySales())->sum('jumlah') }}x
                        </td>
                        <td class="py-2 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format(collect($this->getDailySales())->sum('pendapatan'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
