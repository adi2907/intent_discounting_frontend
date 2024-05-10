<?php 

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait SegmentTrait {
    
    public function runSegment($shop, $row) {
        $baseEndpoint = getAlmeAppURLForStore('segments/identified-users-list');
        $headers = getAlmeHeaders();
        $rules = $row->getRules();
        $responseArr = [];
        foreach($rules as $ruleArr) {
            $payload = [
                'app_name' => $shop->shop_url,
                'action' => $ruleArr['did_event_select'],
            ];

            if($ruleArr['time_select'] == 'yesterday') {
                $payload['yesterday'] = 'true';
            }

            if($ruleArr['time_select'] == 'today') {
                $payload['today'] = 'true';
            }

            if($ruleArr['time_select'] == 'within_last_days') {
                $payload['last_x_days'] = $ruleArr['within_last_days'];
            }

            if($ruleArr['time_select'] == 'before_days') {
                $payload['before_x_days'] = $ruleArr['before_days'];
            }

            $getParams = [];
            foreach($payload as $key => $value) {
                $getParams[] = $key.'='.$value;
            }
            $getParams = implode('&', $getParams);
            $endpoint = $baseEndpoint.'?'.$getParams;
            $responseArr[] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
        }

        $notRules = $row->getNotRules();
        $notResponseArr = [];
        foreach($notRules as $ruleArr) {
            $payload = [
                'app_name' => $shop->shop_url,
                'action' => $ruleArr['did_event_select'],
            ];

            if($ruleArr['time_select'] == 'yesterday') {
                $payload['yesterday'] = 'true';
            }

            if($ruleArr['time_select'] == 'today') {
                $payload['today'] = 'true';
            }

            if($ruleArr['time_select'] == 'within_last_days') {
                $payload['last_x_days'] = $ruleArr['within_last_days'];
            }

            if($ruleArr['time_select'] == 'before_days') {
                $payload['before_x_days'] = $ruleArr['before_days'];
            }

            $getParams = [];
            foreach($payload as $key => $value) {
                $getParams[] = $key.'='.$value;
            }
            $getParams = implode('&', $getParams);
            $endpoint = $baseEndpoint.'?'.$getParams;
            $notResponseArr[] = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
        }
        
        $ids = $this->processAlmeAudienceSegments($responseArr, $rules);
        $finalAudience = $this->getFinalSegmentAudience($ids, $responseArr);
        
        $notIds = $this->processAlmeAudienceSegments($notResponseArr, $notRules);
        $finalNotAudience = $this->getFinalSegmentAudience($notIds, $notResponseArr);

        $aMinusB = $this->getAMinusBForFinalData($finalAudience, $finalNotAudience);

        $profileRules = $row->getProfileRules();
        $combinedProfileAudience = [];

        $arrayVariables = [];
        $arrayKeyArr = [];

        //TODO: use array_keys to compare not the actual audience
        $createdAtResponse = $this->getCreatedAtResponse($profileRules, $shop);
        if($createdAtResponse !== null) {
            $createdAtResponse = $this->filteredCreatedOrSessionResponse($createdAtResponse);
            $arrayKeys = array_keys($createdAtResponse);
            $arrayKeyArr[] = $arrayKeys;
            $arrayVariables[] = $createdAtResponse;
        }
            
        $lastVisitResponse = $this->getLastVisitResponse($profileRules, $shop);
        if($lastVisitResponse !== null) {
            $lastVisitResponse = $this->filteredCreatedOrSessionResponse($lastVisitResponse);
            $arrayKeys = array_keys($lastVisitResponse);
            $arrayKeyArr[] = $arrayKeys;
            $arrayVariables[] = $lastVisitResponse;
        }
        
        $sessionResponse = $this->getSessionResponse($profileRules, $shop);
        if($sessionResponse !== null) {
            $sessionResponse = $this->filteredCreatedOrSessionResponse($sessionResponse);
            $arrayKeys = array_keys($sessionResponse);
            $arrayKeyArr[] = $arrayKeys;
            $arrayVariables[] = $sessionResponse;
        }

        $profileCombinedAudience = $this->getProfileCombinedAudience($arrayVariables);

        $absoluteFinalAudience = $this->array_union($aMinusB, $profileCombinedAudience);
        return ['status' => true, 'body' => $absoluteFinalAudience];
    }

    public function combineProfileAudience($createdUsersResponse, $sessionsAudience) {
        if($createdUsersResponse !== null || $sessionsAudience !== null) {
            if(is_array($createdUsersResponse) && count($createdUsersResponse) > 0) {
                if(is_array($sessionsAudience) && count($sessionsAudience) > 0) {
                    return $this->array_union($createdUsersResponse, $sessionsAudience);
                } else {
                    return $createdUsersResponse;
                }
            } else {
                if(is_array($sessionsAudience) && count($sessionsAudience) > 0) {
                    return $sessionsAudience;
                }
            }
        }
        return null;
    }

    private function getProfileCombinedAudience($arrayVariables) {
        $returnVal = [];
        foreach($arrayVariables as $key => $associatedArr) {
            if(count($returnVal) > 0) {
                foreach($associatedArr as $key => $value) {
                    if(!array_key_exists($key, $returnVal)) {
                        unset($returnVal[$key]);
                    }
                }
            } else {
                $returnVal = $associatedArr;
            }
        }
        return count($returnVal) > 0 ? $returnVal : null;
    }

    public function getAMinusBForFinalData($finalAudience, $finalNotAudience) {
        if($finalAudience == null) return null;

        if($finalNotAudience == null) return $finalAudience;

        $finalArr = [];
        foreach($finalAudience as $id => $data) {
            if(!array_key_exists($id, $finalNotAudience)) {
                $finalArr[$id] = $data;
            }
        }
        return $finalArr;
    }

    public function getFinalSegmentAudience($ids, $responseArr) {
        $returnVal = [];
        
        foreach($responseArr as $arr) {
            $tempArrKeys = collect($arr['body'])->keyBy('id')->toArray();
            foreach($ids as $id) {
                if(array_key_exists($id, $tempArrKeys)) {
                    $returnVal[$id] = $tempArrKeys[$id];
                }
            }
        }

        return $returnVal;
    }

    public function processAlmeAudienceSegments($responseArr, $rules) {
        $dataToReturn = [];

        //match the rules with alme responses
        foreach($rules as $key => $value) {
            $currentAndOr = $value['and_or_val'];
            $currentSegment = $responseArr[$key]['body'];
            if($key == 0) {
                //Initialize first segment to be returned in case there's only one rule
                $dataToReturn = array_keys(collect($currentSegment)->keyBy('id')->toArray());
            }

            $nextRuleExists = array_key_exists($key + 1, $rules) && $rules[$key + 1] != null;
            if($nextRuleExists) {
                //There are more than 1 rules in the segment so now we need to compare
                $dataToReturn = $this->compareTwoSegmentsWithUnionOrIntersection($currentSegment, $responseArr[$key + 1]['body'], $dataToReturn, $currentAndOr);
            } else {
                //No more to compare
                //I guess do nothing
            }
        }

        return $dataToReturn;
    }

    public function getSessionResponse($profileRules, $shop) {
        if(!isset($profileRules['session_filter']) || !isset($profileRules['session_input']) || !filled($profileRules['session_filter']) || !filled($profileRules['session_input'])) {
            Log::info('early return for getSessionResponse');
            return null;
        }

        $endpoint = getAlmeAppURLForStore('segments/identified-users-sessions', $shop);
        $headers = getAlmeHeaders();
        $payload = [
            'app_name' => $shop->shop_url,
            'comparison_field' => $profileRules['session_filter'],
            'comparison_value' => $profileRules['session_input']
        ];

        $getParams = [];
        foreach($payload as $param => $val) {
            $getParams[] = $param.'='.$val;
        }
        $getParams = implode('&', $getParams);
        $endpoint = $endpoint.'?'.$getParams;
        Log::info('getSessionResponse endpoint '.$endpoint);
        $response = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);

        Log::info('Response for getSessionResponse');
        Log::info($response);
        return isset($response['body']) ? $response['body'] : null;

    }

    public function getCreatedAtResponse($profileRules, $shop) {

        if(!isset($profileRules['createdOn_filter']) || !isset($profileRules['createdOn_input']) || !filled($profileRules['createdOn_filter']) || !filled($profileRules['createdOn_input'])) {
            Log::info('early return for getCreatedAtResponse');
            return null;
        }

        $endpoint = getAlmeAppURLForStore('segments/identified-users-created-at', $shop);
        $headers = getAlmeHeaders();
        
        $payload = [
            'app_name' => $shop->shop_url
        ];

        if(in_array($profileRules['createdOn_filter'], ['on', 'before', 'after'])) {
            $payload['date_field'] = $profileRules['createdOn_filter'];
            $payload['date'] = $profileRules['createdOn_input'];
        }

        if(in_array($profileRules['createdOn_filter'], ['between'])) {
            $payload['date_field'] = $profileRules['createdOn_filter'];
            $payload['start_date'] = $profileRules['createdOn_input'];
            $payload['end_date'] = $profileRules['createdOn_inputEnd'];
        }

        
        $getParams = [];
        foreach($payload as $param => $val) {
            $getParams[] = $param.'='.$val;
        }
        $getParams = implode('&', $getParams);
        $endpoint = $endpoint.'?'.$getParams;
        Log::info('getCreatedAtResponse endpoint '.$endpoint);
        $response = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
        Log::info('Response for alme sessions');
        Log::info($response);
        return isset($response['body']) ? $response['body'] : null;
    }

    public function filteredCreatedOrSessionResponse($almeResponse) {
        try {
            $returnVal = [];
            if($almeResponse != null && count($almeResponse) > 0) {
                foreach($almeResponse as $response) {
                    $returnVal[$response['id']] = $response;
                }
            }
            return $returnVal;
        } catch (Exception $e) {
            Log::info('filtered created response error');
            Log::info($e->getMessage().' '.$e->getLine());
        }
        return null;
    }

    public function getLastVisitResponse($profileRules, $shop) {
        
        if(!isset($profileRules['lastSeen_input']) || !isset($profileRules['lastSeen_filter']) || !filled($profileRules['lastSeen_input']) || !filled($profileRules['lastSeen_filter'])) {
            Log::info('early return for getLastVisitResponse');
            return null;
        }

        $endpoint = getAlmeAppURLForStore('segments/identified-users-last-visit', $shop);
        $headers = getAlmeHeaders();
        
        $payload = [
            'app_name' => $shop->shop_url
        ];

        if(in_array($profileRules['lastSeen_filter'], ['on', 'before', 'after'])) {
            $payload['date_field'] = $profileRules['lastSeen_filter'];
            $payload['date'] = $profileRules['lastSeen_input'];
        }

        if(in_array($profileRules['lastSeen_filter'], ['between'])) {
            $payload['date_field'] = $profileRules['lastSeen_filter'];
            $payload['start_date'] = $profileRules['lastSeen_input'];
            $payload['end_date'] = $profileRules['lastSeen_inputEnd'];
        }

        $getParams = [];
        foreach($payload as $key => $val) {
            $getParams[] = $key.'='.$val;
        }

        $getParams = implode('&', $getParams);
        $endpoint = $endpoint.'?'.$getParams;
        Log::info('getLastVisitResponse endpoint '.$endpoint);
        $response = $this->makeAnAlmeAPICall('GET', $endpoint, $headers);
        Log::info('Response for identified-users-last-visit');
        Log::info($response);
        $usersList = isset($response['body']) ? $response['body'] : null;
        return $usersList;
    }

    public function array_union($x, $y) { 
        if($x !== null && $y !== null) {
            return array_merge(
                array_intersect($x, $y),   // Intersection of $x and $y
                array_diff($x, $y),        // Elements in $x but not in $y
                array_diff($y, $x)         // Elements in $y but not in $x
            );
        }
        if($x == null && $y !== null) return $y;
        if($x !== null && $y == null) return $x;
    
        
    }

    public function compareTwoSegmentsWithUnionOrIntersection($currentSegment, $almeBody, $dataToReturn, $currentAndOr) {
        
        $currentSegment = collect($currentSegment)->keyBy('id')->toArray();
        $almeBody = collect($almeBody)->keyBy('id')->toArray();

        $currentSegmentKeys = array_keys($currentSegment);
        $almeBodyKeys = array_keys($almeBody);

        if($almeBodyKeys != null && count($almeBodyKeys) > 0) {
            $tempRes = null;
            if($currentAndOr == 'and') 
                $tempRes = array_intersect($currentSegmentKeys, $almeBodyKeys);

            if($currentAndOr == 'or')
                $tempRes = $this->array_union($currentSegmentKeys, $almeBodyKeys);

            return array_unique(array_merge($tempRes, $dataToReturn));
        }
        return $dataToReturn;
    }
}