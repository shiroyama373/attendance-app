<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    /**
     * スタッフ一覧を表示
     */
    public function index()
    {
        // 全ユーザーを取得（一般・管理者含む）
        $users = User::where('is_admin', false)->get();

        return view('admin.staff.index', compact('users'));
    }
}