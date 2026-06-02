<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Autorise la consultation de la liste des avis.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Autorise la consultation d'un avis (uniquement s'il est publié).
     */
    public function view(User $user, Review $review): bool
    {
        return $review->status === 'published';
    }

    /**
     * Autorise la modification d'un avis par son auteur.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->reviewer_id;
    }

    /**
     * Autorise la suppression d'un avis par son auteur.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->reviewer_id;
    }

    /**
     * Autorise le vendeur évalué à répondre une seule fois à un avis le concernant.
     */
    public function respond(User $user, Review $review): bool
    {
        return $user->id === $review->reviewed_user_id && is_null($review->seller_response);
    }
}
