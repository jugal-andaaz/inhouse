@php     
    $standardMeasur = getStandardMeasurementByItemId($product_id ?? null);
@endphp

@if ($standardMeasur)
    <tr>
        <td scope="col" class="printfonttd">Measurement Type</td>
        <td colspan="2" class="printfonttd">{{ $standardMeasur->measurement_type }} ( {{ $standardMeasur->service ?? '' }} )</td>
    </tr> 
    @php
        if($standardMeasur->Bust_Size=='|'){$bustSize='';}else{$bustSize = $standardMeasur->Bust_Size;}
        if($standardMeasur->Sleeve_Length=='|'){$sleeveLength='';}else{$sleeveLength = $standardMeasur->Sleeve_Length;}
        if($standardMeasur->Dress_Top_Length=='|'){$dressTopLength='';}else{$dressTopLength = $standardMeasur->Dress_Top_Length;}
        if($standardMeasur->Body_Height=='|'){$bodyHeight='';}else{$bodyHeight = $standardMeasur->Body_Height;}
        if($standardMeasur->under_bust=='|'){$under_bust='';}else{$under_bust = $standardMeasur->under_bust;}
        if($standardMeasur->shoulder=='|'){$shoulder='';}else{$shoulder = $standardMeasur->shoulder;}
        if($standardMeasur->hps_under_bust=='|'){$hps_under_bust='';}else{$hps_under_bust = $standardMeasur->hps_under_bust;}
        if($standardMeasur->dresskameez_length=='|'){$dresskameez_length='';}else{$dresskameez_length = $standardMeasur->dresskameez_length;}
        if($standardMeasur->arround_belly_button=='|'){$arround_belly_button='';}else{$arround_belly_button = $standardMeasur->arround_belly_button;}
        if($standardMeasur->arround_arm=='|'){$arround_arm='';}else{$arround_arm = $standardMeasur->arround_arm;}
        if($standardMeasur->arm_hole=='|'){$arm_hole='';}else{$arm_hole = $standardMeasur->arm_hole;}
        if($standardMeasur->bottom_length=='|'){$bottom_length='';}else{$bottom_length = $standardMeasur->bottom_length;}
        if($standardMeasur->hips=='|'){$hips='';}else{$hips = $standardMeasur->hips;}
        if($standardMeasur->modest_requirement=='|'){$modest_requirement='';}else{$modest_requirement = $standardMeasur->modest_requirement;}
        if($standardMeasur->special_msg=='|'){$special_msg='';}else{$special_msg = $standardMeasur->special_msg;}
        if($standardMeasur->waist=='|'){$waist='';}else{$waist = $standardMeasur->waist;}
        if($standardMeasur->waist_type=='|'){$waist_type='';}else{$waist_type = $standardMeasur->waist_type;}
        if($standardMeasur->heels=='|'){$heels='';}else{$heels = $standardMeasur->heels;}
        if($standardMeasur->around_neck=='|'){$around_neck='';}else{$around_neck = $standardMeasur->around_neck;}
        if($standardMeasur->adornment=='|'){$adornment='';}else{$adornment = $standardMeasur->adornment;}
        if($standardMeasur->blouse_length=='|'){$blouse_length='';}else{$blouse_length = $standardMeasur->blouse_length;}
        if($standardMeasur->blousepad=='|'){$blousepad='';}else{$blousepad = $standardMeasur->blousepad;}
        if($standardMeasur->prestich=='|'){$prestich='';}else{$prestich = $standardMeasur->prestich;}
        if($standardMeasur->thigh_length=='|'){$thigh_length='';}else{$thigh_length = $standardMeasur->thigh_length;}
        if($standardMeasur->crotch_length=='|'){$crotch_length='';}else{$crotch_length = $standardMeasur->crotch_length;}
        if($standardMeasur->mori_length=='|'){$mori_length='';}else{$mori_length = $standardMeasur->mori_length;}
        if($standardMeasur->calf_length=='|'){$calf_length='';}else{$calf_length = $standardMeasur->calf_length;}
        if($standardMeasur->wrist_size=='|'){$wrist_size='';}else{$wrist_size = $standardMeasur->wrist_size;}

        if($standardMeasur->womens_garment_bust_size=='|'){$womens_garment_bust_size='';}else{$womens_garment_bust_size = $standardMeasur->womens_garment_bust_size;}

        if($standardMeasur->womens_body_height=='|'){$womens_body_height='';}else{$womens_body_height = $standardMeasur->womens_body_height;}
        if($standardMeasur->mens_body_chest_size=='|'){$mens_body_chest_size='';}else{$mens_body_chest_size = $standardMeasur->mens_body_chest_size;}
        if($standardMeasur->mens_body_height=='|'){$mens_body_height='';}else{$mens_body_height = $standardMeasur->mens_body_height;}



        $typeValue='';
        
        if($standardMeasur->type=='men'){
            $typeValue = "Chest Size";
        }else if($standardMeasur->type=='woman'){
            $typeValue = "Bust Size";
        }else{
            $typeValue = "Andaaz Size";
        }
        if($standardMeasur->service!=='Custom Tailored'){
            $typeValue = "Andaaz Size";
        }
        if($standardMeasur->type=='men'){
            $typeValue = "Chest Size";
        }

        $standardMeasurs = [
            ['label' => $typeValue, 'value' => $bustSize],
            ['label' => 'Sleeve Length', 'value' => $sleeveLength],
            ['label' => 'Dress/Top Length', 'value' => $dressTopLength],
            ['label' => 'Body Height', 'value' => $bodyHeight],
            ['label' => 'Under Bust', 'value' => $under_bust],
            ['label' => 'HPS Under Bust', 'value' => $hps_under_bust],
            ['label' => 'Shoulder', 'value' => $shoulder],
            ['label' => 'Around Belly Button', 'value' => $arround_belly_button],
            ['label' => 'Arround Arm', 'value' => $arround_arm],
            ['label' => 'Arm Hole', 'value' => $arm_hole],
            ['label' => 'Dress/Kameez Length', 'value' => $dresskameez_length],
            ['label' => 'Bottom Length', 'value' => $bottom_length],
            ['label' => 'Hips', 'value' => $hips],
            ['label' => 'Modest Requirement', 'value' => $modest_requirement],
            ['label' => 'Special Message', 'value' => $special_msg],
            ['label' => 'Waist', 'value' => $waist],
            ['label' => 'Waist Type', 'value' => $waist_type],
            ['label' => 'Heels', 'value' => $heels],
            ['label' => 'Around Neck', 'value' => $around_neck],
            ['label' => 'Adornment', 'value' => $adornment],
            ['label' => 'Blouse Length', 'value' => $blouse_length],
            ['label' => 'Blouse Pad', 'value' => $blousepad],
            ['label' => 'Prestich', 'value' => $prestich],
            ['label' => 'Mid Thigh Length', 'value' => $thigh_length],
            ['label' => 'Crotch Length', 'value' => $crotch_length],
            ['label' => 'Mori Length', 'value' => $mori_length],
            ['label' => 'Calf Length', 'value' => $calf_length],
            ['label' => 'Wrist Size', 'value' => $wrist_size],

            ['label' => 'Womens Garment Bust Size', 'value' => $womens_garment_bust_size],
            ['label' => 'Womens Body Height', 'value' => $womens_body_height],
            ['label' => 'Mens Body Chest Size', 'value' => $mens_body_chest_size],
            ['label' => 'Mens Body Height', 'value' => $mens_body_height],
        ]; 
    @endphp

    @foreach($standardMeasurs as $measure)
        @if (!empty($measure['value']))
            @php
                $image = '';
                if (strpos($measure['value'], '|') !== false) {
                    list($val, $image) = explode('|', $measure['value']);
                } else {
                    $val = $measure['value'];
                }
            @endphp
            <tr>
                <td class="printfonttd">{{ $measure['label'] }}</td>
                <td class="printfonttd">{{ $val }}</td>
                @if ($image)
                    <td><img src="{{ asset($image) }}" style="width: 120px;" /></td>
                @endif
            </tr>
        @endif
    @endforeach
@endif