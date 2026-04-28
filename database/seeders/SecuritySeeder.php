<?php

namespace Database\Seeders;

use App\Models\Access;
use App\Models\AccessType;
use App\Models\Asset;
use App\Models\AssetCamera;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SecuritySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAccessTypes();
        $this->seedSecurityUser();
        $this->seedAccesses();
        $this->seedCameraCategoryAndAssets();
    }

    private function seedAccessTypes(): void
    {
        $types = [
            ['name' => 'Servidor',     'slug' => 'server',  'color' => '#0ea5e9', 'icon' => '🖥', 'default_fields' => ['ssh_port', 'os']],
            ['name' => 'Antena WiFi',  'slug' => 'wifi',    'color' => '#10b981', 'icon' => '📶', 'default_fields' => ['ssid', 'mode', 'channel']],
            ['name' => 'Cámara IP',    'slug' => 'camera',  'color' => '#dc2626', 'icon' => '📹', 'default_fields' => ['rtsp_port', 'onvif_port']],
            ['name' => 'Servicio web', 'slug' => 'web',     'color' => '#6366f1', 'icon' => '🌐', 'default_fields' => ['admin_panel', '2fa']],
            ['name' => 'Correo',       'slug' => 'email',   'color' => '#f59e0b', 'icon' => '✉️', 'default_fields' => ['imap_host', 'smtp_host']],
            ['name' => 'Base de datos','slug' => 'database','color' => '#8b5cf6', 'icon' => '🗄', 'default_fields' => ['engine', 'database']],
            ['name' => 'Switch / Router','slug' => 'network','color' => '#06b6d4', 'icon' => '🔌', 'default_fields' => ['mgmt_vlan']],
            ['name' => 'Otro',         'slug' => 'other',   'color' => '#64748b', 'icon' => '🔐', 'default_fields' => []],
        ];
        foreach ($types as $t) {
            AccessType::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }

    private function seedSecurityUser(): void
    {
        $role = Role::where('name', 'security_admin')->first();
        if (! $role) {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'seguridad@gptservices.com'],
            [
                'name' => 'Administrador de Seguridad',
                'password' => Hash::make('password'),
                'employee_code' => 'SEC-001',
                'department' => 'TI',
                'position' => 'Security Admin',
                'is_active' => true,
            ]
        );
        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    private function seedAccesses(): void
    {
        $types = AccessType::pluck('id', 'slug');
        $admin = User::where('email', 'admin@gptservices.com')->first();
        $tiLead = User::where('email', 'knunez@gptservices.com')->first();
        $datacenter = Location::where('name', 'DataCenter')->first();
        $piso6 = Location::where('name', 'Piso 6')->first();
        $monterrey = Location::where('name', 'Comercial MTY')->first();

        $samples = [
            [
                'type' => 'server', 'name' => 'Servidor producción Web1',
                'description' => 'Web app principal · Ubuntu 22.04',
                'ip' => '10.0.1.10', 'hostname' => 'srv-prod-web-01', 'port' => 22,
                'username' => 'devops', 'password' => 'TempProd!2026Web1',
                'location_id' => $datacenter?->id, 'owner_id' => $tiLead?->id,
                'last_rotated_at' => Carbon::now()->subDays(15),
                'extras' => [
                    ['field_name' => 'os', 'field_label' => 'Sistema operativo', 'field_value' => 'Ubuntu 22.04 LTS', 'is_sensitive' => false],
                    ['field_name' => 'sudo_password', 'field_label' => 'Sudo password', 'field_value' => 'SudoPass!Web1', 'is_sensitive' => true],
                ],
            ],
            [
                'type' => 'server', 'name' => 'Servidor base de datos Master',
                'description' => 'MariaDB 10.4 · réplica master',
                'ip' => '10.0.1.20', 'hostname' => 'srv-db-master', 'port' => 3306,
                'username' => 'root', 'password' => 'M4st3rDB!RootPass',
                'location_id' => $datacenter?->id, 'owner_id' => $tiLead?->id,
                'last_rotated_at' => Carbon::now()->subDays(120), // stale!
            ],
            [
                'type' => 'wifi', 'name' => 'WiFi Corporativo · Reforma',
                'description' => 'Red interna de empleados',
                'username' => null, 'password' => 'GPT2026!WiFiCorp',
                'location_id' => $piso6?->id,
                'last_rotated_at' => Carbon::now()->subDays(45),
                'extras' => [
                    ['field_name' => 'ssid', 'field_label' => 'SSID', 'field_value' => 'GPT-Corp', 'is_sensitive' => false],
                    ['field_name' => 'mode', 'field_label' => 'Modo', 'field_value' => 'WPA3-Enterprise', 'is_sensitive' => false],
                    ['field_name' => 'channel', 'field_label' => 'Canal', 'field_value' => '36 (5GHz)', 'is_sensitive' => false],
                ],
            ],
            [
                'type' => 'wifi', 'name' => 'WiFi Invitados · Reforma',
                'description' => 'Red para visitantes',
                'username' => null, 'password' => 'Invitados2026',
                'location_id' => $piso6?->id,
                'last_rotated_at' => Carbon::now()->subDays(95), // stale
                'extras' => [
                    ['field_name' => 'ssid', 'field_label' => 'SSID', 'field_value' => 'GPT-Guests', 'is_sensitive' => false],
                ],
            ],
            [
                'type' => 'web', 'name' => 'Panel admin Cloudflare',
                'description' => 'DNS y WAF',
                'url' => 'https://dash.cloudflare.com/',
                'username' => 'devops@gptservices.com', 'password' => 'CFlareAdmin2026!',
                'last_rotated_at' => Carbon::now()->subDays(60),
                'extras' => [
                    ['field_name' => '2fa', 'field_label' => '2FA activo', 'field_value' => 'Sí (TOTP)', 'is_sensitive' => false],
                    ['field_name' => 'recovery_codes', 'field_label' => 'Códigos de recuperación', 'field_value' => '4F2A-9B3D-7E1C-XX', 'is_sensitive' => true],
                ],
            ],
            [
                'type' => 'web', 'name' => 'Consola AWS · cuenta principal',
                'description' => 'Producción AWS',
                'url' => 'https://gptservices.signin.aws.amazon.com/console',
                'username' => 'devops', 'password' => 'AWSConsole!2026',
                'last_rotated_at' => Carbon::now()->subDays(30),
            ],
            [
                'type' => 'email', 'name' => 'Correo noreply@gptservices.com',
                'description' => 'Cuenta SMTP para notificaciones del sistema',
                'username' => 'noreply@gptservices.com', 'password' => 'SmtpNoReply2026!',
                'last_rotated_at' => Carbon::now()->subDays(80),
                'extras' => [
                    ['field_name' => 'smtp_host', 'field_label' => 'SMTP Host', 'field_value' => 'smtp.gmail.com', 'is_sensitive' => false],
                    ['field_name' => 'smtp_port', 'field_label' => 'Puerto', 'field_value' => '587', 'is_sensitive' => false],
                ],
            ],
            [
                'type' => 'network', 'name' => 'Switch Cisco core · Reforma',
                'description' => 'Catalyst 9200-24T',
                'ip' => '10.0.0.1', 'hostname' => 'sw-core-01',
                'username' => 'admin', 'password' => 'CiscoEnable!2026',
                'location_id' => $datacenter?->id,
                'last_rotated_at' => Carbon::now()->subDays(40),
                'extras' => [
                    ['field_name' => 'enable_password', 'field_label' => 'Enable password', 'field_value' => 'EnableSecret!', 'is_sensitive' => true],
                    ['field_name' => 'mgmt_vlan', 'field_label' => 'VLAN de gestión', 'field_value' => '99', 'is_sensitive' => false],
                ],
            ],
            [
                'type' => 'database', 'name' => 'PostgreSQL · CRM producción',
                'description' => 'Base del CRM',
                'ip' => '10.0.2.50', 'port' => 5432,
                'username' => 'crm_admin', 'password' => 'CrmDbProd!2026',
                'last_rotated_at' => Carbon::now()->subDays(50),
                'extras' => [
                    ['field_name' => 'engine', 'field_label' => 'Motor', 'field_value' => 'PostgreSQL 15', 'is_sensitive' => false],
                    ['field_name' => 'database', 'field_label' => 'Base', 'field_value' => 'crm_prod', 'is_sensitive' => false],
                ],
            ],
        ];

        foreach ($samples as $s) {
            $extras = $s['extras'] ?? [];
            $typeSlug = $s['type'] ?? null;
            unset($s['extras'], $s['type']);

            $access = Access::updateOrCreate(
                ['name' => $s['name']],
                array_merge($s, [
                    'type_id' => $types[$typeSlug] ?? null,
                    'created_by' => $admin?->id,
                    'is_active' => true,
                ])
            );

            $access->extraFields()->delete();
            foreach ($extras as $i => $e) {
                $access->extraFields()->create($e + ['position' => $i + 1]);
            }
        }
    }

    private function seedCameraCategoryAndAssets(): void
    {
        // Ensure category exists
        $cat = AssetCategory::firstOrCreate(
            ['name' => 'Cámaras'],
            ['description' => 'Cámaras IP de seguridad y CCTV']
        );

        $admin = User::where('email', 'admin@gptservices.com')->first();
        $datacenter = Location::where('name', 'DataCenter')->first();
        $piso6 = Location::where('name', 'Piso 6')->first();
        $piso5 = Location::where('name', 'Piso 5')->first();

        $cameraAccessId = Access::where('name', 'Servidor producción Web1')->value('id'); // placeholder

        $cameras = [
            [
                'code' => 'CAM-001',
                'brand' => 'Hikvision', 'model' => 'DS-2CD2T43G2-2I',
                'serial' => 'HK20260100001', 'location' => 'Piso 5 · Recepción',
                'cost' => 4800,
                'cam' => [
                    'protocol' => 'rtsp',
                    'stream_url' => 'rtsp://10.0.10.11:554/Streaming/Channels/101',
                    'mjpeg_url' => 'http://10.0.10.11/cgi-bin/mjpg/video.cgi',
                    'snapshot_url' => 'http://10.0.10.11/ISAPI/Streaming/channels/101/picture',
                    'nvr_url' => 'http://10.0.10.10/login',
                    'resolution' => '2688x1520',
                    'fps' => 25,
                    'has_audio' => true,
                    'has_motion_detection' => true,
                ],
            ],
            [
                'code' => 'CAM-002',
                'brand' => 'Hikvision', 'model' => 'DS-2DE5232W-AE',
                'serial' => 'HK20260100002', 'location' => 'Piso 5 · Pasillo norte',
                'cost' => 12500,
                'cam' => [
                    'protocol' => 'rtsp',
                    'stream_url' => 'rtsp://10.0.10.12:554/Streaming/Channels/101',
                    'snapshot_url' => 'http://10.0.10.12/ISAPI/Streaming/channels/101/picture',
                    'nvr_url' => 'http://10.0.10.10/login',
                    'resolution' => '1920x1080',
                    'fps' => 30,
                    'has_audio' => false,
                    'has_motion_detection' => true,
                    'has_ptz' => true,
                ],
            ],
            [
                'code' => 'CAM-003',
                'brand' => 'Dahua', 'model' => 'IPC-HFW3441T-AS',
                'serial' => 'DH20260100003', 'location' => 'Piso 6 · Sala servidores',
                'cost' => 5200,
                'cam' => [
                    'protocol' => 'rtsp',
                    'stream_url' => 'rtsp://10.0.10.13:554/cam/realmonitor?channel=1&subtype=0',
                    'snapshot_url' => 'http://10.0.10.13/cgi-bin/snapshot.cgi',
                    'nvr_url' => 'http://10.0.10.10/login',
                    'resolution' => '2688x1520',
                    'fps' => 25,
                    'has_motion_detection' => true,
                ],
            ],
            [
                'code' => 'CAM-004',
                'brand' => 'Axis', 'model' => 'M3045-V',
                'serial' => 'AX20260100004', 'location' => 'Piso 6 · Recepción dirección',
                'cost' => 8900,
                'cam' => [
                    'protocol' => 'http',
                    'mjpeg_url' => 'http://10.0.10.14/axis-cgi/mjpg/video.cgi',
                    'snapshot_url' => 'http://10.0.10.14/jpg/image.jpg',
                    'resolution' => '1920x1080',
                    'fps' => 30,
                    'has_audio' => true,
                ],
            ],
            [
                'code' => 'CAM-005',
                'brand' => 'Reolink', 'model' => 'RLC-820A',
                'serial' => 'RL20260100005', 'location' => 'Almacén TI',
                'cost' => 2500,
                'cam' => [
                    'protocol' => 'rtsp',
                    'stream_url' => 'rtsp://10.0.10.15:554/h264Preview_01_main',
                    'snapshot_url' => 'http://10.0.10.15/cgi-bin/api.cgi?cmd=Snap&channel=0',
                    'nvr_url' => 'http://10.0.10.10/login',
                    'resolution' => '3840x2160',
                    'fps' => 25,
                    'has_motion_detection' => true,
                ],
            ],
        ];

        foreach ($cameras as $c) {
            $internal = sprintf('GPT-%s', $c['code']);
            $asset = Asset::updateOrCreate(
                ['internal_code' => $internal],
                [
                    'category_id' => $cat->id,
                    'type' => 'camera',
                    'brand' => $c['brand'],
                    'model' => $c['model'],
                    'serial_number' => $c['serial'],
                    'location' => $c['location'],
                    'status' => 'available',
                    'condition' => 'good',
                    'purchase_date' => Carbon::now()->subMonths(rand(2, 18)),
                    'purchase_cost' => $c['cost'],
                    'created_by' => $admin?->id,
                ]
            );

            AssetCamera::updateOrCreate(
                ['asset_id' => $asset->id],
                $c['cam']
            );
        }
    }
}
