@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-zinc-50 pt-32 pb-12">
        <div class="container mx-auto px-6 max-w-6xl">
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                <div class="w-full lg:w-1/2 space-y-4">
                    <div>
                        <h1 class="text-3xl font-bold text-zinc-900 tracking-tighter mb-2">Finalisasi Pembayaran</h1>
                        <p class="text-xs text-zinc-500 leading-relaxed">
                            Selesaikan pembayaran untuk mengamankan jadwal sewa. Verifikasi otomatis via Midtrans.
                        </p>
                    </div>

                    <div class="bg-white border border-zinc-200 rounded-sm shadow-lg p-6 overflow-hidden relative">
                        <div class="absolute top-0 right-0 p-3">
                            <span
                                class="px-2 py-0.5 bg-zinc-100 text-zinc-500 text-[9px] font-bold uppercase tracking-widest rounded-full">#{{ $penyewaan->id }}</span>
                        </div>

                        <div class="flex gap-4 mb-4">
                            <div class="w-24 h-18 bg-zinc-100 rounded-sm overflow-hidden flex-shrink-0">
                                <img src="{{ $penyewaan->motor->gambar_url }}" class="w-full h-full object-cover"
                                    alt="Motor">
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-zinc-900 mb-0.5">{{ $penyewaan->motor->merk_tipe }}</h3>
                                <p class="text-xs text-zinc-500 mb-1">
                                    {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->format('d M') }} -
                                    {{ \Carbon\Carbon::parse($penyewaan->tgl_kembali)->format('d M Y') }}
                                </p>
                                <span
                                    class="px-2 py-0.5 bg-green-50 text-green-600 text-[9px] font-bold rounded-sm uppercase tracking-tighter">
                                    {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->diffInDays(\Carbon\Carbon::parse($penyewaan->tgl_kembali)) + 1 }}
                                    Hari
                                </span>
                            </div>
                        </div>

                        <div class="space-y-3 pt-6 border-t border-zinc-100">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Harga Sewa Motor</span>
                                <span class="font-bold text-zinc-900">IDR
                                    {{ number_format($penyewaan->motor->harga_sewa_per_hari, 0, ',', '.') }} x
                                    {{ \Carbon\Carbon::parse($penyewaan->tgl_mulai)->diffInDays(\Carbon\Carbon::parse($penyewaan->tgl_kembali)) + 1 }}</span>
                            </div>
                            @if ($penyewaan->details->isNotEmpty())
                                @foreach ($penyewaan->details as $detail)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-500">{{ $detail->tambahan->nama_item }}
                                            ({{ $detail->jumlah }}x)
                                        </span>
                                        <span class="font-bold text-zinc-900">IDR
                                            {{ number_format($detail->subtotal_harga, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            @endif
                            <div class="flex justify-between pt-4 border-t border-zinc-900 mt-4">
                                <span class="text-lg font-bold text-zinc-900">Total Tagihan</span>
                                <span class="text-2xl font-black text-zinc-900">IDR
                                    {{ number_format($penyewaan->total_biaya, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 bg-white border border-zinc-200 rounded-sm">
                            <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-2">Payment Gateway
                            </p>
                            <p class="text-[10px] text-zinc-400 mt-0.5 uppercase">Mendukung Transfer Bank Otomatis</p>
                        </div>
                        <div class="p-4 bg-white border border-zinc-200 rounded-sm">
                            <p class="text-[9px] font-black text-orange-600 uppercase tracking-widest mb-2">E-Wallet</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5 uppercase">Dana / Gopay / ShopeePay / QRIS</p>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/2">
                    <div class="bg-white p-8 rounded-sm border border-zinc-200 shadow-xl">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-zinc-900 mb-1">Pembayaran Online</h2>
                            <p class="text-[11px] text-zinc-400 font-medium italic border-l-2 border-zinc-200 pl-3">
                                Klik tombol bayar untuk memilih metode pembayaran via Midtrans.
                            </p>
                        </div>

                        <form id="submit-form" action="{{ route('pembayaran.store', $penyewaan->id) }}" method="POST"
                            style="display: none;">
                            @csrf
                            <input type="hidden" name="id_penyewaan" value="{{ $penyewaan->id }}">
                            <input type="hidden" name="payment_result" id="payment-result">
                        </form>

                        <div class="space-y-6">
                            <div class="flex flex-col gap-3">
                                <label class="text-xs font-black text-zinc-400 uppercase tracking-widest">Total Yang Harus
                                    Dibayar</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-zinc-400">IDR</span>
                                    <input type="text"
                                        value="{{ number_format($penyewaan->total_biaya, 0, ',', '.') }}" readonly
                                        class="w-full border-2 border-zinc-100 bg-zinc-50 rounded-sm p-4 pl-14 focus:outline-none transition-all font-black text-2xl text-zinc-900 cursor-not-allowed">
                                </div>
                            </div>

                            <div>
                                <button type="button" id="pay-button"
                                    class="w-full py-4 bg-zinc-900 text-white font-black rounded-sm hover:bg-zinc-800 transition-all shadow-2xl hover:shadow-zinc-900/40 tracking-[0.2em] uppercase flex justify-center items-center gap-3 mt-4">
                                    <span>Bayar Sekarang</span>
                                    <i class="fa-solid fa-credit-card text-xs"></i>
                                </button>
                                <p
                                    class="text-center text-[9px] text-zinc-400 uppercase tracking-[0.15em] font-medium leading-loose mt-2">
                                    Transaksi aman & terverifikasi otomatis oleh Midtrans.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>

        <script type="text/javascript">
            const payButton = document.getElementById('pay-button');

            payButton.onclick = function() {
                // Ubah tombol jadi loading state
                payButton.disabled = true;
                payButton.innerHTML = `<i class="fa-solid fa-circle-notch animate-spin mr-3"></i> Memproses...`;

                // Panggil Snap Midtrans
                // Pastikan Controller mengirim variabel $snapToken
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        // Masukkan hasil JSON ke hidden input
                        document.getElementById('payment-result').value = JSON.stringify(result);
                        // Submit form hidden
                        document.getElementById('submit-form').submit();
                    },
                    onPending: function(result) {
                        alert("Menunggu pembayaran!");
                        console.log(result);
                        payButton.disabled = false;
                        payButton.innerHTML = `<span>Bayar Sekarang</span><i class="fa-solid fa-credit-card text-xs"></i>`;
                    },
                    onError: function(result) {
                        alert("Pembayaran gagal!");
                        console.log(result);
                        payButton.disabled = false;
                        payButton.innerHTML = `<span>Bayar Sekarang</span><i class="fa-solid fa-credit-card text-xs"></i>`;
                    },
                    onClose: function() {
                        alert('Anda menutup popup pembayaran tanpa menyelesaikan transaksi');
                        payButton.disabled = false;
                        payButton.innerHTML = `<span>Bayar Sekarang</span><i class="fa-solid fa-credit-card text-xs"></i>`;
                    }
                });
            };
        </script>
    @endpush
@endsection