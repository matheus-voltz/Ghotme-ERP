<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return view('content.apps.app-calendar');
    }

    // API: Listar Eventos
    public function fetchEvents(Request $request)
    {
        // Pega eventos da empresa do usuário logado
        $events = Event::where('company_id', Auth::user()->company_id)
            ->get()
            ->map(function ($event) {
                return $event->toFullCalendarEvent();
            });

        return response()->json($events);
    }

    // API: Criar Evento
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
            'extendedProps.calendar' => 'required|string',
        ]);

        $event = Event::create([
            'user_id' => Auth::id(),
            'company_id' => Auth::user()->company_id,
            'title' => $request->title,
            'start' => $request->start,
            'end' => $request->end,
            'all_day' => $request->allDay ?? false,
            'url' => $request->url,
            'calendar' => $request->extendedProps['calendar'],
            'location' => $request->extendedProps['location'] ?? null,
            'description' => $request->extendedProps['description'] ?? null,
            'guests' => $request->extendedProps['guests'] ?? [],
        ]);

        return response()->json($event->toFullCalendarEvent());
    }

    // API: Atualizar Evento
    public function update(Request $request, $id)
    {
        $event = Event::where('company_id', Auth::user()->company_id)->findOrFail($id);

        $event->update([
            'title' => $request->title,
            'start' => $request->start,
            'end' => $request->end,
            'all_day' => $request->allDay ?? false,
            'url' => $request->url,
            'calendar' => $request->extendedProps['calendar'],
            'location' => $request->extendedProps['location'] ?? null,
            'description' => $request->extendedProps['description'] ?? null,
            'guests' => $request->extendedProps['guests'] ?? [],
        ]);

        return response()->json($event->toFullCalendarEvent());
    }

    // API: Deletar Evento
    public function destroy($id)
    {
        $event = Event::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    }
}
