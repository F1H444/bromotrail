<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Handle Midtrans payment notification webhook
     */
    public function notification(Request $request)
    {
        try {
            $notification = new Notification();

            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $orderId = $notification->order_id;

            // Extract ID Penyewaan dari Order ID (format: BT-{id}-{timestamp})
            $parts = explode('-', $orderId);
            $idPenyewaan = $parts[1] ?? null;

            if (!$idPenyewaan) {
                return response()->json(['status' => 'error', 'message' => 'Invalid order ID'], 400);
            }

            $penyewaan = Penyewaan::find($idPenyewaan);
            if (!$penyewaan) {
                return response()->json(['status' => 'error', 'message' => 'Penyewaan not found'], 404);
            }

            // Cek apakah pembayaran sudah ada
            $pembayaran = Pembayaran::where('id_penyewaan', $idPenyewaan)->first();

            // Determine payment status
            $statusPembayaran = 'Pending';
            
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $statusPembayaran = 'Lunas';
                }
            } else if ($transactionStatus == 'settlement') {
                $statusPembayaran = 'Lunas';
            } else if ($transactionStatus == 'pending') {
                $statusPembayaran = 'Pending';
            } else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                $statusPembayaran = 'Gagal';
            }

            // Create or update pembayaran
            if ($statusPembayaran == 'Lunas') {
                if (!$pembayaran) {
                    Pembayaran::create([
                        'id_penyewaan' => $penyewaan->id,
                        'metode_bayar' => $notification->payment_type ?? 'midtrans',
                        'jumlah_bayar' => $notification->gross_amount ?? $penyewaan->total_biaya,
                        'status_pembayaran' => 'Lunas',
                        'bukti_bayar' => 'midtrans_auto_' . $orderId,
                        'tgl_bayar' => now(),
                    ]);
                } else {
                    $pembayaran->update([
                        'status_pembayaran' => 'Lunas',
                        'metode_bayar' => $notification->payment_type ?? $pembayaran->metode_bayar,
                        'tgl_bayar' => now(),
                    ]);
                }

                // Update status penyewaan
                $penyewaan->update(['status_sewa' => 'Aktif']);
            } else if ($statusPembayaran == 'Gagal') {
                if ($pembayaran) {
                    $pembayaran->update(['status_pembayaran' => 'Gagal']);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle finish redirect (success payment)
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        // Extract ID Penyewaan dari Order ID
        $parts = explode('-', $orderId);
        $idPenyewaan = $parts[1] ?? null;

        if (!$idPenyewaan) {
            return redirect()->route('dashboard')->with('error', 'ID Pembayaran tidak valid');
        }

        $penyewaan = Penyewaan::find($idPenyewaan);
        if (!$penyewaan) {
            return redirect()->route('dashboard')->with('error', 'Data penyewaan tidak ditemukan');
        }

        return redirect()->route('pembayaran.success', $idPenyewaan)->with('success', 'Pembayaran berhasil!');
    }

    /**
     * Handle unfinish redirect (customer closes payment page)
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        // Extract ID Penyewaan dari Order ID
        $parts = explode('-', $orderId);
        $idPenyewaan = $parts[1] ?? null;

        if (!$idPenyewaan) {
            return redirect()->route('dashboard')->with('warning', 'Pembayaran belum diselesaikan');
        }

        $penyewaan = Penyewaan::find($idPenyewaan);
        if (!$penyewaan) {
            return redirect()->route('dashboard')->with('error', 'Data penyewaan tidak ditemukan');
        }

        return view('pembayaran.unfinish', compact('penyewaan'));
    }

    /**
     * Handle error redirect (payment failed)
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        
        // Extract ID Penyewaan dari Order ID
        $parts = explode('-', $orderId);
        $idPenyewaan = $parts[1] ?? null;

        if (!$idPenyewaan) {
            return redirect()->route('dashboard')->with('error', 'Pembayaran gagal');
        }

        $penyewaan = Penyewaan::find($idPenyewaan);
        if (!$penyewaan) {
            return redirect()->route('dashboard')->with('error', 'Data penyewaan tidak ditemukan');
        }

        return view('pembayaran.error', compact('penyewaan'));
    }
}
