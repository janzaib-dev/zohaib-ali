<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Holiday;
use App\Services\HrCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class HolidayController extends Controller
{
    protected $hrCache;

    public function __construct(HrCacheService $hrCache)
    {
        $this->hrCache = $hrCache;
    }

    public function index()
    {
        if (!auth()->user()->can('hr.holidays.view')) {
            abort(403, 'Unauthorized action.');
        }
        $year = request('year', date('Y'));
        $holidays = Holiday::whereYear('date', $year)->orderBy('date')->paginate(12)->withQueryString();
        return view('hr.holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:public,company,optional',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('edit_id')) {
            if (!auth()->user()->can('hr.holidays.edit')) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            $holiday = Holiday::findOrFail($request->edit_id);
            $oldYear = Carbon::parse($holiday->date)->year;
            
            $holiday->update($request->all());
            
            // Clear cache for old and new year
            $newYear = Carbon::parse($request->date)->year;
            $this->hrCache->clearHolidaysCache($oldYear);
            if ($oldYear !== $newYear) {
                $this->hrCache->clearHolidaysCache($newYear);
            }
            
            $message = 'Holiday Updated Successfully';
        } else {
            if (!auth()->user()->can('hr.holidays.create')) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            // Check if date already exists
            if (Holiday::whereDate('date', $request->date)->exists()) {
                return response()->json(['errors' => ['date' => ['A holiday already exists on this date.']]], 422);
            }
            
            $holiday = Holiday::create($request->all());
            
            // Clear cache for the year
            $year = Carbon::parse($request->date)->year;
            $this->hrCache->clearHolidaysCache($year);
            
            $message = 'Holiday Created Successfully';
        }

        return response()->json(['success' => $message, 'reload' => true]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('hr.holidays.delete')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        $holiday = Holiday::findOrFail($id);
        
        // Capture year before deleting
        $year = Carbon::parse($holiday->date)->year;
        
        $holiday->delete();
        
        // Clear cache
        $this->hrCache->clearHolidaysCache($year);
        
        return response()->json(['success' => 'Holiday Deleted Successfully', 'reload' => true]);
    }

    /**
     * API to get holidays for calendar
     */
    public function getHolidays(Request $request)
    {
        if (!auth()->user()->can('hr.holidays.view')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');
        
        // Use cache for logic optimization
        $holidays = $this->hrCache->getHolidays($year);
        
        if ($month) {
            $holidays = $holidays->filter(function($holiday) use ($month) {
                return Carbon::parse($holiday->date)->month == $month;
            })->values();
        }
        
        return response()->json($holidays);
    }
}
