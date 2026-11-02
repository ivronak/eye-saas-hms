<?php

namespace Database\Seeders;

use App\Models\Hospital\CaseType;
use App\Models\Hospital\HospitalUser;
use App\Models\Hospital\OT\OtBooking;
use App\Models\Hospital\OT\OtPreOp;
use App\Models\Hospital\Patient;
use App\Models\Hospital\Slot;
use App\Models\Platform\Tenant;
use App\Services\Hospital\PatientService;
use App\Services\Platform\TenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DemoTenantSeeder — a fully FAKE hospital for app-store screenshots / demos.
 *
 * Creates (idempotently) the tenant "Eyenosis Demo Eye Hospital"
 * (slug: eyenosis-demo) with staff, today's OPD queue and a few OT cases.
 * Every name, phone and email is invented (example.com / 98000xxxxx) — no real
 * patient data is read or copied.
 *
 *   php artisan db:seed --class=DemoTenantSeeder
 *
 * Logins (password for all: Demo@12345)
 *   hospital_admin  demo.admin@example.com
 *   doctor          doctor.aarav@example.com   (Dr. Aarav Mehta)
 *   doctor          doctor.nisha@example.com   (Dr. Nisha Patel)
 *   receptionist    reception@example.com
 *   ot_assistant    ot.assistant@example.com
 *   ward            ward@example.com
 *   counselling     counselling@example.com
 *   accountant      accounts@example.com
 *
 * Each section is wrapped in try/catch so one schema mismatch does not stop
 * the rest; failures are printed.
 */
class DemoTenantSeeder extends Seeder
{
    private const PASSWORD = 'Demo@12345';
    private const SLUG = 'eyenosis-demo';

    private Tenant $tenant;

    /** @var array<string,HospitalUser> */
    private array $users = [];

    public function run(): void
    {
        // Never send the welcome e-mail to a real SMTP server from a seeder.
        config(['mail.default' => 'log']);

        $this->tenant = Tenant::where('slug', self::SLUG)->first() ?? app(TenantService::class)->createTenant([
            'hospital_name' => 'Eyenosis Demo Eye Hospital',
            'hospital_code' => 'DEMO',
            'slug' => self::SLUG,
            'admin_name' => 'Demo Admin',
            'admin_email' => 'demo.admin@example.com',
            'admin_phone' => '+919800000001',
            'password' => self::PASSWORD,
            'country' => 'India',
            'state' => 'Gujarat',
            'district' => 'Ahmedabad',
            'city' => 'Ahmedabad',
            'plan' => 'monthly',
            'start_trial' => '1',
            'skip_approval' => true,
        ]);
        app()->instance('tenant', $this->tenant);
        $this->say("Tenant #{$this->tenant->id} {$this->tenant->slug} ({$this->tenant->status})");

        $this->section('staff', fn () => $this->seedStaff());
        $this->section('OPD patients', fn () => $this->seedPatients());
        $this->section('OT cases', fn () => $this->seedOt());

        $this->say('Done. Login at /' . self::SLUG . '/login  (password: ' . self::PASSWORD . ')');
    }

    private function seedStaff(): void
    {
        $roleIds = DB::table('roles')->where('tenant_id', $this->tenant->id)->pluck('id', 'slug');

        $defs = [
            'admin' => ['hospital_admin', 'Demo Admin', 'demo.admin@example.com', '9800000001', []],
            'aarav' => ['doctor', 'Dr. Aarav Mehta', 'doctor.aarav@example.com', '9800000002', ['doctor_type' => 'primary', 'doctor_prefix' => 'AM', 'registration_no' => 'GMC-10231', 'experience_years' => 12]],
            'nisha' => ['doctor', 'Dr. Nisha Patel', 'doctor.nisha@example.com', '9800000003', ['doctor_type' => 'secondary', 'doctor_prefix' => 'NP', 'registration_no' => 'GMC-20877', 'experience_years' => 9]],
            'reception' => ['receptionist', 'Priya Shah', 'reception@example.com', '9800000004', []],
            'ot' => ['ot_assistant', 'Kiran Desai', 'ot.assistant@example.com', '9800000005', []],
            'ward' => ['ward_management', 'Ravi Joshi', 'ward@example.com', '9800000006', []],
            'counselling' => ['counselling', 'Meena Rao', 'counselling@example.com', '9800000007', []],
            'accounts' => ['accountant', 'Sunil Trivedi', 'accounts@example.com', '9800000008', []],
        ];

        foreach ($defs as $key => [$role, $name, $email, $phone, $extra]) {
            $this->users[$key] = HospitalUser::withoutGlobalScopes()->where('email', $email)->first()
                ?? HospitalUser::create([
                    'tenant_id' => $this->tenant->id,
                    'role_id' => $roleIds[$role] ?? null,
                    'name' => $name,
                    'email' => $email,
                    'password' => self::PASSWORD,
                    'original_password' => self::PASSWORD,
                    'contact' => $phone,
                    'status' => 'active',
                    ...$extra,
                ]);
        }

        // Doctors send "Ready for OT" patients to this OT Assistant.
        foreach (['aarav', 'nisha'] as $d) {
            $this->users[$d]->update(['default_ot_assistant_id' => $this->users['ot']->id]);
        }
        $this->say('  ' . count($this->users) . ' staff accounts');
    }

