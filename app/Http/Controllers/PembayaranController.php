<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class PembayaranController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function create($id)
    {
        $penyewaan = Penyewaan::with(['pelanggan', 'motor', 'details.tambahan'])->findOrFail($id);

        // Cek jika sudah lunas
        $cekPembayaran = Pembayaran::where('id_penyewaan', $id)->first();
        if ($cekPembayaran && $cekPembayaran->status_pembayaran == 'Lunas') {
            return redirect()->route('pembayaran.success', $id);
        }

        // Buat Order ID unik
        $orderId = 'BT-' . $penyewaan->id . '-' . time();

        // Parameter Transaksi Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $penyewaan->total_biaya,
            ],
            'customer_details' => [
                'first_name' => $penyewaan->pelanggan->nama_lengkap,
                'email' => $penyewaan->pelanggan->email,
                'phone' => $penyewaan->pelanggan->no_wa,
            ],
            'item_details' => [
                [
                    'id' => 'MTR-' . $penyewaan->motor->id,
                    'price' => (int) $penyewaan->motor->harga_sewa_per_hari,
                    'quantity' => (int) $penyewaan->tgl_mulai->diffInDays($penyewaan->tgl_kembali) + 1, // Ditambah +1 agar hitungan hari tepat
                    'name' => 'Sewa ' . $penyewaan->motor->merk_tipe,
                ]
            ],
        ];

        // PERBAIKAN DI SINI: Loop menggunakan 'details' bukan 'detailSewa'
        foreach ($penyewaan->details as $detail) {
            $params['item_details'][] = [
                'id' => 'ADD-' . $detail->tambahan->id,
                'price' => (int) $detail->tambahan->harga_satuan,
                'quantity' => (int) $detail->jumlah,
                'name' => $detail->tambahan->nama_item,
            ];
        }

        // Add callback URLs for Midtrans
        $params['callbacks'] = [
            'finish' => route('midtrans.finish'),
            'unfinish' => route('midtrans.unfinish'),
            'error' => route('midtrans.error'),
        ];


        // Dapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungkan ke payment gateway: ' . $e->getMessage());
        }

        return view('pembayaran.create', compact('penyewaan', 'snapToken'));
    }

    public function store(Request $request, $id)
    {
        $json = json_decode($request->get('payment_result'), true);
        $penyewaan = Penyewaan::findOrFail($id);

        // Cek status transaksi dari response JSON Midtrans
        $status = $json['transaction_status'] ?? null;
        $fraud = $json['fraud_status'] ?? null;

        // Tentukan status pembayaran internal berdasarkan respon Midtrans
        $statusPembayaran = 'Pending';
        if ($status == 'capture' || $status == 'settlement') {
            $statusPembayaran = 'Lunas';
        } else if ($status == 'cancel' || $status == 'deny' || $status == 'expire') {
            $statusPembayaran = 'Gagal';
        }

        if ($statusPembayaran == 'Lunas') {
            Pembayaran::create([
                'id_penyewaan' => $penyewaan->id,
                'metode_bayar' => $json['payment_type'] ?? 'midtrans',
                'jumlah_bayar' => $json['gross_amount'] ?? $penyewaan->total_biaya,
                'status_pembayaran' => 'Lunas',
                'bukti_bayar' => 'midtrans_auto',
                // 'catatan' field dihapus sesuai struktur tabel user jika tidak ada
                'tgl_bayar' => now(),
            ]);

            $penyewaan->update(['status_sewa' => 'Aktif']);

            return redirect()->route('pembayaran.success', $id)->with('success', 'Pembayaran berhasil!');
        }

        return redirect()->back()->with('error', 'Pembayaran belum diselesaikan atau gagal.');
    }

    public function success($id)
    {
        $penyewaan = Penyewaan::findOrFail($id);
        return view('pembayaran.success', compact('penyewaan'));
    }
}
