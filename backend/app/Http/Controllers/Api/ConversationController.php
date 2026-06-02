<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\Request;


class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Conversation::with(['listing.images', 'buyer', 'seller', 'lastMessage'])
            ->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)->orWhere('seller_id', $userId);
            })
            ->latest('updated_at')
            ->get()
            ->map(function ($conv) use ($userId) {
                $conv->unread_count = $conv->unreadCount($userId);
                $conv->other_user = $conv->buyer_id === $userId ? $conv->seller : $conv->buyer;
                return $conv;
            });

        return response()->json($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        // Vérification de l'autorisation
        if ($user->id !== $conversation->buyer_id && $user->id !== $conversation->seller_id) {
            abort(403, 'Accès non autorisé');
        }

        $conversation->load(['listing.images', 'buyer', 'seller', 'messages.sender']);

        // Marque comme lus les messages reçus
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Ajoute l'interlocuteur pour faciliter l'affichage côté client
        $conversation->other_user = $conversation->buyer_id === $user->id
            ? $conversation->seller
            : $conversation->buyer;

        return response()->json($conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'message' => 'required|string|max:2000',
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);
        $user = $request->user();

        // Empêche d'envoyer un message sur sa propre annonce
        if ($listing->user_id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas vous envoyer un message à vous-même'
            ], 422);
        }

        $conversation = Conversation::firstOrCreate([
            'listing_id' => $listing->id,
            'buyer_id' => $user->id,
            'seller_id' => $listing->user_id,
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => $validated['message'],
        ]);

        $conversation->touch();

        return response()->json([
            'conversation' => $conversation->load(['listing', 'buyer', 'seller']),
            'message' => $message,
        ], 201);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = $request->user();

        if ($user->id !== $conversation->buyer_id && $user->id !== $conversation->seller_id) {
            abort(403, 'Accès non autorisé');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:5120',
        ]);

        if (empty($validated['message']) && !$request->hasFile('image')) {
            return response()->json([
                'message' => 'Un message ou une image est requis.',
                'errors' => ['message' => ['Veuillez saisir un message ou joindre une image.']],
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages/' . $conversation->id, 'public');
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'content' => $validated['message'] ?? null,
            'image_path' => $imagePath,
        ]);

        $conversation->touch();

        return response()->json($message->load('sender'), 201);
    }
}
