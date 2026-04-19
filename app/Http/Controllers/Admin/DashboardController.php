<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DashboardController extends Controller
{
    public function index(): View
    {
        $storagePath = storage_path('app');
        $usedBytes = 0;

        if (is_dir($storagePath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storagePath));

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $usedBytes += $file->getSize();
                }
            }
        }

        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'totalMessages' => Message::query()->count(),
            'usedBytes' => $usedBytes,
            'limitBytes' => 5 * 1024 * 1024 * 1024,
        ]);
    }
}
