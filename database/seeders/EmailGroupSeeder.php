<?php

namespace Database\Seeders;

use App\Models\EmailGroup;
use App\Models\EmailGroupMember;
use Illuminate\Database\Seeder;

class EmailGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Test Receiver',
                'description' => 'Grup uji coba untuk testing pengiriman email',
                'type' => 'static',
                'criteria' => null,
                'members' => [
                    ['email' => 'choxaneh@gmail.com', 'name' => 'Choxa'],
                    ['email' => 'muti.relegi@gmail.com', 'name' => 'Muti'],
                ],
            ],
            [
                'name' => 'User Baru',
                'description' => 'User yang mendaftar dalam 30 hari terakhir',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_baru', 'params' => ['days' => 30]],
                'members' => [],
            ],
            [
                'name' => 'User Akan Expired',
                'description' => 'User dengan subscription yang akan expired dalam 7 hari',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'akan_expired', 'params' => ['days' => 7]],
                'members' => [],
            ],
            [
                'name' => 'User Belum Bayar',
                'description' => 'User yang belum pernah melakukan pembayaran',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'belum_bayar', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'User Loyal',
                'description' => 'User dengan 3 atau lebih transaksi sukses',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_loyal', 'params' => ['min_trx' => 3]],
                'members' => [],
            ],
            [
                'name' => 'User Baru Bayar Hari Ini',
                'description' => 'User yang melakukan pembayaran pertama hari ini',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'baru_bayar_hari_ini', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'Semua User Aktif',
                'description' => 'User dengan subscription yang masih aktif',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_aktif', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'User Gratis',
                'description' => 'User yang terdaftar tapi belum pernah berlangganan',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_gratis', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'User Expired',
                'description' => 'User yang subscription-nya sudah habis — target re-engagement',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_expired', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'User Baru Minggu Ini',
                'description' => 'User yang mendaftar dalam 7 hari terakhir — window onboarding',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_baru_minggu_ini', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'Akan Expired 3 Hari Lagi',
                'description' => 'User dengan subscription yang habis dalam 3 hari — urgensi tinggi',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'akan_expired_3hari', 'params' => []],
                'members' => [],
            ],
            [
                'name' => 'User Dorman 90 Hari',
                'description' => 'User yang pernah bayar tapi tidak ada transaksi dalam 90 hari terakhir',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_dorman', 'params' => ['days' => 90]],
                'members' => [],
            ],
            [
                'name' => 'User Loyal Super',
                'description' => 'User dengan 5 atau lebih transaksi sukses — VIP readership',
                'type' => 'dynamic',
                'criteria' => ['filter' => 'user_loyal', 'params' => ['min_trx' => 5]],
                'members' => [],
            ],
        ];

        foreach ($groups as $groupData) {
            $members = $groupData['members'];
            unset($groupData['members']);

            $group = EmailGroup::updateOrCreate(
                ['name' => $groupData['name']],
                array_merge($groupData, ['is_active' => true])
            );

            if ($group->type === 'static' && ! empty($members)) {
                foreach ($members as $member) {
                    EmailGroupMember::updateOrCreate(
                        ['email_group_id' => $group->id, 'email' => $member['email']],
                        ['name' => $member['name']]
                    );
                }
            }
        }
    }
}
