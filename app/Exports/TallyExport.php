<?php

namespace Vanguard\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Vanguard\Models\FedexInvoiceFbt;
use Illuminate\Support\Collection;

class TallyExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected array $invoiceNumbers;

    public function __construct(array $invoiceNumbers = [])
    {
        $this->invoiceNumbers = $invoiceNumbers;
    }

    public function headings(): array
    {
        return [
            'Shipment_Number',
            'Master Tracking No',
            'MPS Tracking No',
            'Sender Account',
            'Sender Company Name',
            'Sender Contact Name',
            'Sender Address Line 1',
            'Sender Address Line 2',
            'Sender City',
            'Sender State',
            'Sender Country',
            'Sender_Postalcode',
            'Sender_PhoneNo',
            'Billed Transportation Type',
            'Billed Transportation A/c#',
            'Billed Duties and Taxes Type',
            'Billed Duties and Taxes A/C#',
            'CSB type',
            'Invoice Type',
            'Term of Invoice',
            'Bond/LUT',
            'Bond LUT Number',
            'LUT Expire Date',
            'Ecom Type',
            'MEIS',
            'IEC',
            'Purpose of Shipment',
            'Packaging',
            'FedEx Service Type',
            'AD code',
            'Taxnumber',
            'Recipient_Contact Name',
            'Recipient_Company Name',
            'Recipient_Address Line 1',
            'Recipient_Address Line 2',
            'Recipient_Address Line 3',
            'Recipient_Country',
            'Recipient_City',
            'Recipient_State',
            'Recipient_Postal code',
            'Recipient_Phone Number',
            'Recipient_Phone Extension',
            'Recipient_Email',
            'Type of Notification',
            'GST Amount',
            'Reference',
            'Invoice Number',
            'Invoice Date',
            'Freight_charges',
            'Insurance_charges',
            'Taxes',
            'Total No of Package',
            'Total Shipment weight',
            'Pkg_length',
            'Pkg_width',
            'Pkg_height',
            'Signature Option',
            'FOB Value',
            'Carriage Value',
            'Invoice Value',
            'CURRENCY',
            'Terms_Of_Sales',
            'Prefrential Agreement (If any)',
            'Importer_contact',
            'Importer_company',
            'Importer_address_1',
            'Importer_address_2',
            'Importer_city',
            'Importer_state',
            'Importer_postal_code',
            'Importer_country',
            'Importer_phone',
            'Importer_Email',
            'Origin_Location_ID',
            'Destination_Location_ID',
            'Bank_Account_No',
        ];
    }

    public function collection(): Collection
    {
        $query = FedexInvoiceFbt::whereNotNull('tracking_number')
            ->whereNotNull('tally_data');

        if (!empty($this->invoiceNumbers)) {
            $query->whereIn('invoice_number', $this->invoiceNumbers);
        }

        $rows = $query->orderBy('id')->get();

        return $rows->values()->map(function ($rec, $index) {
            $t = $rec->tally_data ?? [];

            $fob      = (float) ($t['fob_value'] ?? 0);
            $freight  = (float) ($t['freight_charges'] ?? 0);
            $invoice  = $fob + $freight;
            $csbType  = ($t['csb_type'] ?? 'CSB5') === 'CSB4' ? 'CSB4' : 'CSB5';
            $dutyType = $csbType === 'CSB4' ? 'RECIPIENT' : 'SENDER';

            return [
                $index + 1,                                         // Shipment_Number
                $rec->tracking_number,                              // Master Tracking No
                $rec->tracking_number,                              // MPS Tracking No
                '558546867',                                        // Sender Account
                'SV GLOBAL DESIGNS PVT. LTD.',                     // Sender Company Name
                'VISHAL JUNEJA',                                    // Sender Contact Name
                'C-32, OKHLA INDUSTRIAL ',                         // Sender Address Line 1
                'AREA,PHASE-1',                                     // Sender Address Line 2
                'DELHI',                                            // Sender City
                'DL',                                               // Sender State
                'IN',                                               // Sender Country
                '110020',                                           // Sender_Postalcode
                '9818041040',                                       // Sender_PhoneNo
                'SENDER',                                           // Billed Transportation Type
                '558546867',                                        // Billed Transportation A/c#
                $dutyType,                                                      // P - Billed Duties and Taxes Type
                $csbType === 'CSB4' ? '' : '558546867',                         // Q - Billed Duties and Taxes A/C#
                $csbType,                                                        // R - CSB type
                $csbType === 'CSB4' ? 'NON GST' : 'GST',                        // S - Invoice Type
                'CFR',                                                           // T - Term of Invoice
                $csbType === 'CSB4' ? '' : 'LUT',                               // U - Bond/LUT
                $csbType === 'CSB4' ? '' : 'AD070326014263G',                   // V - Bond LUT Number
                $csbType === 'CSB4' ? '' : '31-03-27',                          // W - LUT Expire Date
                $csbType === 'CSB4' ? '' : 'ECOM',                              // X - Ecom Type
                $csbType === 'CSB4' ? '' : 'NONE',                              // Y - MEIS
                '0516508041',                                                    // Z - IEC
                $csbType === 'CSB4' ? 'SAMPLE' : 'SOLD',                        // AA - Purpose of Shipment
                'FEDEX PAK',                                                     // AB - Packaging
                $t['fedex_service_type'] ?? '',                                  // AC - FedEx Service Type
                $csbType === 'CSB4' ? '' : '01803033480007',                    // AD - AD code
                'GST07AAUCS4226R1ZF',                                           // AE - Taxnumber
                $t['recipient_name'] ?? '',                         // Recipient_Contact Name
                $t['recipient_company'] ?? $t['recipient_name'] ?? '', // Recipient_Company Name
                $t['recipient_street1'] ?? '',                      // Recipient_Address Line 1
                $t['recipient_street2'] ?? '',                      // Recipient_Address Line 2
                '',                                                 // Recipient_Address Line 3
                $t['recipient_country'] ?? '',                      // Recipient_Country
                $t['recipient_city'] ?? '',                         // Recipient_City
                $t['recipient_state'] ?? '',                        // Recipient_State
                $t['recipient_postal'] ?? '',                       // Recipient_Postal code
                $t['recipient_phone'] ?? '',                        // Recipient_Phone Number
                '',                                                 // Recipient_Phone Extension
                $t['recipient_email'] ?? '',                        // Recipient_Email
                '',                                                 // Type of Notification
                '',                                                 // GST Amount
                $t['reference'] ?? '',                              // Reference
                $t['inv_number'] ?? '',                             // Invoice Number
                $t['invoice_date_raw'] ?? '',                       // Invoice Date (DDMMYY)
                $freight ?: '',                                     // Freight_charges
                '',                                                 // Insurance_charges
                '',                                                 // Taxes
                $t['total_packages'] ?? '1',                        // Total No of Package
                $t['weight'] ?? '',                                 // Total Shipment weight
                $t['length'] ?? '',                                 // Pkg_length
                $t['width'] ?? '',                                  // Pkg_width
                $t['height'] ?? '',                                 // Pkg_height
                'None',                                             // BE - Signature Option
                $csbType === 'CSB4' ? '' : ($fob ?: ''),           // BF - FOB Value
                '',                                                 // Carriage Value
                $invoice ?: '',                                     // Invoice Value
                $t['currency'] ?? 'USD',                            // CURRENCY
                $t['incoterm'] ?? 'CFR',                            // Terms_Of_Sales
                '',                                                 // Prefrential Agreement
                '',                                                 // Importer_contact
                '',                                                 // Importer_company
                '',                                                 // Importer_address_1
                '',                                                 // Importer_address_2
                '',                                                 // Importer_city
                '',                                                 // Importer_state
                '',                                                 // Importer_postal_code
                '',                                                 // Importer_country
                '',                                                 // Importer_phone
                '',                                                 // Importer_Email
                'DELSN',                                            // Origin_Location_ID
                $t['destination_location_id'] ?? '',                // Destination_Location_ID
                '',                                                 // Bank_Account_No
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
