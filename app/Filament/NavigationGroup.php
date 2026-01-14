<?php

namespace App\Filament;

enum NavigationGroup: string
{
    case Transaksi = 'Transaksi';
    case Penyimpanan = 'Penyimpanan';
    case Laporan = 'Laporan';
    case MasterData = 'Master Data';
    case Pengaturan = 'Pengaturan';
}
