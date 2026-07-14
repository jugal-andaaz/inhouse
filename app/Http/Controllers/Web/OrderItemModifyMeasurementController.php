<?php
namespace Vanguard\Http\Controllers\Web;

use Illuminate\Http\Request;
use Vanguard\Models\MeasurementProfile; 
use Vanguard\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; 
use \Vanguard\Helpers\GoogleSheetHelper;
use Carbon\Carbon;

class OrderItemModifyMeasurementController extends Controller
{
    public function index(Request $request)
    {
        $orderid = $request->get('ordid');
        $pid = $request->get('pid');
        $collections = [];

        $measurmentProfileId = getMeasurmentCustomerByOrderId($orderid);
        $myMeasurments = getMeasurmentByOrderId($measurmentProfileId[$pid] ?? null);

        foreach($myMeasurments as $myMeasurment){
            $profile = MeasurementProfile::where('id', $myMeasurment->id)->first();
            if ($profile) {
                $collections[] = $profile;
            }
        }

        return view('orderitempopup.order_item_modifymeasurement', [
            'collections' => $collections,
            'orderid' => $orderid
        ]);
    }

    public function updateMeasurementDetails(Request $request)
    {
        $currentDate =now();
        /*$currentDateFormated = date('d-m-Y H:m:i', strtotime($currentDate));*/
        
        $utc = Carbon::now('UTC');
        $ist = $utc->setTimezone('Asia/Kolkata');
        $currentDateFormated = $ist->format('Y-m-d H:i:s');

        $productid = $request->input('productid');
        $productsku = $request->input('productsku');
        $tailsrvcid = $request->input('tailsrvcid');
        $selectedAddons = $request->input('addons', []);
        $changedMeasurementV=$NewFields = '';
        $productsize = $request->input('productsizeattr');
        $changedFields = '';
        $oldFields = '';
        // Get existing record
        if($tailsrvcid=='ready'){
            $existingData = DB::table('standard_measurement')->where('item_id', $productid)->first();
            if ($existingData || $existingData=='') {
            /*if ($existingData) {
                $changedFields = '';$oldFields = '';*/

                $formData = [
                    'Bust_Size' => $request->Bust_Sizechk,
                    'Sleeve_Length' => $request->Sleeve_Lengthchk,
                    'Dress_Top_Length' => $request->Dress_Top_Lengthchk,
                    'Body_Height' => $request->Body_Heightchk,
                    'under_bust' => $request->under_bustchk,
                    'hps_under_bust' => $request->hps_under_bustchk,
                    'shoulder' => $request->shoulderchk,
                    'arround_belly_button' => $request->arround_belly_buttonchk,
                    'waist' => $request->waistchk,
                    'hips' => $request->hipschk,
                    'arround_arm' => $request->arround_armchk,
                    'bottom_length' => $request->bottom_lengthchk,
                    'modest_requirement' => $request->modest_requirementchk,
                    'special_msg' => $request->special_msgchk,
                    'waist_type' => $request->waist_typechk,
                    'heels' => $request->heelschk,
                    'arm_hole' => $request->arm_holechk,
                    'dresskameez_length' => $request->dresskameez_lengthchk,
                    'adornment' => $request->adornmentchk,
                    'blouse_length' => $request->blouse_lengthchk,
                    'measurement_type' => $request->measurement_typechk,
                    'blousepad' => $request->blousepadchk,
                    'prestich' => $request->prestichchk,
                    'around_neck' => $request->around_neckchk,
                    'thigh_length' => $request->thigh_lengthchk,
                    'crotch_length' => $request->crotch_lengthchk,
                    'mori_length' => $request->mori_lengthchk,
                    'calf_length' => $request->calf_lengthchk,
                    'wrist_size' => $request->wrist_sizechk,
                    'womens_garment_bust_size' => $request->womens_garment_bust_sizechk,
                    'womens_body_height' => $request->womens_body_heightchk,
                    'mens_body_chest_size' => $request->mens_body_chest_sizechk,
                    'mens_body_height' => $request->mens_body_heightchk,
                ];
                
                foreach ($formData as $field => $newValue) {
                    $newValue = trim((string) $newValue);
                    $existingValue = isset($existingData->$field) ? trim((string) $existingData->$field) : '';

                    $existingNumeric = $existingValue !== ''
                        ? trim(explode('|', $existingValue)[0])
                        : '';

                    if (
                        ($existingNumeric === '' && $newValue !== '') ||
                        ($existingNumeric !== $newValue)
                    ) {

                        $displayField = ($field === 'Bust_Size') ? 'Andaaz_Size' : $field;

                        $changedFields .= $displayField . " - " . $newValue . " | ";
                        $oldFields .= "<li>{$displayField} - " . ($existingNumeric ?: 'N/A') . "</li>";
                        $NewFields .= "<li>{$displayField} - {$newValue}</li>";
                    }
                } 
            } 
            $bustSizeImageName=$this->getImageUploadSaved($request,'Bust_Sizeimage','Bust_Sizechkimg');
            $sleeveLengthImageName=$this->getImageUploadSaved($request,'Sleeve_Lengthimage','Sleeve_Lengthchkimg');
            $dresstopLengthImageName=$this->getImageUploadSaved($request,'Dress_Top_Lengthimage','Dress_Top_Lengthchkimg');
            $bodyHeightImageName=$this->getImageUploadSaved($request,'Body_Heightimage','Body_Heightchkimg');            
            $under_bustImageName=$this->getImageUploadSaved($request,'under_bustimage','under_bustchkimg');
            $hps_under_bustImageName=$this->getImageUploadSaved($request,'hps_under_bustimage','hps_under_bustchkimg');
            $shoulderImageName=$this->getImageUploadSaved($request,'shoulderimage','shoulderchkimg');
            $arroundBellyButtonImageName=$this->getImageUploadSaved($request,'arround_belly_buttonimage','arround_belly_buttonchkimg');
            $waistImageName=$this->getImageUploadSaved($request,'waistimage','waistchkimg');
            $hipsImageName=$this->getImageUploadSaved($request,'hipsimage','hipschkimg');
            $arroundArmImageName=$this->getImageUploadSaved($request,'arround_armimage','arround_armchkimg');
            
            $arroundNeckImageName=$this->getImageUploadSaved($request,'around_neckimage','around_neckchkimg');

            $bottomLengthImageName=$this->getImageUploadSaved($request,'bottom_lengthimage','bottom_lengthchkimg');
            $modestRequirementImageName=$this->getImageUploadSaved($request,'modest_requirementimage','modest_requirementchkimg');
            $specialMsgImageName=$this->getImageUploadSaved($request,'special_msgimage','special_msgchkimg');            
            $waistTypeImageName=$this->getImageUploadSaved($request,'waist_typeimage','waist_typechkimg');
            $heelsImageName=$this->getImageUploadSaved($request,'heelsimage','heelschkimg');
            $armHoleImageName=$this->getImageUploadSaved($request,'arm_holeimage','arm_holechkimg');
            $dresskameezLengthImageName=$this->getImageUploadSaved($request,'dresskameez_lengthimage','dresskameez_lengthchkimg');
            $adornmentImageName=$this->getImageUploadSaved($request,'adornmentimage','adornmentchkimg');
            $blouseLengthImageName=$this->getImageUploadSaved($request,'blouse_lengthimage','blouse_lengthchkimg');
            $blousePadImageName=$this->getImageUploadSaved($request,'blousepadimage','blousepadchkimg');
            $prestichImageName=$this->getImageUploadSaved($request,'prestichimage','prestichchkimg');
            $aroundNeckImageName=$this->getImageUploadSaved($request,'around_neckimage','around_neckchkimg');
            $thighLengthImageName=$this->getImageUploadSaved($request,'thigh_lengthimage','thigh_lengthchkimg');
            $crotchLengthImageName=$this->getImageUploadSaved($request,'crotch_lengthimage','crotch_lengthchkimg');
            $moriLengthImageName=$this->getImageUploadSaved($request,'mori_lengthimage','mori_lengthchkimg');
            $calfLengthImageName=$this->getImageUploadSaved($request,'calf_lengthimage','calf_lengthchkimg');
            $wristSizeImageName=$this->getImageUploadSaved($request,'wrist_sizeimage','wrist_sizechkimg'); 

            $womenBustSizeImageName=$this->getImageUploadSaved($request,'womens_garment_bust_sizeimage','womens_garment_bust_sizechkimg'); 
            $womenBodyHeightImageName=$this->getImageUploadSaved($request,'womens_body_heightimage','womens_body_heightchkimg'); 
            $menBodyChestSizeImageName=$this->getImageUploadSaved($request,'mens_body_chest_sizeimage','mens_body_chest_sizechkimg'); 
            $menBodyHeightImageName=$this->getImageUploadSaved($request,'mens_body_heightimage','mens_body_heightchkimg'); 

            DB::table('standard_measurement')->updateOrInsert(
                ['item_id' => $productid],
                [
                    'item_id' => $productid,
                    'Bust_Size' => $request->Bust_Sizechk.$bustSizeImageName,
                    'Sleeve_Length' => $request->Sleeve_Lengthchk.$sleeveLengthImageName,
                    'Dress_Top_Length' => $request->Dress_Top_Lengthchk.$dresstopLengthImageName,
                    'Body_Height' => $request->Body_Heightchk.$bodyHeightImageName,
                    'under_bust' => $request->under_bustchk.$under_bustImageName,
                    'hps_under_bust' => $request->hps_under_bustchk.$hps_under_bustImageName,
                    'shoulder' => $request->shoulderchk.$shoulderImageName, 
                    'arround_belly_button' => $request->arround_belly_buttonchk.$arroundBellyButtonImageName,
                    'arround_arm' => $request->arround_armchk.$arroundArmImageName,
                    'around_neck' => $request->around_neckchk.$aroundNeckImageName,
                    'dresskameez_length' => $request->dresskameez_lengthchk.$dresskameezLengthImageName,
                    'bottom_length' => $request->bottom_lengthchk.$bottomLengthImageName,
                    'hips' => $request->hipschk.$hipsImageName,
                    'modest_requirement' => $request->modest_requirementchk.$modestRequirementImageName,
                    'special_msg' => $request->special_msgchk.$specialMsgImageName,
                    'waist' => $request->waistchk.$waistImageName,
                    'waist_type' => $request->waist_typechk.$waistTypeImageName,
                    'heels' => $request->heelschk.$heelsImageName,
                    'arm_hole' => $request->arm_holechk.$armHoleImageName,
                    'adornment' => $request->adornmentchk.$adornmentImageName, 
                    'blouse_length' => $request->blouse_lengthchk.$blouseLengthImageName, 
                    'blousepad' => $request->blousepadchk.$blousePadImageName, 
                    'prestich' => $request->prestichchk.$prestichImageName, 
                    'thigh_length' => $request->thigh_lengthchk.$thighLengthImageName, 
                    'crotch_length' => $request->crotch_lengthchk.$crotchLengthImageName, 
                    'mori_length' => $request->mori_lengthchk.$moriLengthImageName, 
                    'calf_length' => $request->calf_lengthchk.$calfLengthImageName, 
                    'wrist_size' => $request->wrist_sizechk.$wristSizeImageName,
                    'womens_garment_bust_size' => $request->womens_garment_bust_sizechk.$womenBustSizeImageName,
                    'womens_body_height' => $request->womens_body_heightchk.$womenBodyHeightImageName,
                    'mens_body_chest_size' => $request->mens_body_chest_sizechk.$menBodyChestSizeImageName,
                    'mens_body_height' => $request->mens_body_heightchk.$menBodyHeightImageName,
                    'Created_By' => $request->loginuser ?? 'system',
                    'Created_Date' => now(),
                    'measurement_type' => $request->measurement_type,
                    'type' => $request->typechk,
                    'service' => $request->sizetype,
                ]
            ); 
            
            if(isset($oldFields) || $NewFields!==''){
                DB::table('andaaz_order_log')->insert([
                    'order_id' => $request->orderid,
                    'sku' => $productsku,
                    'item_id' => $productid,
                    'product_item_id' => $request->productitemid,
                    'new_value' => $NewFields,
                    'old_value' => $oldFields,
                    'column_name' => 'Measurement - Ready Size', 
                    'pending_reason' => '1',
                    'updated_by' => $request->loginuser ?? 'system',
                    'updated_date' => now(),
                ]);
            }
            $sheetData = GoogleSheetHelper::sendIndicatorSheetDataAndMatch($request->productitemid);
        }
        
        if(isset($request->mmpid)){
            $oldFields1 = [];$updatedFields = [];
            $validated = $request->validate(['mmpid' => 'required|integer']);
            $measurement = MeasurementProfile::find($request->mmpid);
            if ($measurement) {
                $fields = [
                    'bustchk' => 'bust',
                    'under_bustchk' => 'under_bust',
                    'hps_under_bustchk' => 'hps_under_bust',
                    'shoulderchk' => 'shoulder',
                    'arround_belly_buttonchk' => 'arround_belly_button',
                    'waistchk' => 'waist',
                    'hipschk' => 'hips',
                    'arround_armchk' => 'arround_arm',
                    'sleeve_lengthchk' => 'sleeve_length',
                    'top_lengthchk' => 'top_length',
                    'bottom_lengthchk' => 'bottom_length',
                    'modest_requirementchk' => 'modest_requirement',
                    'special_msgchk' => 'special_msg',
                    'waist_typechk' => 'waist_type',
                    'heightchk' => 'height',
                    'heelschk' => 'heels',
                    'arm_holechk' => 'arm_hole',
                    'dresskameez_lengthchk' => 'dresskameez_length',
                    'adornmentchk' => 'adornment',
                    'blouse_lengthchk' => 'blouse_length',
                    'blousepadchk' => 'blousepad',
                    'prestichchk' => 'prestich',
                    'around_neckchk' => 'around_neck',
                    'thigh_lengthchk' => 'thigh_length',
                    'crotch_lengthchk' => 'crotch_length',
                    'mori_lengthchk' => 'mori_length',
                    'calf_lengthchk' => 'calf_length',
                    'wrist_sizechk' => 'wrist_size',
                ];

                $bustImageName=$this->getImageUploadSaved($request,'bustimage','bustchkimg');
                $under_bustImageName=$this->getImageUploadSaved($request,'under_bustimage','under_bustchkimg');
                $hps_under_bustImageName=$this->getImageUploadSaved($request,'hps_under_bustimage','hps_under_bustchkimg');
                $shoulderImageName=$this->getImageUploadSaved($request,'shoulderimage','shoulderchkimg');
                $arroundBellyButtonImageName=$this->getImageUploadSaved($request,'arround_belly_buttonimage','arround_belly_buttonchkimg');
                $waistImageName=$this->getImageUploadSaved($request,'waistimage','waistchkimg');
                $hipsImageName=$this->getImageUploadSaved($request,'hipsimage','hipschkimg');
                $waistTypeImageName=$this->getImageUploadSaved($request,'waist_typeimage','waist_typechkimg');
                $arroundArmImageName=$this->getImageUploadSaved($request,'arround_armimage','arround_armchkimg');
                $sleeveLengthImageName=$this->getImageUploadSaved($request,'sleeve_lengthimage','sleeve_lengthchkimg');
                $topLengthImageName=$this->getImageUploadSaved($request,'top_lengthimage','top_lengthchkimg');
                $bottomLengthImageName=$this->getImageUploadSaved($request,'bottom_lengthimage','bottom_lengthchkimg');
                $modestRequirementImageName=$this->getImageUploadSaved($request,'modest_requirementimage','modest_requirementchkimg');
                $specialMsgImageName=$this->getImageUploadSaved($request,'special_msgimage','special_msgchkimg');
                $waistTypeImageName=$this->getImageUploadSaved($request,'waist_typeimage','waist_typechkimg');
                $heightImageName=$this->getImageUploadSaved($request,'heightimage','heightchkimg');
                $heelsImageName=$this->getImageUploadSaved($request,'heelsimage','heelschkimg');
                $armHoleImageName=$this->getImageUploadSaved($request,'arm_holeimage','arm_holechkimg');
                $dresskameezLengthImageName=$this->getImageUploadSaved($request,'dresskameez_lengthimage','dresskameez_lengthchkimg');
                $adornmentImageName=$this->getImageUploadSaved($request,'adornmentimage','adornmentchkimg');
                $blouseLengthImageName=$this->getImageUploadSaved($request,'blouse_lengthimage','blouse_lengthchkimg');
                $blousePadImageName=$this->getImageUploadSaved($request,'blousepadimage','blousepadchkimg');
                $prestichImageName=$this->getImageUploadSaved($request,'prestichimage','prestichchkimg');
                $aroundNeckImageName=$this->getImageUploadSaved($request,'around_neckimage','around_neckchkimg');
                $thighLengthImageName=$this->getImageUploadSaved($request,'thigh_lengthimage','thigh_lengthchkimg');
                $crotchLengthImageName=$this->getImageUploadSaved($request,'crotch_lengthimage','crotch_lengthchkimg');
                $moriLengthImageName=$this->getImageUploadSaved($request,'mori_lengthimage','mori_lengthchkimg');
                $calfLengthImageName=$this->getImageUploadSaved($request,'calf_lengthimage','calf_lengthchkimg');
                $wristSizeImageName=$this->getImageUploadSaved($request,'wrist_sizeimage','wrist_sizechkimg');
                $updatedFields = [];
                $imageNameMap = [
                    'bust' => $bustImageName,
                    'under_bust' => $under_bustImageName,
                    'hps_under_bust' => $hps_under_bustImageName,
                    'shoulder' => $shoulderImageName,
                    'arround_belly_button' => $arroundBellyButtonImageName,
                    'waist' => $waistImageName,
                    'hips' => $hipsImageName,
                    'arround_arm' => $arroundArmImageName,
                    'sleeve_length' => $sleeveLengthImageName,
                    'top_length' => $topLengthImageName,
                    'bottom_length' => $bottomLengthImageName,
                    'modest_requirement' => $modestRequirementImageName,
                    'special_msg' => $specialMsgImageName,
                    'waist_type' => $waistTypeImageName,
                    'height' => $heightImageName,
                    'heels' => $heelsImageName,
                    'arm_hole' => $armHoleImageName,
                    'dresskameez_length' => $dresskameezLengthImageName,
                    'adornment' => $adornmentImageName,
                    'blouse_length' => $blouseLengthImageName,
                    'blousepad' => $blousePadImageName,
                    'prestich' => $prestichImageName,
                    'around_neck' => $aroundNeckImageName,
                    'thigh_length' => $thighLengthImageName,
                    'crotch_length' => $crotchLengthImageName,
                    'mori_length' => $moriLengthImageName,
                    'calf_length' => $calfLengthImageName,
                    'wrist_size' => $wristSizeImageName,
                ];

                foreach ($fields as $input => $column) {
                    if (!$request->filled($input)) continue;

                    $newValue = $request->$input;
                    $trackInput = $this->trackChangedMeasurement($measurement->$column, $newValue, $column);
 
                    if ($trackInput !== null) {
                        $changedMeasurementV .= $trackInput;

                        // safer check: does this column exist in either map?
                        if (array_key_exists($column, $imageNameMap) || array_key_exists($column, $oldFields)) {
                            $oldFields1[$column] = $measurement->$column ?? null;   // old value
                            $updatedFields[$column] = $newValue;                   // new value
                        }
                    }
                    $suffix = $imageNameMap[$column] ?? '';
                    $measurement->$column = $newValue . $suffix;
                }
                $measurement->save();

                $sheetData = GoogleSheetHelper::sendIndicatorSheetDataAndMatch($request->productitemid);

                if (!empty($updatedFields)) {
                    // Build HTML table for new values
                    $newHtmlTable = '<table border="1" cellpadding="5" cellspacing="0">';
                    $newHtmlTable .= '<tr><th colspan="2" class="measurementlog">Modify Measurement</th></tr>';
                    foreach ($updatedFields as $field => $value) {
                        $newHtmlTable .= '<tr>';
                        $newHtmlTable .= '<td class="measurementlog">' . htmlspecialchars($field) . '</td>';
                        $newHtmlTable .= '<td class="measurementlog">' . htmlspecialchars(str_replace("|","",$value)) . '</td>';
                        $newHtmlTable .= '</tr>';
                    }
                    $newHtmlTable .= '</table>';

                    // Build HTML table for old values
                    $oldHtmlTable = '<table border="1" cellpadding="5" cellspacing="0">';
                    $oldHtmlTable .= '<tr><th colspan="2" class="measurementlog">Modify Measurement</th></tr>';
                    foreach ($oldFields1 as $field => $value) {
                        $oldHtmlTable .= '<tr>';
                        $oldHtmlTable .= '<td class="measurementlog">' . htmlspecialchars($field) . '</td>';
                        $oldHtmlTable .= '<td class="measurementlog">' . htmlspecialchars(str_replace("|","",$value)) . '</td>';
                        $oldHtmlTable .= '</tr>';
                    }
                    $oldHtmlTable .= '</table>';

                    DB::table('andaaz_order_log')->insert([
                        'order_id' => $request->orderid,
                        'sku' => $productsku,
                        'item_id' => $productid,
                        'product_item_id' => $request->productitemid,
                        'new_value' => $newHtmlTable,
                        'old_value' => $oldHtmlTable ?? '',
                        'column_name' => 'Measurement', 
                        'pending_reason' => '1',
                        'updated_by' => $request->loginuser ?? 'system',
                        'updated_date' => now(),
                    ]);
                }
                Session::flash('success', 'Measurement updated successfully.'); 
            } 
        }

/* ============================================= For Special Instruction  ====================================*/

    $headings = $request->input('heading');       // array of headings
    $instructions = $request->input('instruction');
    $images = $request->file('image');
    $oldImages = $request->input('old_image');
    $addclickspinstruct = $request->input('addclickspinstruct');
    $deleteclickspinstruct = $request->input('deleteclickspinstruct');
    $oldHtmlInstruction = $newHtmlInstruction = $specialInstructionGsheet = '';
    if (!empty($headings) || !empty($addclickspinstruct) || !empty($deleteclickspinstruct)) {
        $hasNewInstruction = false; 
        $HtmlInstruction = '<table border="1" cellpadding="5" cellspacing="0"> <tr><th class="small">Heading</th><th class="small">Instruction</th><th class="small">Image</th></tr>';
        $existingInstructions = DB::table('special_instruction')
            ->where('item_id', $productid)
            ->get();

        foreach ($existingInstructions as $instruction) {
            $oldHtmlInstruction .= '<tr class="small">';
            $oldHtmlInstruction .= '<td>' . e($instruction->heading) . '</td>';
            $oldHtmlInstruction .= '<td>' . e($instruction->instruction) . '</td>';
            $oldHtmlInstruction .= '<td><img width="50px" src="' . e($instruction->image_url) . '"/></td>';
            $oldHtmlInstruction .= '</tr>';
            $hasNewInstruction = true;
        }

        $oldHtmlInstruction .= '</table>';

        $newRows = [];
        if (!empty($headings) && is_array($headings)) {
            foreach ($headings as $index => $heading) {
                $instruction = $instructions[$index] ?? '';
                $imagePath = null;

                if (isset($oldImages[$index])) {
                    $imagePath = $oldImages[$index];
                }
                if (!empty($images[$index]) && $images[$index]->isValid()) {
                    $imagePath = '/' . $images[$index]->store('uploads/special_instructions', 'public');
                } else if(isset($oldImages[$index]) && $oldImages[$index] === "[object HTMLInputElement]"){
                    $oldImages[$index]=$imagePath ='--';
                }

                if (!empty($heading) || !empty($instruction) || $imagePath) {
                    $hasNewInstruction = true;

                    $newRows[] = [
                        'heading' => $heading,
                        'instruction' => $instruction,
                        'image_url' => $imagePath,
                    ];
                }
            }
        } 

        if ($hasNewInstruction) {
            DB::table('special_instruction')->where('item_id', $productid)->delete(); 

            foreach ($newRows as $row) {
                DB::table('special_instruction')->insert([
                    'sku' => $productsku,
                    'item_id' => $productid,
                    'heading' => $row['heading'],
                    'instruction' => $row['instruction'],
                    'image_url' => $row['image_url'],
                    'updated_by' => $request->loginuser,
                    'updated_date' => now(),
                ]);

                $newHtmlInstruction .= '<tr class="small">';
                $newHtmlInstruction .= '<td>' . e($row['heading']) . '</td>';
                $newHtmlInstruction .= '<td>' . e($row['instruction']) . '</td>';
                $newHtmlInstruction .= '<td><img width="50px" src="' . e($row['image_url']) . '"/></td>';
                $newHtmlInstruction .= '</tr>';
                $specialInstructionGsheet .= e($row['heading'])." - ". e($row['instruction'])." | ";
            }

            $newHtmlInstruction .= '</table>';

            // Compare and log only if different
            if (trim($oldHtmlInstruction) !== trim($newHtmlInstruction)) {
                DB::table('andaaz_order_log')->insert([
                    'order_id' => $request->orderid,
                    'sku' => $productsku,
                    'item_id' => $productid,
                    'product_item_id' => $request->productitemid,
                    'new_value' => $HtmlInstruction.$newHtmlInstruction,
                    'old_value' => $HtmlInstruction.$oldHtmlInstruction,
                    'column_name' => 'Modify Instruction',
                    'pending_reason' => '1',
                    'updated_by' => $request->loginuser ?? 'system',
                    'updated_date' => now(),
                ]);
            }
        }
    }

/* ============================================= For Add On's Data  ====================================*/
        
        $newSelectedAddon = 'Add Ons:' . implode(', ', $selectedAddons);
        $addonsFormatted=$NewSelectedAddonValue=$NewSelectedAddonVal = $oldAddonsVals ='';
        $productsize = $request->input('productsizeattr');
        $newproductsize=$productsize;
        $removeAddons = [
            'Add Blouse Pad',
            'Add Add Inskirt\/Petticoat',
            'Add Inskirt/Petticoat',
            'Add Fall &amp; Pico',
            'Add Fall & Pico',
            'Pre Drape This Saree'
        ]; 
        $selectedAddonsa = array_diff($selectedAddons, $removeAddons);
        $newSelectedAddona = 'Add Ons:' . implode(', ', $selectedAddonsa);
        if (strpos($productsize, 'Add Ons:') !== false) { 
            $newproductsize = preg_replace('/Add Ons:[^|]*/', $newSelectedAddona, $productsize);
        } else {        
            $newproductsize .= ' | ' . $newSelectedAddon;            
        } 

if (!in_array('Add Blouse Pad', $selectedAddons)) {
    $newproductsize = str_replace('| Blouse Pad:Add Blouse Pad', '', $newproductsize);
} else if (in_array('Add Blouse Pad', $selectedAddons)) {
    $newproductsize = str_replace('| Blouse Pad:Add Blouse Pad', '', $newproductsize);
    $newproductsize = $newproductsize.'| Blouse Pad:Add Blouse Pad';
} 

if (!in_array('Add Inskirt\/Petticoat', $selectedAddons)) {
    $newproductsize = str_replace('| Petticoat:Add Inskirt\/Petticoat', '', $newproductsize);
    $newproductsize = str_replace('| Petticoat:Add Inskirt/Petticoat', '', $newproductsize);
} else if (in_array('Add Inskirt\/Petticoat', $selectedAddons)) {
    $newproductsize = str_replace(', Add Inskirt\/Petticoat', '', $newproductsize);
    $newproductsize = $newproductsize.'| Petticoat:Add Inskirt\/Petticoat';
}

if (!in_array('Add Fall &amp; Pico', $selectedAddons)) {
    $newproductsize = str_replace(' | Fall & Pico:Add Fall &amp; Pico', '', $newproductsize);
}else if (in_array('Add Fall &amp; Pico', $selectedAddons)) {
    $newproductsize = str_replace(' | Fall & Pico:Add Fall &amp; Pico', '', $newproductsize);
    $newproductsize = $newproductsize.'| Fall & Pico:Add Fall &amp; Pico';
}

if (!in_array('Pre Drape This Saree', $selectedAddons)) {
    $newproductsize = str_replace('| Convert into Ready to Wear Saree:Pre Drape This Saree',
        '',$newproductsize);
}else if (in_array('Pre Drape This Saree', $selectedAddons)) {
    $newproductsize = $newproductsize.'| Convert into Ready to Wear Saree:Pre Drape This Saree';
}

 
        $oldAddons =  explode('Add Ons:', $productsize);
        if(isset($oldAddons[1])){
            $oldAddonsValues =  explode(', ', $oldAddons[1]);
            $NewSelectedAddons = array_diff($selectedAddons, $oldAddonsValues);
        }else{
            $NewSelectedAddons = [];
        }

        DB::table('andaaz_inhouse_new')
        ->where('id', $productid)
        ->update([
            'product_size' => $newproductsize,
        ]);

        $addonsArray = array_map('trim', explode(':', $newSelectedAddon));

        if(isset($addonsArray[0]) && isset($addonsArray[1])){
            $addonsFormatted=$addonsArray[0].' - ['.$addonsArray[1].']';
        }        
        if(count($NewSelectedAddons) > 0){
            foreach($NewSelectedAddons as $NewSelectedAddon){
                $NewSelectedAddonValue .= $NewSelectedAddon.',';
                $NewSelectedAddonVal.= '<li>'.$NewSelectedAddon.'</li>';
            }
            foreach($oldAddonsValues as $oldAddonsVal){
                $oldAddonsVals.= '<li>'.$oldAddonsVal.'</li>';
            }
            $addonsFormatted=$addonsArray[0].' - ['.$NewSelectedAddonValue.']';
        }else{
            $addonsFormatted='';
        }
        if($addonsFormatted!==''){
            DB::table('andaaz_order_log')->insert([
                'order_id' => $request->orderid,
                'sku' => $productsku,
                'item_id' => $productid,
                'product_item_id' => $request->productitemid,
                'new_value' => $NewSelectedAddonVal,
                'old_value' => $oldAddonsVals ?? '--',
                'column_name' => 'Modify Instruction', 
                'pending_reason' => '1',
                'updated_by' => $request->loginuser ?? 'system',
                'updated_date' => now(),
            ]);
        }
        if($changedFields!=='' || $changedMeasurementV!=='' || $specialInstructionGsheet!=='' || $addonsFormatted!==''){
            $newRow = [
                $currentDateFormated,
                'ANDFS_'.$request->productitemid, 
                $request->orderid, 
                $request->input('productsku'), 
                $changedFields.$changedMeasurementV.$specialInstructionGsheet.$addonsFormatted,
                $request->loginuser ?? 'system', '', ''
            ];
            $result = GoogleSheetHelper::appendToIndicatorSheet($newRow); 
        }

/* ============================================= For logs  ====================================*/
      
    Session::flash('success', 'Details saved successfully.');
 
        return "<script>
            window.opener.location.reload();
            window.close();
        </script>";
    }

    /**
     * Handle image upload and return the stored filename.
     */
    private function getImageUploadSaved(Request $request,$inputName,$inputHiddenImg)
    {
        if(isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] == 0){        
            if ($request->hasFile($inputName)) {
                $imagePath = $request->file($inputName)->store('extra-images', 'public');
                return '|' . $imagePath;
            } 
        }
        if(isset($_POST[$inputHiddenImg]) && $_FILES[$inputName]['error'] == 4){
            $bustImagePath = $_POST[$inputHiddenImg];
            return $bustImageName = '|'.$bustImagePath;
        }
        if ($request->hasFile($inputName)) { 
            $bustImagePath = $request->file($inputName)->store('extra-images', 'public');
            return $bustImageName = '|'.$bustImagePath;
        }
        return null;
    }

    private function trackChangedMeasurement($measurement, $request, $column)
    {
        $existingValue = $measurement;
        $existingValueParts = explode('|', $existingValue);
        $existingNumeric = trim($existingValueParts[0]);
 
        $newValue = trim($request);

        if ($existingNumeric !== trim($request)) {
            return $changedMeasurement = $column . " - " . $newValue . " | ";
        }else{
            return null;
        }
    }
}
