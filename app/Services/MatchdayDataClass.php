<?php

namespace App\Services;

class MatchdayDataClass
{
    protected $matchday;
    protected $matchdayDetails;
    protected $rawDatas;
    protected $pointsTotal;
    protected $highestGoalScored;
    protected $highestGoal;
    protected $doc;
    protected $odds;

    public function __construct(mixed $data, int $matchdayNumber)
    {
        // $path = storage_path('App/Data/data.json');
        // $json = file_get_contents($path);
        // $this->matchday = json_decode($json, true);
        $this->matchday = $data[0];
        $this->doc = $this->matchday["doc"][0];
        $this->odds = collect($this->doc["data"]["odds"]);

        // $path2 = storage_path('App/Data/data2.json');
        // $json2 = file_get_contents($path2);
        // $this->matchdayDetails = json_decode($json2, true);
        $this->matchdayDetails = $data[1];
        $this->rawDatas = $this->matchdayDetails["doc"][0]["data"]["tables"][0]["tablerows"];

        $this->pointsTotal = ["pointsTotal" => $this->rawDatas[0]["pointsTotal"], "teamName" => $this->rawDatas[0]["team"]["name"]];

        $highestGoalScored = new HighestGoalScoredClass($this->rawDatas);
        $this->highestGoal = $highestGoalScored->calculateHighestGoalScored();
    }
    protected function filterMarketByTeam(string $team)
    {
        return collect($this->odds->filter(
            function ($odd) use ($team) {
                return $odd["teams"]["home"]["name"] === $team || $odd["teams"]["away"]["name"] === $team;
            }
        ))->first();
    }
    public function getWinOrDrawMatchday()
    {
        $odd = $this->filterMarketByTeam($this->pointsTotal["teamName"]);
        $WinOrDraw = collect(collect($odd["market"])
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
        $teams = $odd['teams'];

        $homeOrAway = (collect($teams)->filter(function ($filterPrediction) {
            return $filterPrediction['name'] === $this->pointsTotal["teamName"];
        }))->keys()->first();

        $prediction = (collect($WinOrDraw)->filter(function ($filterPrediction) use ($homeOrAway) {
            return $filterPrediction['type'] === $homeOrAway;
        }))->values()->first()['result'] === 1 ? "win" : "loss";;

        return ["queryUrl" => $this->matchday["queryUrl"], "home" => $odd["teams"]["home"]["name"], "away" => $odd["teams"]["away"]["name"], "market" => $WinOrDraw, "prediction" => $prediction, "team"=>$this->pointsTotal["teamName"]];
    }
    //     return [
    //         // "index" => $matchdayNumber,
    //         "queryUrl" => $this->matchday["queryUrl"],
    //         "timestamp" => $this->doc["timestamp"],
    //         "outcome" => $team
    //     ];
    // }

    // 3 ["2863795", "2863820","2863846","2863872","2863899","2863920","2863951","2863975","2864001"]
    // 7 ["2863805", "2863782","2863833","2863857","2863883","2863906","2863933","2863959","2863989"]
    // 8 ["2863794", "2863819","2863845","2863871","2863898","2863919","2863950","2863974","2864000"]

    public function getOverOrUnderMatchday()
    {
        $highestGoalTeam = $this->highestGoal['teamName'];
        $odd = $this->filterMarketByTeam($highestGoalTeam);
        $total = collect(collect($odd["market"])->filter(function ($marketOdd) {
            return $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=0.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=1.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=2.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=3.5" || $marketOdd["id"] === 18 && $marketOdd["specifiers"] === "total=4.5";
        }))->map(function ($marketType) {
            $key = explode("=",  $marketType["specifiers"]);
            $key = array_map('trim',  $key);

            return ["type" => $key[1], "over" => ["odds" => $marketType["outcome"][0]["odds"], "result" => $marketType["outcome"][0]["result"]], "under" => ["odds" => $marketType["outcome"][1]["odds"], "result" => $marketType["outcome"][1]["result"]]];
        })->values()->all();

        return ["queryUrl" => $this->matchday["queryUrl"], 'odd' => $total, 'prediction' => $total[1]['over']['result'] === 1 ? 'over' : 'under'];
    }
}
