<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('profile')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('name', 'like', $term)
                        ->orWhere('username', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function ban(User $user): RedirectResponse
    {
        if (! $user->is_admin) {
            $user->update(['is_banned' => true]);
        }

        return back();
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update(['is_banned' => false]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is_admin, 403);
        $user->delete();

        return back();
    }
}
