<?php

namespace App\Services;

class FilterMatchdayData
{

    public function getFilteredMatchday($matchday)
    {
        // $path = storage_path('App/Data/data.json');
        // $json = file_get_contents($path);
        // $matchday = json_decode($json, true);
        $doc = $matchday["doc"][0];
        $odds = collect($doc["data"]["odds"]);
        $team = $odds->map(function ($odd) {
            $marketCol = collect($odd["market"]);

            $WinOrDraw = collect($marketCol
                ->filter(function ($marketOdd) {
                    return $marketOdd["id"] === 1;
                })->values()->first()['outcome'])
                ->map(function ($marketOdd) {
                    if ($marketOdd["id"] === "1") {
                        $type = "home";
                    } elseif ($marketOdd["id"] === "2") {
                        $type = "draw";
                    } else {
                        $type = "away";
                    }
                    return [
                        "type" => $type,
                        "odds" => $marketOdd["odds"],
                        "result" => $marketOdd["result"]
                    ];
                })->values()->all();

            $total = $marketCol->filter(function ($marketOdd) {
                return $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=0.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=1.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=2.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=3.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=4.5";
            })->map(function ($marketType) {
                $key = explode("=",  $marketType["specifiers"]);
                $key = array_map('trim',  $key);

                return ["type" => $key[1], "over" => ["odds" => $marketType["outcome"][0]["odds"], "result" => $marketType["outcome"][0]["result"]], "under" => ["odds" => $marketType["outcome"][1]["odds"], "result" => $marketType["outcome"][1]["result"]]];
            })->values()->all();
            return ["home" => $odd["teams"]["home"]["name"], "away" => $odd["teams"]["away"]["name"], "market" => ["winordraw" => $WinOrDraw, "total" => $total]];
        })->values()->all();

        return [
            "queryUrl" => $matchday["queryUrl"],
            "timestamp" => $doc["timestamp"],
            "outcome" => $team
        ];
    }
}
