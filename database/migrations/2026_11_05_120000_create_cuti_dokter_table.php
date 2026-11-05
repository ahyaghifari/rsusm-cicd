<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ACTIONS = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'];

    public function up(): void
    {
        Schema::create('cuti_dokter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rumah_sakit_id')->constrained('rumah_sakit')->cascadeOnDelete();
            $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('alasan');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['dokter_id', 'tanggal_mulai', 'tanggal_selesai']);
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect(self::ACTIONS)->map(fn ($a) => Permission::firstOrCreate([
            'name'       => "{$a}_cuti::dokter",
            'guard_name' => 'web',
        ]));

        foreach (['super_admin', 'admin', 'informasi'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_dokter');
        Permission::where('name', 'like', '%_cuti::dokter')->delete();
    }
};
