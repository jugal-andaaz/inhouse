<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AttendanceDB23janModel;
use Vanguard\Models\StitchingToPPTestModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceOutTimeSync extends Command
{
    protected $signature = 'attendance:formatouttimecron';
    protected $description = 'Match attendance.emp_code (via AppSheet mapping) to a verified stitchingToPPTest tailor, then stamp the matching open stitchingToPPTest hold field for today\'s clock-outs. Only stitchingToPPTest is ever written to.';

    // start-column => hold-column, checked in this priority order
    protected $holdFieldPriority = [
        'fstart'   => 'fhold',
        'sstart'   => 'shold',
        'thstart'  => 'thhold',
        'frtstart' => 'frthold',
    ];

    public function handle()
    {
        try {
            DB::connection('adzappsheetsql')->getPdo();
        } catch (\Exception $e) {
            $this->error("AttendanceOutTimeSync__Remote DB (adzappsheetsql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        try {
            DB::connection('inhousedb23jansql')->getPdo();
        } catch (\Exception $e) {
            $this->error("AttendanceOutTimeSync__Remote DB (inhousedb23jansql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        // ── Build verified emp_code => Final_EMP_Code map (sheet colA/colC, validated against stitchingToPPTest.emp_id_tailor) ──
        $sheetRows = GoogleSheetHelper::fetchEmpCodeMappingSheet();

        $empCodeMap = [];
        if (is_array($sheetRows)) {
            foreach ($sheetRows as $index => $row) {
                if ($index === 0) continue;

                $empCode = isset($row[0]) ? (int) trim($row[0]) : 0;
                $finalCode = isset($row[2]) ? trim($row[2]) : '';

                if ($empCode && $finalCode !== '') {
                    $empCodeMap[$empCode] = $finalCode;
                }
            }
        }

        if (empty($empCodeMap)) {
            $this->info('No emp_code mapping found in sheet.');
            return 0;
        }

        $tailorCodes = StitchingToPPTestModel::whereNotNull('emp_id_tailor')
            ->where('emp_id_tailor', '<>', '')
            ->distinct()
            ->pluck('emp_id_tailor')
            ->map(fn ($v) => trim($v))
            ->flip()
            ->all();

        if (empty($tailorCodes)) {
            $this->info('No tailor codes found in stitchingToPPTest.');
            return 0;
        }

        $validEmpCodes = array_filter($empCodeMap, fn ($finalCode) => isset($tailorCodes[$finalCode]));

        if (empty($validEmpCodes)) {
            $this->info('No verified tailor emp_codes found.');
            return 0;
        }

        // ── READ-ONLY: today's attendance rows with a real (non-placeholder) out_time ──
        // attendance is never written to by this command.
        $today = Carbon::today()->format('Y-m-d');

        $todaysRows = AttendanceDB23janModel::where('date_string', $today)
            ->whereNotNull('out_time')
            ->where('out_time', '<>', '')
            ->where('out_time', '<>', '--:--')
            ->get(['emp_code', 'out_time']);

        $stampedCount = 0;
        $skippedCount = 0;

        foreach ($todaysRows as $row) {
            $empCode = (int) trim($row->emp_code);
            $finalCode = $validEmpCodes[$empCode] ?? null;

            if (!$finalCode) {
                continue;
            }

            $rawOutTime = trim($row->out_time);
            $formatted = preg_match('/^[0-9]{1,2}:[0-9]{2}$/', $rawOutTime)
                ? $today . ' ' . $rawOutTime . ':00'
                : Carbon::parse($rawOutTime)->format('Y-m-d H:i:s');

            $openRows = StitchingToPPTestModel::where('emp_id_tailor', $finalCode)
                ->whereNull('stitching_finished')
                ->orderBy('id', 'desc')
                ->get();

            if ($openRows->isEmpty()) {
                $skippedCount++;
                continue;
            }

            if ($openRows->count() > 1) {
                Log::warning("Multiple open stitchingToPPTest rows for tailor {$finalCode}", [
                    'ids' => $openRows->pluck('id')->all(),
                ]);
            }

            $openRow = $openRows->first();

            $field = null;
            foreach ($this->holdFieldPriority as $startField => $holdField) {
                if (!empty($openRow->$startField) && empty($openRow->$holdField)) {
                    $field = $holdField;
                    break;
                }
            }

            if (!$field) {
                $skippedCount++;
                continue;
            }

            StitchingToPPTestModel::where('id', $openRow->id)
                ->whereNull($field)
                ->update([
                    $field       => $formatted,
                    'updated_at' => Carbon::now(),
                ]);

            $stampedCount++;
        }

        $this->info("$stampedCount stitchingToPPTest hold fields stamped for today's clock-outs. $skippedCount skipped (no open row or no pending stage).");

        return 0;
    }
}
