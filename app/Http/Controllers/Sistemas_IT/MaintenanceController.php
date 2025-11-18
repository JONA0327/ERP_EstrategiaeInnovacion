<?php

namespace App\Http\Controllers\Sistemas_IT;

use App\Http\Controllers\Controller;
use App\Models\Sistemas_IT\ComputerProfile;
use App\Models\Sistemas_IT\MaintenanceSlot;
use App\Models\Sistemas_IT\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function availability(Request $request): JsonResponse
    {
        $month = $request->query('month');
        try {
            $start = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        } catch (\Exception $e) {
            $start = now()->startOfMonth();
        }

        $end = $start->copy()->endOfMonth();

        $now = Carbon::now('America/Mexico_City');

        $slots = MaintenanceSlot::active()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get()
            ->groupBy(fn ($slot) => $slot->date->toDateString())
            ->map(function ($daySlots) use ($now) {
                $date = $daySlots->first()->date->toDateString();
                $dayDate = Carbon::parse($date, 'America/Mexico_City');

                $futureSlots = $daySlots->filter(fn (MaintenanceSlot $slot) => $slot->start_date_time->greaterThan($now));

                $totalCapacity = $futureSlots->sum('capacity');
                $booked = $futureSlots->sum('booked_count');
                $available = max(0, $totalCapacity - $booked);
                $availableSlots = $futureSlots->filter(fn (MaintenanceSlot $slot) => $slot->available_capacity > 0)->count();

                if ($dayDate->copy()->endOfDay()->lessThanOrEqualTo($now) || $futureSlots->isEmpty()) {
                    $status = 'past';
                    $available = 0;
                } elseif ($available === 0) {
                    $status = 'full';
                } elseif ($booked === 0) {
                    $status = 'available';
                } else {
                    $status = 'partial';
                }

                return [
                    'date' => $date,
                    'total_slots' => $daySlots->count(),
                    'total_capacity' => $totalCapacity,
                    'booked' => $booked,
                    'available' => $available,
                    'available_slots' => $availableSlots,
                    'is_past' => $status === 'past',
                    'status' => $status,
                ];
            })
            ->values();

        return response()->json([
            'month' => $start->format('Y-m'),
            'days' => $slots,
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $now = Carbon::now('America/Mexico_City');

        $slots = MaintenanceSlot::active()
            ->whereDate('date', $request->query('date'))
            ->orderBy('start_time')
            ->get()
            ->map(function (MaintenanceSlot $slot) use ($now) {
                $start = $slot->start_date_time;
                $end = $slot->end_date_time;

                $isPast = $start->lessThanOrEqualTo($now);
                $availableCapacity = $isPast ? 0 : $slot->available_capacity;

                if ($isPast) {
                    $status = 'past';
                } elseif ($availableCapacity === 0) {
                    $status = 'full';
                } elseif ($availableCapacity === $slot->capacity) {
                    $status = 'available';
                } else {
                    $status = 'partial';
                }

                return [
                    'id' => $slot->id,
                    'start' => $start->format('H:i'),
                    'end' => $end->format('H:i'),
                    'label' => $start->format('H:i') . ' - ' . $end->format('H:i'),
                    'available' => $availableCapacity,
                    'capacity' => $slot->capacity,
                    'status' => $status,
                    'is_past' => $isPast,
                ];
            });

        return response()->json([
            'date' => $request->query('date'),
            'slots' => $slots,
        ]);
    }

    // Admin sub-funciones (no expuestas por ruta actualmente)
    public function adminIndex(): View
    {
        $slots = MaintenanceSlot::orderBy('date')->orderBy('start_time')->get();
        $groupedSlots = $slots->groupBy(fn ($slot) => Carbon::parse($slot->date)->format('Y-m-d'));
        $maintenanceTickets = Ticket::query()
            ->where('tipo_problema', 'mantenimiento')
            ->with(['computerProfile', 'maintenanceSlot'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('admin.maintenance.index', [
            'groupedSlots' => $groupedSlots,
            'componentOptions' => $this->getReplacementComponentOptions(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'maintenanceTickets' => $maintenanceTickets,
        ]);
    }

    public function showComputer(ComputerProfile $computerProfile): View
    {
        $tickets = Ticket::query()
            ->where('tipo_problema', 'mantenimiento')
            ->where('computer_profile_id', $computerProfile->id)
            ->with(['maintenanceSlot'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.maintenance.computers.show', [
            'computerProfile' => $computerProfile,
            'tickets' => $tickets,
            'componentOptions' => $this->getReplacementComponentOptions(),
        ]);
    }

    private function getReplacementComponentOptions(): array
    {
        return [
            'disco_duro' => 'Disco duro',
            'ram' => 'RAM',
            'bateria' => 'Batería',
            'pantalla' => 'Pantalla',
            'conectores' => 'Conectores',
            'teclado' => 'Teclado',
            'mousepad' => 'Mousepad',
            'cargador' => 'Cargador',
        ];
    }
}
