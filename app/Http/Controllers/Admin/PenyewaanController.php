<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyewaan;
use Illuminate\Http\Request;

class PenyewaanController extends Controller
{
    public function index()
    {
        $penyewaans = Penyewaan::with(['pelanggan', 'motor', 'pembayaran'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.penyewaan.index', compact('penyewaans'));
    }

    public function show($id)
    {
        $penyewaan = Penyewaan::with(['pelanggan', 'motor', 'details', 'pembayaran'])->findOrFail($id);
        return view('admin.penyewaan.show', compact('penyewaan'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_sewa' => 'required',
        ]);

        $penyewaan = Penyewaan::findOrFail($id);
        $oldStatus = $penyewaan->status_sewa;
        $newStatus = $request->status_sewa;

        // If status changes to Dibatalkan, increment stock
        if ($oldStatus !== 'Dibatalkan' && $newStatus === 'Dibatalkan') {
            $penyewaan->motor->increment('stok');
        }
        // Optional: If status was Dibatalkan and changes back to something else, decrement stock
        elseif ($oldStatus === 'Dibatalkan' && $newStatus !== 'Dibatalkan') {
            $penyewaan->motor->decrement('stok');
        }

        $penyewaan->status_sewa = $newStatus;
        $penyewaan->save();

        return redirect()->back()->with('success', 'Status penyewaan diperbarui');
    }
}
