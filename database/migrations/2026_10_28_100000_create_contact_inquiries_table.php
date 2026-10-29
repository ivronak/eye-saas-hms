<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public "Contact us" form submissions (landing site). Platform-level, not
 * tenant-scoped — read and handled from the Super Admin panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();

            // Who is writing
            $table->string('name', 120);
            $table->string('email', 190);
            $table->string('phone', 30)->nullable();
            $table->string('hospital_name', 190)->nullable();

            // What they want
            $table->string('inquiry_type', 30)->default('general'); // general|demo|pricing|support|partnership|other
            $table->string('subject', 190)->nullable();
            $table->text('message');

            // Handling workflow (shown as the sidebar badge while "new")
            $table->string('status', 20)->default('new'); // new|read|replied|closed
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable(); // platform_admins.id (no FK: history must survive admin removal)
            $table->text('admin_notes')->nullable();

            // Spam / audit trail
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('inquiry_type');
            $table->index('created_at');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
