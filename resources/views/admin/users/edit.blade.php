@extends('layouts.admin')

@section('title', 'Edit Akun Internal')
@section('page-title', 'Edit Akun Internal')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-extrabold text-slate-900">Edit Akun: {{ $user->name }}</h2>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">
                    Password Baru (Kosongkan bila tidak ingin mengubah)
                </label>
                <input type="password" name="password" id="password"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700 mb-1">Role Internal</label>
                    <select name="role" id="role" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="admin" {{ old('role', $user->role?->value) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="cashier" {{ old('role', $user->role?->value) === 'cashier' ? 'selected' : '' }}>Kasir</option>
                        <option value="owner" {{ old('role', $user->role?->value) === 'owner' ? 'selected' : '' }}>Pemilik (Owner)</option>
                    </select>
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-semibold text-slate-700 mb-1">Status Akun</label>
                    <select name="is_active" id="is_active" required
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-red-600 focus:border-red-600">
                        <option value="1" {{ old('is_active', (string)(int)$user->is_active) === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', (string)(int)$user->is_active) === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    Perbarui Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
