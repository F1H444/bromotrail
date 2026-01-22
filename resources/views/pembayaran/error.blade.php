@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-zinc-50 pt-32 pb-12 flex items-center justify-center">
        <div class="container mx-auto px-6 max-w-lg">
            <div class="bg-white p-10 rounded-sm border border-zinc-200 shadow-2xl text-center relative overflow-hidden">
                <!-- Error Indicator -->
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8 relative z-10">
                    <i class="fa-solid fa-times text-3xl text-red-600"></i>
                </div>

                <div class="relative z-10">
                    <h1 class="text-3xl font-black text-zinc-900 tracking-tighter mb-4 uppercase italic">Pembayaran Gagal
                    </h1>
                    <p class="text-zinc-500 leading-relaxed mb-8">
                        Maaf, terjadi kesalahan saat memproses pembayaran Anda. Silakan coba lagi atau hubungi customer
                        service kami.
                    </p>

                    <!-- Info Card -->
                    <div class="bg-zinc-50 border border-zinc-100 rounded-sm p-6 mb-10 text-left">
                        <div class="flex justify-between items-center mb-4 pb-4 border-b border-zinc-200">
                            <span class="text-[10px] font-black text-zinc-400 uppercase tracking-widest">ID BOOKING</span>
                            <span class="font-bold text-zinc-900">#{{ $penyewaan->id }}</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Motor</span>
                                <span class="font-bold text-zinc-900">{{ $penyewaan->motor->merk_tipe }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Total Biaya</span>
                                <span class="font-bold text-zinc-900">Rp
                                    {{ number_format($penyewaan->total_biaya, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-zinc-500">Status</span>
                                <span
                                    class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-sm uppercase tracking-tighter">
                                    Gagal
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Info Alert -->
                    <div class="bg-red-50 border border-red-100 rounded-sm p-4 mb-6 text-left">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info text-red-600 mt-0.5"></i>
                            <div class="text-sm text-red-800">
                                <p class="font-bold mb-1">Kemungkinan Penyebab:</p>
                                <ul class="list-disc list-inside space-y-1 text-red-700">
                                    <li>Saldo tidak mencukupi</li>
                                    <li>Transaksi ditolak oleh bank</li>
                                    <li>Koneksi terputus</li>
                                    <li>Melewati batas waktu pembayaran</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-4">
                        <a href="{{ route('pembayaran.create', $penyewaan->id) }}"
                            class="block w-full py-4 bg-zinc-900 text-white font-black rounded-sm hover:bg-zinc-800 transition-all shadow-xl hover:shadow-zinc-900/40 tracking-[0.2em] uppercase text-center flex items-center justify-center gap-3">
                            <i class="fa-solid fa-rotate-right text-xs"></i>
                            <span>Coba Lagi</span>
                        </a>

                        <a href="{{ route('dashboard.bookings') }}"
                            class="block w-full py-4 bg-white text-zinc-900 border-2 border-zinc-200 font-black rounded-sm hover:bg-zinc-50 transition-all tracking-[0.2em] uppercase text-center">
                            Lihat Booking Saya
                        </a>

                        <p class="text-[10px] text-zinc-400 uppercase tracking-widest font-medium">
                            Butuh bantuan? <a href="https://wa.me/628123456789"
                                class="text-zinc-900 font-bold underline decoration-zinc-200 hover:decoration-zinc-900 transition-all">Hubungi
                                WhatsApp Kami</a>
                        </p>
                    </div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-red-50/50 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-zinc-100 rounded-full blur-3xl"></div>
            </div>
        </div>
    </div>
@endsection
