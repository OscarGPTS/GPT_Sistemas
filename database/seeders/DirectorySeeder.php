<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\DeviceUser;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        $locationMap = $this->seedLocations();
        $this->seedDeviceUsers($locationMap);
    }

    /**
     * @return array<string, Location>
     */
    private function seedLocations(): array
    {
        $tree = [
            ['name' => 'Oficina CDMX', 'type' => 'site', 'children' => [
                ['name' => 'Edificio Reforma', 'type' => 'building', 'children' => [
                    ['name' => 'Piso 5', 'type' => 'floor', 'children' => [
                        ['name' => 'Operaciones', 'type' => 'area'],
                        ['name' => 'Marketing', 'type' => 'area'],
                        ['name' => 'Sala de juntas Norte', 'type' => 'room'],
                    ]],
                    ['name' => 'Piso 6', 'type' => 'floor', 'children' => [
                        ['name' => 'TI', 'type' => 'area'],
                        ['name' => 'Finanzas', 'type' => 'area'],
                        ['name' => 'Recursos Humanos', 'type' => 'area'],
                        ['name' => 'Dirección', 'type' => 'area'],
                    ]],
                ]],
            ]],
            ['name' => 'Oficina Guadalajara', 'type' => 'site', 'children' => [
                ['name' => 'Planta única', 'type' => 'floor', 'children' => [
                    ['name' => 'Ventas GDL', 'type' => 'area'],
                    ['name' => 'Soporte', 'type' => 'area'],
                ]],
            ]],
            ['name' => 'Oficina Monterrey', 'type' => 'site', 'children' => [
                ['name' => 'Comercial MTY', 'type' => 'area'],
            ]],
            ['name' => 'Site Principal', 'type' => 'site', 'children' => [
                ['name' => 'DataCenter', 'type' => 'warehouse'],
            ]],
            ['name' => 'Almacén TI', 'type' => 'warehouse'],
            ['name' => 'Home Office', 'type' => 'other'],
        ];

        $map = [];
        foreach ($tree as $i => $node) {
            $this->createNode($node, null, $map, $i);
        }
        return $map;
    }

    private function createNode(array $node, ?int $parentId, array &$map, int $position = 0): void
    {
        $location = Location::updateOrCreate(
            ['name' => $node['name'], 'parent_id' => $parentId],
            [
                'type' => $node['type'],
                'is_active' => true,
                'position' => $position,
            ]
        );
        $map[$node['name']] = $location;

        foreach ($node['children'] ?? [] as $i => $child) {
            $this->createNode($child, $location->id, $map, $i);
        }
    }

    private function seedDeviceUsers(array $locationMap): void
    {
        // Mapeo de usuarios del sistema → ubicación de impresión
        $assignments = [
            'admin@gptservices.com'     => 'TI',
            'ti@gptservices.com'        => 'TI',
            'rmendoza@gptservices.com'  => 'TI',
            'knunez@gptservices.com'    => 'TI',
            'dortiz@gptservices.com'    => 'TI',
            'gerente@gptservices.com'   => 'Operaciones',
            'fcastillo@gptservices.com' => 'Operaciones',
            'aruiz@gptservices.com'     => 'Operaciones',
            'hdominguez@gptservices.com'=> 'Operaciones',
            'psalgado@gptservices.com'  => 'Finanzas',
            'jramirez@gptservices.com'  => 'Finanzas',
            'asolis@gptservices.com'    => 'Finanzas',
            'lvelazquez@gptservices.com'=> 'Recursos Humanos',
            'mtorres@gptservices.com'   => 'Recursos Humanos',
            'egalvez@gptservices.com'   => 'Comercial MTY',
            'crivera@gptservices.com'   => 'Ventas GDL',
            'raguilar@gptservices.com'  => 'Ventas GDL',
            'vibarra@gptservices.com'   => 'Marketing',
            'sparedes@gptservices.com'  => 'Marketing',
            'auditor@gptservices.com'   => 'Dirección',
            'caja@gptservices.com'      => 'Finanzas',
        ];

        // Buscar impresoras
        $printers = Asset::where('type', 'printer')
            ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%mpresor%'))
            ->get();

        $usedCodes = [];
        $code = 1001;

        foreach ($assignments as $email => $locationName) {
            $user = User::where('email', $email)->first();
            $location = $locationMap[$locationName] ?? null;
            if (! $user) {
                continue;
            }

            // Generate unique 4-digit code
            while (in_array((string) $code, $usedCodes, true) || DeviceUser::where('print_code', (string) $code)->exists()) {
                $code++;
            }

            $deviceUser = DeviceUser::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $user->employee_code,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'mailbox' => 'INBOX-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                    'print_code' => (string) $code,
                    'location_id' => $location?->id,
                    'is_active' => $user->is_active,
                ]
            );
            $usedCodes[] = (string) $code;
            $code++;

            // Assign 1-2 printers (random)
            if ($printers->isNotEmpty() && rand(1, 10) <= 7) {
                $count = min(rand(1, 2), $printers->count());
                $sample = $printers->random($count);
                $sync = collect($sample)->mapWithKeys(fn ($p) => [
                    $p->id => ['granted_at' => now(), 'granted_by' => null],
                ])->all();
                $deviceUser->printers()->sync($sync);
            }
        }
    }
}
