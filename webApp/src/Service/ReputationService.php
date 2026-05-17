<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserReputation;

class ReputationService
{
    public function getReputationStats(User $user): array
    {
        $rep = $user->getReputation();
        
        if (!$rep) {
            return [
                'score' => 0,
                'stars' => 0,
                'completed' => 0,
                'canceled' => 0,
                'rank' => 'Newcomer',
                'color' => '#94a3b8'
            ];
        }

        $completed = $rep->getCompletedContracts();
        $canceled = $rep->getCanceledContracts();
        $total = $completed + $canceled;

        $stars = 0;
        if ($total > 0) {
            $stars = ($completed / $total) * 5;
        }

        // Rank determination
        $rank = 'Rookie';
        $color = '#94a3b8';

        if ($completed >= 50) {
            $rank = 'Grand Master';
            $color = '#f59e0b';
        } elseif ($completed >= 20) {
            $rank = 'Expert Trader';
            $color = '#a855f7';
        } elseif ($completed >= 5) {
            $rank = 'Verified Pro';
            $color = '#6366f1';
        } elseif ($completed > 0) {
            $rank = 'Active Member';
            $color = '#10b981';
        }

        return [
            'score' => $completed,
            'stars' => round($stars, 1),
            'completed' => $completed,
            'canceled' => $canceled,
            'rank' => $rank,
            'color' => $color
        ];
    }
}
