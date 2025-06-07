<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('idap_disc_tran', function (Blueprint $table) {
            $table->string('fin_year', 7);
            $table->string('inst_id', 10);
            $table->char('div_code', 5);
            $table->string('item_code', 10);
            $table->float('disc_pct')->nullable();
            $table->float('disc_rate')->nullable();
            $table->float('idap_prnt_q_id');
            $table->string('metis_q_id', 20)->nullable();
            $table->char('q_type', 5)->nullable();
            $table->float('rev_no');
            $table->tinyInteger('upd_flag')->default(0);
            $table->date('q_date')->nullable();
            $table->date('metis_upd_date')->nullable();
            $table->date('q_strt_date')->nullable();
            $table->date('q_end_date')->nullable();
            $table->char('stockist_code', 10);
            $table->string('discount_mode', 2);
            $table->float('netdiscountperc_stockist')->nullable();
            $table->char('is_deleted', 1)->nullable();

            // Define a shorter primary key name explicitly
            $table->primary(
                ['fin_year', 'inst_id', 'div_code', 'item_code', 'rev_no', 'stockist_code', 'discount_mode', 'idap_prnt_q_id'], 
                'idap_disc_pk'
            ); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('idap_disc_tran');
    }

};