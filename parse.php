<?php

class Subtitle {
    function Subtitle ($time, $content) {
        $times = explode(',', $time);
        $this->start = $times[0];
        $this->end = $times[1];
        $this->content = $content;
    }   
}

function SRT($file_path) {
    $lines = file($file_path);
    $subtitles = array();
    
    for ($i=1;$i<count($lines);$i++) {
        
        if (substr($lines[$i], 12, 5) === ' --> ') {
            if (isset($index)) {
                $timeStr = getKey($lines[$time]);
                $subtitles[$timeStr] = new Subtitle($timeStr, getContent($lines, $time + 1, $i - 2));
            }
            $index = $i-1;
            $time = $i;
            
        }
    }
    if (isset($index)) {
        $timeStr = getKey($lines[$time]);
        $subtitles[$timeStr] = new Subtitle($timeStr, getContent($lines, $time + 1, $i - 2));
        
    }
    return $subtitles;
}

function getContent($array, $start, $end) {
    $res = array();
    for ($i=$start; $i <= $end; $i++) { 
        $res[]= trim($array[$i]);
    }
    return implode(' ' , $res);
}

function getKey($timeString) {
    $timeString = trim($timeString);
    $times = explode(' --> ', $timeString);
    $timeStart = explode(',', $times[0]);
    $timeStop = explode(',', $times[1]);
    return $timeStart[0].','.$timeStop[0];
}

function searchContent($startKey, $start, $end, $objs) {
    
    $res = array();
    $powerOn = false;
    $start = adjustTime($start, -1);
    $end = adjustTime($end, 1);
    foreach($objs as $key => $obj) {
        if ($key == $startKey) {
            $powerOn = true;
            continue;
        }
        if ($powerOn && $obj->start >= $start && $obj->end <= $end) {
            $res[] = $obj->content;
        } elseif ($obj->start > $end) {
            // 已经超出时间范围
            break;
        }
    }
    return implode(' ', $res);
}

function adjustTime($time, $seconds) {
    $time = strtotime($time);
    return date('H:i:s', $time + $seconds);
}

function zeroNum($num) {
    return substr('0'. $num, -2);
}

function makeTxtBySrt($firstSrt, $SecondSrt, $file_path) {
    $en = SRT($firstSrt);
    $cn = SRT($SecondSrt);
    $content = '';
    $lastKey = '';
    foreach ($en as $key => $value) {
        if (isset($cn[$key])) {
            $content .= $value -> content . "\n" . $cn[$key] -> content  . "\n\n" ;
            $lastKey = $key;
        } else {
            $content .= $value -> content . "\n" . searchContent($lastKey, $value->start, $value->end, $cn)  . "\n\n" ;
        }
    }
    
    file_put_contents($file_path, $content);
}

makeTxtBySrt('en.srt', 'cn.srt', dirname(__FILE__). '/./encn.txt');
