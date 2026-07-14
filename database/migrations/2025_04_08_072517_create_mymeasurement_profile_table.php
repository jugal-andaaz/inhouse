<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMymeasurementProfileTable extends Migration
{
    public function up()
    {
        Schema::create('mymeasurement_profile', function (Blueprint $table) {
            $table->id();
            $table->string('height', 25)->nullable();
            $table->string('bust', 250)->nullable();
            $table->string('under_bust', 50)->nullable();
            $table->string('shoulder', 50)->nullable();
            $table->string('top_length', 50)->nullable();
            $table->string('body_bust_size', 250)->nullable();
            $table->text('customer_note')->nullable();
            $table->string('arm_hole', 50)->nullable();
            $table->string('front_neck_style', 50)->nullable();
            $table->string('front_neck_depth', 50);
            $table->string('back_neck_style', 50)->nullable();
            $table->string('back_neck_depth', 50)->nullable();
            $table->string('sleeve_length', 50)->nullable();
            $table->string('blouse_length', 50)->nullable();
            $table->string('closing_side', 50)->nullable();
            $table->string('closing_with', 50)->nullable();
            $table->string('heels', 50)->nullable();
            $table->string('adornment', 50)->nullable();
            $table->string('hips', 50)->nullable();
            $table->string('bottom_length', 50)->nullable();
            $table->string('dresskameez_length', 50)->nullable();
            $table->string('waist', 50)->nullable();
            $table->text('special_msg')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('profile_name', 100)->nullable();
            $table->string('unit', 100)->nullable();
            $table->string('mtype', 100)->nullable();
            $table->string('customer_id', 255)->nullable();
            $table->timestamp('created_date')->useCurrent()->nullable();
            $table->timestamp('updated_date')->nullable()->default(DB::raw("'2000-01-01 01:01:01'"));
            $table->string('blousepad', 100)->nullable();
            $table->string('frontimg', 250)->nullable();
            $table->string('sideimg', 250)->nullable();
            $table->string('prestich', 250)->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->string('arround_belly_button', 50)->nullable();
            $table->string('arround_arm', 50)->nullable();
            $table->string('modest_requirement', 100)->nullable();
            $table->string('waist_type', 100)->nullable();
            $table->string('upload_image', 250)->nullable();
            $table->boolean('STATUS')->nullable()->default(0);
            $table->string('around_neck', 20)->nullable();
            $table->string('thigh_length', 20)->nullable();
            $table->string('crotch_length', 20)->nullable();
            $table->string('mori_length', 20)->nullable();
            $table->string('calf_length', 20)->nullable();
            $table->string('wrist_size', 20)->nullable();
            $table->string('hps_under_bust', 250)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mymeasurement_profile');
    }
}
