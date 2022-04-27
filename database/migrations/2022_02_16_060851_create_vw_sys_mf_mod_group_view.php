<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVwSysMfModGroupView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $view = "CREATE VIEW vw_sys_mf_mod_group AS
        with recursive mod_group (mod_group_id,mod_group_name,ref_mod_id,ref_mod_name,level,seq) 
        as 
        (select	a.id AS mod_group_id,
                a.name AS mod_group_name,
                0 AS ref_mod_id,
                a.name AS ref_mod_name,
                1 AS level,
                cast(a.seq as char charset utf8mb4) AS seq 
        from	laravel_demo.tb_sys_mf_mod_group a 
        where 	(a.ref_mod_id is null) 
        
        union 
        all 	
        
        select	a.id AS mod_group_id,
                a.name AS mod_group_name,
                a.ref_mod_id AS ref_mod_id,
                b.mod_group_name AS ref_mod_name,
                (b.level + 1) AS level,
                concat(cast(b.seq as char charset utf8mb4),
                cast(a.seq as char charset utf8mb4)) AS seq 
        from	(laravel_demo.tb_sys_mf_mod_group a 
                join mod_group b on((a.ref_mod_id = b.mod_group_id)))) 
        
        select	mod_group.mod_group_id AS mod_group_id,
                mod_group.mod_group_name AS mod_group_name
                ,mod_group.ref_mod_id AS ref_mod_id,
                mod_group.ref_mod_name AS ref_mod_name,
                mod_group.level AS level,
                mod_group.seq AS seq
        from	mod_group 
        order by mod_group.seq";
        \DB::statement($view);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $view = "DROP VIEW IF EXISTS vw_sys_mf_mod_group";
        \DB::statement($view);
    }

}
