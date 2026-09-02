<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountHierarchySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $headers = [
                ['1000','ASET','ASSET','DEBIT',1000,null],
                ['1100','Aset Lancar','ASSET','DEBIT',1100,'1000'],
                ['1200','Piutang','ASSET','DEBIT',1200,'1000'],
                ['1300','Aset Tetap','ASSET','DEBIT',1300,'1000'],
                ['2000','LIABILITAS','LIABILITY','CREDIT',2000,null],
                ['2100','Simpanan Anggota','LIABILITY','CREDIT',2100,'2000'],
                ['2200','Liabilitas Lainnya','LIABILITY','CREDIT',2200,'2000'],
                ['3000','EKUITAS','EQUITY','CREDIT',3000,null],
                ['3100','Modal / Ekuitas','EQUITY','CREDIT',3100,'3000'],
                ['3200','SHU Tahun Berjalan','EQUITY','CREDIT',3200,'3000'],
                ['4000','PENDAPATAN','REVENUE','CREDIT',4000,null],
                ['4100','Pendapatan Pinjaman','REVENUE','CREDIT',4100,'4000'],
                ['4200','Pendapatan Lain-lain','REVENUE','CREDIT',4200,'4000'],
                ['5000','BEBAN','EXPENSE','DEBIT',5000,null],
                ['5100','Beban Operasional','EXPENSE','DEBIT',5100,'5000'],
                ['5200','Beban Administrasi','EXPENSE','DEBIT',5200,'5000'],
            ];

            foreach ($headers as [$code,$name,$type,$normal,$sort,$parentCode]) {
                Account::updateOrCreate(
                    ['code' => $code],
                    ['name'=>$name,'type'=>$type,'normal_balance'=>$normal,'sort_order'=>$sort,'description'=>null,'is_cash_bank'=>false,'is_postable'=>false,'is_active'=>true]
                );
            }

            foreach ($headers as [$code,$name,$type,$normal,$sort,$parentCode]) {
                if (!$parentCode) continue;
                $parent = Account::where('code',$parentCode)->first();
                $child = Account::where('code',$code)->first();
                if ($parent && $child) $child->update(['parent_id'=>$parent->id]);
            }

            $map = ['1101'=>'1100','1102'=>'1100','1201'=>'1200','2101'=>'2100','2102'=>'2100','2103'=>'2100','4101'=>'4100','4102'=>'4100'];
            foreach ($map as $childCode=>$parentCode) {
                $child = Account::where('code',$childCode)->first();
                $parent = Account::where('code',$parentCode)->first();
                if ($child && $parent) {
                    $child->update([
                        'parent_id'=>$parent->id,
                        'is_postable'=>true,
                        'normal_balance'=>in_array($child->type,['ASSET','EXPENSE'],true) ? 'DEBIT' : 'CREDIT',
                    ]);
                }
            }

            Account::whereIn('type',['ASSET','EXPENSE'])->where('is_postable',true)->update(['normal_balance'=>'DEBIT']);
            Account::whereIn('type',['LIABILITY','EQUITY','REVENUE'])->where('is_postable',true)->update(['normal_balance'=>'CREDIT']);
        });
    }
}
