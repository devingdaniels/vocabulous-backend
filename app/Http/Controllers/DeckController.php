<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deck;
use App\Models\User;

class DeckController extends Controller
{
    public function createForUser(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user exists
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Find the user
        $user = User::find($validated['user_id']);

        // Create the deck associated with the user
        $deck = $user->decks()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Deck created successfully',
            'deck' => $deck,
        ], 201);
    }

    public function show($id)
    {
        // Retrieve the deck by its ID
        $deck = Deck::find($id);

        // Check if the deck exists
        if (!$deck) {
            return response()->json([
                'message' => 'Deck not found',
            ], 404);
        }

        // Return the deck as a JSON response
        return response()->json([
            'deck' => $deck,
        ], 200);
    }
}
