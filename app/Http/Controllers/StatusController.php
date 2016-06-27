<?php

/**
 *    Copyright 2015 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace App\Http\Controllers;

use DB;
use App\Models\BanchoStats;
use App\Models\Incident;
use App\Models\Score\Osu;
use Carbon\Carbon;

class StatusController extends Controller
{
    protected $section = 'status';

    public function getMain()
    {
        //Get the last 12 hours worth of online user data
        $userStats = BanchoStats::where('date', '>=', Carbon::now()->subHours(12))
            ->whereRaw('banchostats_id mod 12 = 0')
            ->limit(13)
            ->get();

        //Get the last 12 hours worth of score submits, and group by hour
        $scoreSubmits = Osu::where('date', '>=', Carbon::now()->subHours(12))
            ->groupBy('hour')
            ->orderBy('hour', 'ASC')
            ->limit(13)
            //Groups by date and hour
            ->get([DB::raw('DATE_FORMAT(date,\'%y-%m-%d %H:00:00\') as hour'), DB::raw('COUNT(*) as count')]);

        $data = [
            'status' => [
                'incidents' => $this->getIncidents(),

                'servers' => $this->getServers(),

                'online' => [
                    'graphs' => [
                        'online' => $this->getOnlineUsers($userStats),
                        'score' => $this->getScoreReports($scoreSubmits),
                    ],
                    'current' => $this->getCurrentOnline($userStats),
                    'score' => $this->getCurrentScore($scoreSubmits),
                ],

                'uptime' => [
                    'graphs' => [
                        'server' => $this->getServerUptime(),
                        'web' => $this->getWebUptime(),
                    ],
                ],
            ],
        ];

        return view('status.main')
        ->with('title', 'Status')
        ->with('data', $data);
    }

    private function getCurrentOnline($userStats)
    {
        return ($userStats->isEmpty() ? 0 : $userStats->last()->users_osu);
    }

    private function getCurrentScore($scoreSubmits)
    {
        return ($scoreSubmits->isEmpty() ? 0 : $scoreSubmits->last()->count);
    }

    private function getOnlineUsers($userStats)
    {
        $onlineCounts = [];
        foreach ($userStats as $stat) 
        {
            $onlineCounts[] = $stat->users_osu;
        }
        return $onlineCounts;
    }

    private function getScoreReports($scoreSubmits)
    {
        $scoreCounts = [];
        foreach ($scoreSubmits as $score)
        {
            $scoreCounts[] = $score->count;
        }
        return $scoreCounts;
    }

    private function getIncidents()
    {
        $outgoingIncidents = [];
        $serverIncidents = Incident::all();

        $status = ['unknown', 'update', 'resolved'];
        foreach ($serverIncidents as $incident)
        {
            $outgoingIncidents[] = [
                'description' => $incident->description,
                'status' => $status[$incident->status],
                'child' => !$incident->isParent(),
                'date' => $incident->date,
                'by' => ($incident->hasAuthor() ? $incident->author->username : null),
            ];
        }
        return $outgoingIncidents;
    }

    private function getServers()
    {
        return [
            [
                'name' => 'Europe',
                'players' => 69,
                'y' => 200,
                'x' => 450,
                'state' => 'up', // green
            ],

            [
                'name' => 'North America',
                'players' => '',
                'y' => 350,
                'x' => 150,
                'state' => 'down', // red
            ],

            [
                'name' => 'Asia',
                'players' => 1337,
                'y' => 250,
                'x' => 1200,
                'state' => 'up',
            ],

            [
                'name' => 'Africa',
                'players' => 420,
                'y' => 550,
                'x' => 800,
                'state' => 'up',
            ],

            [
                'name' => 'South America',
                'players' => 71,
                'y' => 520,
                'x' => 150,
                'state' => 'up',
            ],
        ];
    }

    private function getServerUptime()
    {
        return [
            'today' => [
                'up' => 55,
                'down' => 18,
            ],

            'week' => [
                'up' => 12,
                'down' => 66,
            ],

            'month' => [
                'up' => 99,
                'down' => 1,
            ],

            'all_time' => [
                'up' => 67,
                'down' => 5,
            ],
        ];
    }

    private function getWebUptime()
    {
        return [
            'today' => [
                'up' => 40,
                'down' => 10,
            ],

            'week' => [
                'up' => 58,
                'down' => 2,
            ],

            'month' => [
                'up' => 54,
                'down' => 20,
            ],

            'all_time' => [
                'up' => 90,
                'down' => 5,
            ],
        ];
    }
}
