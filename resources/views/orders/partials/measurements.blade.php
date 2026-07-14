@php
    $measurmentProfileId = getMeasurmentCustomerByOrderId($order->increment_id);
    $myMeasurment = getMeasurmentByOrderId($measurmentProfileId[$product->product_item_id] ?? null);

    $productType = ['Saree','saree', 'Salwar Kameez', 'Lehenga Choli','Dupatta','Kurti','woman','Woman'];
    $imageUrlPath = "https://assets2.andaazfashion.com/";

    $fieldsToAppendInches = [
        'Chest Size', 'Shoulder', 'Around Belly Button', 'Waist', 'Around Hips',
        'Around Arm', 'Sleeve Length', 'Top Length', 'Bottom Length', 'Arm Hole',
        'Around Neck', 'Mid Thigh Length', 'Crotch Length','Under Bust','HPS Under Bust'
    ];
    
@endphp

@if($myMeasurment)

    <tbody>
        @foreach ($myMeasurment as $myMeasure)
            @php
                $bustSize = ''; 
                $measurementId = $myMeasure->id;
            @endphp

            @if ($myMeasure->type && $myMeasure->mtype && $myMeasure->unit)
                <tr>
                    <td class="printfonttd">Product Type 
                        <input type='hidden' value="{{ $myMeasure->id }}" class="easurementid" name="measurementid_{{ $myMeasure->id }}" />
                    </td>
                    <td colspan="2" class="printfonttd">{{ $myMeasure->type }} ({{ $myMeasure->mtype }})</td>
                </tr>
            @endif

            @php
                $prestich ='';
                if($myMeasure->prestich !=='USD '){
                    $prestich = $myMeasure->prestich;
                } 
                $measurements = [
                    ['label' => in_array($myMeasure->type, $productType) ? 'Bust Size' : 'Chest Size', 'value' => $myMeasure->bust],
                    ['label' => 'Under Bust', 'value' => $myMeasure->under_bust],
                    ['label' => 'Shoulder', 'value' => $myMeasure->shoulder],
                    ['label' => 'HPS Under Bust', 'value' => $myMeasure->hps_under_bust],
                    ['label' => 'Around Belly Button', 'value' => $myMeasure->arround_belly_button],
                    ['label' => 'Waist', 'value' => $myMeasure->waist],
                    ['label' => 'Around Hips', 'value' => $myMeasure->hips],
                    ['label' => 'Around Arm', 'value' => $myMeasure->arround_arm],
                    ['label' => 'Sleeve Length', 'value' => $myMeasure->sleeve_length],
                    ['label' => 'Top Length', 'value' => $myMeasure->top_length],
                    ['label' => 'Bottom Length', 'value' => $myMeasure->bottom_length],
                    ['label' => 'Modest Requirement', 'value' => $myMeasure->modest_requirement],
                    ['label' => 'Special Message', 'value' => $myMeasure->special_msg],
                    ['label' => 'Waist Type', 'value' => $myMeasure->waist_type],
                    ['label' => 'Height', 'value' => $myMeasure->height],
                    ['label' => 'Heels', 'value' => $myMeasure->heels],
                    ['label' => 'Arm Hole', 'value' => $myMeasure->arm_hole],
                    ['label' => 'Dress/Kameez Length', 'value' => $myMeasure->dresskameez_length],
                    ['label' => 'Adornment', 'value' => $myMeasure->adornment],
                    ['label' => 'Blouse Length', 'value' => $myMeasure->blouse_length],
                    ['label' => 'Blouse Pad', 'value' => $myMeasure->blousepad],
                    ['label' => 'Prestich', 'value' => $prestich],
                    ['label' => 'Around Neck', 'value' => $myMeasure->around_neck],
                    ['label' => 'Mid Thigh Length', 'value' => $myMeasure->thigh_length],
                    ['label' => 'Crotch Length', 'value' => $myMeasure->crotch_length],
                    ['label' => 'Mori Length', 'value' => $myMeasure->mori_length],
                    ['label' => 'Calf Length', 'value' => $myMeasure->calf_length],
                    ['label' => 'Wrist Size', 'value' => $myMeasure->wrist_size],
                ];
            @endphp

            @foreach($measurements as $measure)
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
                        <td class="printfonttd">{{ $val }}
                            <!-- @if($measure['label'] != 'Modest Requirement' && $measure['label'] != 'Special Message')
                                {{ $myMeasure->unit }}
                            @endif -->
                        </td>
                        @if ($image)
                            <td><img src="{{ asset($image) }}" style="width: 120px;" /></td>
                        @endif
                    </tr>
                @endif
            @endforeach

            @foreach([
                'customer_note' => 'Customer Note',
                'front_neck_style' => 'Front Neck Style',
                'front_neck_depth' => 'Front Neck Depth',
                'back_neck_style' => 'Back Neck Style',
                'back_neck_depth' => 'Back Neck Depth',
                'closing_side' => 'Closing Side',
                'closing_with' => 'Closing With', 
                'upload_image' => 'Upload Image',
                'STATUS' => 'Status',
                'profile_name' => 'Profile Name',
            ] as $field => $label)
                @if (!empty($myMeasure->$field))
                    <tr>
                        <td class="printfonttd">{{ $label }}</td>
                        <td colspan="2" class="printfonttd">{{ $myMeasure->$field }} 
                            <!-- @if (in_array($label, $fieldsToAppendInches))
                                {{ $myMeasure->unit}}
                            @endif -->
                        </td>
                    </tr>
                @endif
            @endforeach

            @if ($myMeasure->frontimg)
                <tr>
                    <td>Front Image</td>
                    <td><img src="{{ $imageUrlPath . $myMeasure->frontimg }}" max-height="150" width="100" /></td>
                </tr>
            @endif

            @if ($myMeasure->sideimg)
                <tr>
                    <td>Side Image</td>
                    <td><img src="{{ $imageUrlPath . $myMeasure->sideimg }}" max-height="150" width="100" /></td>
                </tr>
            @endif
        @endforeach
    </tbody> 

@endif