    private function seedPatients(): void
    {
        if (Patient::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->exists()) {
            $this->say('  patients already exist — skipped');

            return;
        }

        $cases = CaseType::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->get();
        if ($cases->isEmpty()) {
            $cases = collect([CaseType::create(['tenant_id' => $this->tenant->id, 'case_type' => 'New Consultation', 'case_fee' => 300])]);
        }

        $people = [
            ['Rohan', 'Sharma', 42, 'male'], ['Anjali', 'Verma', 35, 'female'], ['Vikram', 'Singh', 58, 'male'],
            ['Sneha', 'Kulkarni', 29, 'female'], ['Imran', 'Qureshi', 61, 'male'], ['Pooja', 'Nair', 47, 'female'],
            ['Karan', 'Malhotra', 33, 'male'], ['Divya', 'Reddy', 52, 'female'], ['Harsh', 'Trivedi', 24, 'male'],
            ['Lata', 'Bhatt', 67, 'female'], ['Nitin', 'Joshi', 45, 'male'], ['Farah', 'Khan', 38, 'female'],
            ['Mohan', 'Lal', 71, 'male'], ['Riya', 'Choksi', 19, 'female'],
        ];
        $svc = app(PatientService::class);
        $today = now()->toDateString();
        $doctors = [$this->users['aarav'], $this->users['nisha']];

        foreach ($people as $i => [$first, $last, $age, $gender]) {
            $doctor = $doctors[$i % 2];
            $case = $cases[$i % $cases->count()];
            $patient = $svc->registerWalkIn([
                'first_name' => $first,
                'last_name' => $last,
                'age' => $age,
                'gender' => $gender,
                'contact_no' => '98000' . str_pad((string) (10 + $i), 5, '0', STR_PAD_LEFT),
                'appointment_date' => $today,
                'doctor_id' => $doctor->id,
                'case_id' => $case->id,
                'case_fee' => $case->case_fee,
                'reception_id' => $this->users['reception']->id,
                'is_old_patient' => $i % 4 === 0,
                'checked_in_at' => now()->subMinutes(90 - $i * 5),
            ], $this->tenant->id);

            // 0-5 waiting for primary exam, 6-9 primary done (awaiting secondary), 10-13 fully done.
            if ($i >= 6) {
                $patient->update(['primary_done_at' => now()->subMinutes(70 - $i * 3)]);
            }
            if ($i >= 10) {
                $patient->update(['secondary_done_at' => now()->subMinutes(30 - $i)]);
            }
        }
        $this->say('  ' . count($people) . ' OPD patients for today');
    }

    private function seedOt(): void
    {
        if (OtBooking::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->exists()) {
            $this->say('  OT bookings already exist — skipped');

            return;
        }

        $patients = Patient::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->whereNotNull('primary_done_at')
            ->orderBy('id')
            ->limit(6)
            ->get();
        foreach ($patients as $p) {
            if (! $p->secondary_done_at) {
                $p->update(['secondary_done_at' => now()->subMinutes(20)]);
            }
        }
        $slot = Slot::withoutGlobalScopes()->where('tenant_id', $this->tenant->id)->first();
        if ($patients->count() < 6) {
            $this->say('  not enough completed patients for OT cases — skipped');

            return;
        }

        // [status, doctor key, eye, type, amount]
        $plan = [
            ['counselled', 'aarav', 'RE', 'Cataract (Phaco + IOL)', 28000],
            ['counselled', 'nisha', 'LE', 'Cataract (Phaco + IOL)', 32000],
            ['payment_verified', 'aarav', 'RE', 'Cataract (SICS)', 22000],
            ['in_ward', 'nisha', 'Both', 'Cataract (Phaco + IOL)', 54000],  // + pre-op "preparing" => doctor "Review"
            ['ready', 'aarav', 'LE', 'Pterygium Excision', 15000],
            ['operated', 'nisha', 'RE', 'Cataract (Phaco + IOL)', 30000],
        ];

        foreach ($plan as $i => [$status, $docKey, $eye, $type, $amount]) {
            $booking = OtBooking::create([
                'tenant_id' => $this->tenant->id,
                'patient_id' => $patients[$i]->id,
                'ot_doctor_id' => $this->users[$docKey]->id,
                'ot_assistant_id' => in_array($status, ['ready', 'operated'], true) ? $this->users['ot']->id : null,
                'booked_by' => $this->users[$docKey]->id,
                'surgery_date' => now()->toDateString(),
                'slot_id' => $slot?->id,
                'eye' => $eye,
                'ot_type' => $type,
                'package_amount' => $amount,
                'ot_status' => $status,
                'attended_at' => in_array($status, ['in_ward', 'ready', 'operated'], true) ? now()->subMinutes(45) : null,
                'operated_at' => $status === 'operated' ? now()->subMinutes(10) : null,
            ]);

            if ($status === 'in_ward') {
                OtPreOp::create([
                    'tenant_id' => $this->tenant->id,
                    'ot_booking_id' => $booking->id,
                    'pre_op_status' => OtPreOp::STATUS_PREPARING,
                    'entered_by' => $this->users['ward']->id,
                ]);
            }
        }
        $this->say('  ' . count($plan) . ' OT cases (payment pending / ward / ready / operated)');
    }

    private function section(string $name, \Closure $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            $this->say("  !! {$name} failed: " . $e->getMessage());
        }
    }

    private function say(string $line): void
    {
        $this->command?->info($line);
    }
}
